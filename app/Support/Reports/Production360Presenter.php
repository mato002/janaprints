<?php

namespace App\Support\Reports;

use App\Enums\ArtworkRequestStatus;
use App\Enums\ProductionJobCardStatus;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Branch;
use App\Models\Production\ProductionJobCard;
use App\Support\Reports\Concerns\BuildsIntelligenceSections;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Production360Presenter
{
    use BuildsIntelligenceSections;

    public function __construct(
        protected IntelligenceScopeResolver $scopeResolver,
        protected IntelligenceAggregateQueries $queries,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function present(Request $request): array
    {
        $resolved = $this->scopeResolver->resolve($request);
        $scope = $resolved['scope'];

        return [
            'title' => __('Production 360'),
            'description' => __('Job pipeline, delays, and production intelligence.'),
            'filters' => $resolved['filters'],
            'branches' => $resolved['branches'],
            'can_export' => $resolved['can_export'],
            'sections' => [
                $this->summary($scope),
                $this->pipeline($scope),
                $this->delayIntelligence($scope),
                $this->branchProduction($scope),
                $this->productMix($scope),
                $this->consumption($scope),
                $this->qualityPlaceholder(),
                $this->attention($scope),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function summary(IntelligenceScope $scope): array
    {
        if (! $this->queries->hasTable('production_job_cards')) {
            return $this->pendingSection(__('Production Summary'));
        }

        $base = $this->queries->scoped(ProductionJobCard::class, $scope);
        $awaitingArtwork = $this->queries->hasTable('artwork_requests')
            ? (int) $this->queries->scoped(ArtworkRequest::class, $scope)->whereIn('status', [
                ArtworkRequestStatus::Submitted,
                ArtworkRequestStatus::InDesign,
            ])->count()
            : 0;

        return $this->kpiSection(__('Production Summary'), [
            $this->kpi(__('Active jobs'), (string) $this->queries->countActiveJobs($scope), 'cog'),
            $this->kpi(__('Completed (period)'), (string) $this->queries->countCompletedJobsInPeriod($scope), 'check-circle'),
            $this->kpi(__('Delayed jobs'), (string) $this->queries->countDelayedJobs($scope), 'exclamation'),
            $this->kpi(__('Cancelled'), (string) (clone $base)->where('status', ProductionJobCardStatus::Cancelled)->count(), 'x-circle'),
            $this->kpi(__('Awaiting artwork'), (string) $awaitingArtwork, 'color-swatch'),
            $this->kpi(__('Ready for dispatch'), (string) (clone $base)->where('status', ProductionJobCardStatus::ReadyForDispatch)->count(), 'truck'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function pipeline(IntelligenceScope $scope): array
    {
        if (! $this->queries->hasTable('production_job_cards')) {
            return $this->pendingSection(__('Production Pipeline'));
        }

        return ['type' => 'pipeline', 'title' => __('Production Pipeline'), 'stages' => $this->queries->productionPipelineCounts($scope)];
    }

    /**
     * @return array<string, mixed>
     */
    protected function delayIntelligence(IntelligenceScope $scope): array
    {
        if (! $this->queries->hasTable('production_job_cards')) {
            return $this->pendingSection(__('Delay Intelligence'));
        }

        $byBranch = ProductionJobCard::query()
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->whereNotIn('status', [ProductionJobCardStatus::Completed, ProductionJobCardStatus::Cancelled, ProductionJobCardStatus::ReadyForDispatch])
            ->whereNotNull('planned_end_date')
            ->whereDate('planned_end_date', '<', $scope->toDate)
            ->select('branch_id', DB::raw('COUNT(*) as cnt'))
            ->groupBy('branch_id')
            ->get();

        $branchNames = Branch::query()->whereIn('id', $byBranch->pluck('branch_id'))->pluck('name', 'id');

        return [
            'type' => 'split',
            'title' => __('Delay Intelligence'),
            'kpis' => [
                $this->kpi(__('Jobs past planned end'), (string) $this->queries->countDelayedJobs($scope), 'exclamation'),
                $this->kpi(__('Average delay days'), '—', 'clock', __('Pending source')),
            ],
            'tables' => [
                $this->tableSection(
                    __('Delays by Branch'),
                    [__('Branch'), __('Count')],
                    $byBranch->map(fn ($r) => ['branch' => $branchNames[$r->branch_id] ?? '—', 'count' => (string) $r->cnt])->all(),
                ),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function branchProduction(IntelligenceScope $scope): array
    {
        if (! $this->queries->hasTable('production_job_cards')) {
            return $this->pendingSection(__('Branch Production'));
        }

        $rows = Branch::query()
            ->where('company_id', $scope->companyId)
            ->where('is_active', true)
            ->when($scope->branchId, fn ($q) => $q->where('id', $scope->branchId))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(function (Branch $branch) use ($scope) {
                $scoped = new IntelligenceScope($scope->companyId, $branch->id, $scope->fromDate, $scope->toDate);
                $active = $this->queries->countActiveJobs($scoped);
                $completed = $this->queries->countCompletedJobsInPeriod($scoped);
                $total = $active + $completed;

                return [
                    'branch' => $branch->name,
                    'active' => (string) $active,
                    'completed' => (string) $completed,
                    'delayed' => (string) $this->queries->countDelayedJobs($scoped),
                    'rate' => $total > 0 ? round(($completed / $total) * 100).'%' : '0%',
                    'dispatch' => (string) $this->queries->scoped(ProductionJobCard::class, $scoped)
                        ->where('status', ProductionJobCardStatus::ReadyForDispatch)->count(),
                ];
            })
            ->all();

        return $this->tableSection(
            __('Branch Production'),
            [__('Branch'), __('Active'), __('Completed'), __('Delayed'), __('Completion %'), __('Ready Dispatch')],
            $rows,
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function productMix(IntelligenceScope $scope): array
    {
        return $this->pendingSection(__('Job Type / Product Mix'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function consumption(IntelligenceScope $scope): array
    {
        if (! $this->queries->hasTable('production_material_consumptions')) {
            return $this->pendingSection(__('Material Consumption'));
        }

        $count = (int) DB::table('production_material_consumptions')
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->whereDate('consumed_at', '>=', $scope->fromDate)
            ->whereDate('consumed_at', '<=', $scope->toDate)
            ->count();

        return $this->kpiSection(__('Material Consumption'), [
            $this->kpi(__('Consumption lines'), (string) $count, 'cog'),
            $this->kpi(__('Total consumed value'), '—', 'currency-dollar', __('Pending source')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function qualityPlaceholder(): array
    {
        return $this->kpiSection(__('Quality / Rework'), [
            $this->kpi(__('QC pass rate'), '—', 'clipboard-check', __('Pending source'), true),
            $this->kpi(__('Rework jobs'), '—', 'cog', __('Pending source'), true),
            $this->kpi(__('Rejection rate'), '—', 'exclamation', __('Pending source'), true),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function attention(IntelligenceScope $scope): array
    {
        return [
            'type' => 'attention',
            'title' => __('Attention Center'),
            'items' => [
                ['label' => __('Delayed jobs'), 'count' => $this->queries->countDelayedJobs($scope), 'severity' => 'danger'],
                ['label' => __('Ready for dispatch'), 'count' => $this->queries->hasTable('production_job_cards')
                    ? (int) $this->queries->scoped(ProductionJobCard::class, $scope)->where('status', ProductionJobCardStatus::ReadyForDispatch)->count()
                    : 0, 'severity' => 'warning'],
            ],
        ];
    }
}
