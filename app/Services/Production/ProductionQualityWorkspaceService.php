<?php

namespace App\Services\Production;

use App\Enums\ProductionJobCardStatus;
use App\Enums\QualityCheckResult;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\QualityCheck;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ProductionQualityWorkspaceService
{
    /**
     * @return array{
     *     pending_inspections: int,
     *     passed: int,
     *     failed: int,
     *     on_hold: int
     * }
     */
    public function kpiCounts(): array
    {
        $checks = QualityCheck::query()->forTenant();
        $jobs = ProductionJobCard::query()->forTenant();

        return [
            'pending_inspections' => (clone $jobs)
                ->where('status', ProductionJobCardStatus::QualityCheck)
                ->count(),
            'passed' => (clone $checks)->where('result', QualityCheckResult::Passed)->count(),
            'failed' => (clone $checks)->where('result', QualityCheckResult::Failed)->count(),
            'on_hold' => (clone $jobs)->where('status', ProductionJobCardStatus::OnHold)->count(),
        ];
    }

    public function paginatedRegister(Request $request): LengthAwarePaginator
    {
        if ($request->query('status') === 'pending') {
            return $this->pendingInspectionsPaginator($request);
        }

        $query = QualityCheck::query()
            ->forTenant()
            ->with([
                'checker:id,name',
                'jobCard' => fn ($q) => $q->select(['id', 'job_card_number', 'customer_id', 'status'])
                    ->with('customer:id,company_name'),
            ]);

        $this->applyRegisterFilters($query, $request);

        return $query
            ->orderByDesc('checked_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();
    }

    /**
     * @return array{
     *     pass_rate: float,
     *     fail_rate: float,
     *     rework_count: int,
     *     hold_count: int,
     *     total_inspections: int
     * }
     */
    public function analytics(): array
    {
        $checks = QualityCheck::query()->forTenant();
        $passed = (clone $checks)->where('result', QualityCheckResult::Passed)->count();
        $failed = (clone $checks)->where('result', QualityCheckResult::Failed)->count();
        $rework = (clone $checks)->where('result', QualityCheckResult::ReworkRequired)->count();
        $total = $passed + $failed + $rework;

        return [
            'pass_rate' => $total > 0 ? round(($passed / $total) * 100, 1) : 0.0,
            'fail_rate' => $total > 0 ? round(($failed / $total) * 100, 1) : 0.0,
            'rework_count' => $rework,
            'hold_count' => ProductionJobCard::query()
                ->forTenant()
                ->where('status', ProductionJobCardStatus::OnHold)
                ->count(),
            'total_inspections' => $total,
        ];
    }

    /**
     * @return array{
     *     recent_failures: list<array<string, mixed>>,
     *     recent_holds: list<array<string, mixed>>,
     *     jobs_requiring_rework: list<array<string, mixed>>
     * }
     */
    public function intelligenceWidgets(): array
    {
        return [
            'recent_failures' => $this->recentFailures(),
            'recent_holds' => $this->recentHolds(),
            'jobs_requiring_rework' => $this->jobsRequiringRework(),
        ];
    }

    public function resultLabel(QualityCheckResult $result): string
    {
        return match ($result) {
            QualityCheckResult::Passed => __('Passed'),
            QualityCheckResult::Failed => __('Failed'),
            QualityCheckResult::ReworkRequired => __('Rework required'),
        };
    }

    /**
     * @return Collection<int, User>
     */
    public function inspectorOptions(): Collection
    {
        $inspectorIds = QualityCheck::query()
            ->forTenant()
            ->whereNotNull('checked_by')
            ->distinct()
            ->pluck('checked_by');

        if ($inspectorIds->isEmpty()) {
            return collect();
        }

        return User::query()
            ->whereIn('id', $inspectorIds)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function recentFailures(int $limit = 8): array
    {
        return QualityCheck::query()
            ->forTenant()
            ->where('result', QualityCheckResult::Failed)
            ->with([
                'checker:id,name',
                'jobCard' => fn ($q) => $q->select(['id', 'job_card_number', 'customer_id'])
                    ->with('customer:id,company_name'),
            ])
            ->orderByDesc('checked_at')
            ->limit($limit)
            ->get()
            ->map(fn (QualityCheck $check) => $this->mapCheckRow($check))
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function recentHolds(int $limit = 8): array
    {
        return ProductionJobCard::query()
            ->forTenant()
            ->where('status', ProductionJobCardStatus::OnHold)
            ->with([
                'customer:id,company_name',
                'qualityChecks' => fn ($q) => $q
                    ->select(['id', 'production_job_card_id', 'result', 'comments', 'checked_at', 'checked_by'])
                    ->with('checker:id,name')
                    ->orderByDesc('checked_at')
                    ->limit(1),
            ])
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get()
            ->map(function (ProductionJobCard $job) {
                $lastCheck = $job->qualityChecks->first();

                return [
                    'job_id' => $job->id,
                    'job_number' => $job->job_card_number,
                    'customer' => $job->customer?->company_name ?? '—',
                    'held_since' => $job->updated_at?->format('M j, Y') ?? '—',
                    'last_result' => $lastCheck?->result?->value,
                    'inspector' => $lastCheck?->checker?->name ?? '—',
                    'comments' => $lastCheck?->comments,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function jobsRequiringRework(int $limit = 8): array
    {
        return ProductionJobCard::query()
            ->forTenant()
            ->where('status', ProductionJobCardStatus::Rework)
            ->with([
                'customer:id,company_name',
                'qualityChecks' => fn ($q) => $q
                    ->where('result', QualityCheckResult::ReworkRequired)
                    ->with('checker:id,name')
                    ->orderByDesc('checked_at')
                    ->limit(1),
            ])
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get()
            ->map(function (ProductionJobCard $job) {
                $reworkCheck = $job->qualityChecks->first();

                return [
                    'job_id' => $job->id,
                    'job_number' => $job->job_card_number,
                    'customer' => $job->customer?->company_name ?? '—',
                    'flagged_at' => $reworkCheck?->checked_at?->format('M j, Y') ?? $job->updated_at?->format('M j, Y') ?? '—',
                    'inspector' => $reworkCheck?->checker?->name ?? '—',
                    'comments' => $reworkCheck?->comments,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function mapCheckRow(QualityCheck $check): array
    {
        return [
            'job_id' => $check->jobCard?->id,
            'job_number' => $check->jobCard?->job_card_number ?? '—',
            'customer' => $check->jobCard?->customer?->company_name ?? '—',
            'inspector' => $check->checker?->name ?? '—',
            'result' => $check->result->value,
            'checked_at' => $check->checked_at?->format('M j, Y H:i') ?? '—',
            'comments' => $check->comments,
        ];
    }

    protected function pendingInspectionsPaginator(Request $request): LengthAwarePaginator
    {
        $query = ProductionJobCard::query()
            ->forTenant()
            ->where('status', ProductionJobCardStatus::QualityCheck)
            ->with('customer:id,company_name');

        if ($search = trim((string) $request->query('search', ''))) {
            $like = '%'.addcslashes($search, '%_\\').'%';
            $query->where(function (Builder $q) use ($like) {
                $q->where('job_card_number', 'like', $like)
                    ->orWhereHas('customer', fn (Builder $c) => $c->where('company_name', 'like', $like));
            });
        }

        return $query
            ->orderBy('planned_end_date')
            ->orderBy('job_card_number')
            ->paginate(15)
            ->withQueryString();
    }

    protected function applyRegisterFilters(Builder $query, Request $request): void
    {
        if ($result = QualityCheckResult::tryFrom((string) $request->query('status', ''))) {
            $query->where('result', $result);
        }

        if ($date = $request->query('date')) {
            $query->whereDate('checked_at', $date);
        }

        if ($inspectorId = $request->query('inspector')) {
            $query->where('checked_by', $inspectorId);
        }

        if ($search = trim((string) $request->query('search', ''))) {
            $like = '%'.addcslashes($search, '%_\\').'%';
            $query->where(function (Builder $q) use ($like) {
                $q->whereHas('jobCard', fn (Builder $job) => $job->where('job_card_number', 'like', $like))
                    ->orWhereHas('jobCard.customer', fn (Builder $c) => $c->where('company_name', 'like', $like))
                    ->orWhereHas('checker', fn (Builder $u) => $u->where('name', 'like', $like));
            });
        }
    }
}
