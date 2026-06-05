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
use Illuminate\Support\Carbon;
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
                'jobCard' => fn ($q) => $q
                    ->select(['id', 'job_card_number', 'customer_id', 'status', 'production_type', 'planned_end_date'])
                    ->with([
                        'customer:id,company_name',
                        'salesOrder.items:id,sales_order_id,item_name,description,quantity',
                    ])
                    ->withCount([
                        'qualityChecks as rework_count' => fn (Builder $q) => $q->where('result', QualityCheckResult::ReworkRequired),
                    ])
                    ->addSelect([
                        'approval_checked_at' => QualityCheck::query()
                            ->select('checked_at')
                            ->whereColumn('production_job_card_id', 'production_job_cards.id')
                            ->where('result', QualityCheckResult::Passed)
                            ->orderByDesc('checked_at')
                            ->limit(1),
                        'hold_reason' => QualityCheck::query()
                            ->select('comments')
                            ->whereColumn('production_job_card_id', 'production_job_cards.id')
                            ->whereIn('result', [QualityCheckResult::Failed, QualityCheckResult::ReworkRequired])
                            ->orderByDesc('checked_at')
                            ->limit(1),
                    ]),
            ]);

        $this->applyRegisterFilters($query, $request);

        return $query
            ->orderByDesc('checked_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();
    }

    /**
     * @return array<string, mixed>
     */
    public function presentRegisterRow(QualityCheck $check): array
    {
        $job = $check->jobCard;
        $onHold = $job?->status === ProductionJobCardStatus::OnHold;

        return [
            'job_card_number' => $job?->job_card_number ?? '—',
            'customer_name' => $job?->customer?->company_name ?? '—',
            'product' => $job ? $this->productDescription($job) : '—',
            'inspector_name' => $check->checker?->name ?? '—',
            'result' => $check->result,
            'inspection_date' => $check->checked_at?->format('M j, Y H:i') ?? '—',
            'notes' => $check->comments,
            'rework_count' => (int) ($job?->rework_count ?? 0),
            'hold_reason' => $onHold ? ($job?->hold_reason ?: '—') : '—',
            'status_label' => $this->jobStatusLabel($job),
            'job_id' => $job?->id,
            'is_failed_row' => in_array($check->result, [QualityCheckResult::Failed, QualityCheckResult::ReworkRequired], true),
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

    protected function pendingInspectionsPaginator(Request $request): LengthAwarePaginator
    {
        $query = ProductionJobCard::query()
            ->forTenant()
            ->where('status', ProductionJobCardStatus::QualityCheck)
            ->with([
                'customer:id,company_name',
                'salesOrder.items:id,sales_order_id,item_name,description,quantity',
            ]);

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

    /**
     * @return array<string, mixed>
     */
    public function presentPendingRow(ProductionJobCard $job): array
    {
        return [
            'job_card_number' => $job->job_card_number,
            'customer_name' => $job->customer?->company_name ?? '—',
            'product' => $this->productDescription($job),
            'due_date' => $job->planned_end_date?->format('Y-m-d') ?? '—',
            'inspector_name' => '—',
            'status_label' => __('Pending inspection'),
            'job_id' => $job->id,
        ];
    }

    protected function productDescription(ProductionJobCard $jobCard): string
    {
        $items = $jobCard->salesOrder?->items ?? collect();
        if ($items->isEmpty()) {
            return str_replace('_', ' ', $jobCard->production_type->value);
        }

        return $items->take(2)->map(fn ($item) => $item->item_name ?: $item->description)->filter()->implode(', ');
    }

    protected function jobStatusLabel(?ProductionJobCard $job): string
    {
        if (! $job) {
            return '—';
        }

        if ($job->status === ProductionJobCardStatus::OnHold) {
            return __('On hold');
        }

        return str_replace('_', ' ', ucfirst($job->status->value));
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
