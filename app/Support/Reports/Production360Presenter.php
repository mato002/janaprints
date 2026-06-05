<?php

namespace App\Support\Reports;

use App\Enums\ProductionJobCardStatus;
use App\Support\Reports\Concerns\BuildsIntelligenceSections;
use Illuminate\Http\Request;

class Production360Presenter
{
    use BuildsIntelligenceSections;

    public function __construct(
        protected IntelligenceScopeResolver $scopeResolver,
        protected IntelligenceAggregateQueries $queries,
        protected Production360Queries $production,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function present(Request $request): array
    {
        $resolved = $this->scopeResolver->resolve($request, defaultBranchFromTenant: false);
        $scope = $resolved['scope'];

        return [
            'title' => __('Production 360'),
            'description' => __('Executive production intelligence for operations leadership — read-only analytics across branches, capacity, quality, and dispatch.'),
            'filters' => $resolved['filters'],
            'branches' => $resolved['branches'],
            'can_export' => $resolved['can_export'],
            'read_only' => true,
            'export_url' => $resolved['can_export']
                ? route('admin.reports.production360', array_merge($request->query(), ['export' => 'csv']))
                : null,
            'sections' => [
                $this->summary($scope),
                $this->branchComparison($scope),
                $this->productMix($scope),
                $this->jobTypeMix($scope),
                $this->materialConsumption($scope),
                $this->materialCostConsumption($scope),
                $this->delayIntelligence($scope),
                $this->qualityIntelligence($scope),
                $this->capacityIntelligence($scope),
                $this->dispatchIntelligence($scope),
                $this->trends($scope),
                $this->performers($scope),
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

        $material = $this->production->materialConsumption($scope);
        $dispatch = $this->production->dispatchMetrics($scope);

        return $this->kpiSection(__('Production Summary'), [
            $this->kpi(__('Active jobs'), (string) $this->queries->countActiveJobs($scope), 'cog'),
            $this->kpi(__('Completed (period)'), (string) $this->queries->countCompletedJobsInPeriod($scope), 'check-circle'),
            $this->kpi(__('Delayed jobs'), (string) $this->queries->countDelayedJobs($scope), 'exclamation'),
            $this->kpi(__('Ready for dispatch'), (string) $this->queries->scoped(\App\Models\Production\ProductionJobCard::class, $scope)
                ->where('status', ProductionJobCardStatus::ReadyForDispatch)->count(), 'truck'),
            $this->kpi(__('Material lines'), (string) $material['lines'], 'cube'),
            $this->kpi(__('Material cost'), $this->queries->money($material['total_cost']), 'currency-dollar'),
            $this->kpi(__('Deliveries (period)'), (string) $dispatch['throughput'], 'truck'),
            $this->kpi(__('On-time delivery'), $dispatch['on_time_rate'].'%', 'check-circle'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function branchComparison(IntelligenceScope $scope): array
    {
        if (! $this->queries->hasTable('production_job_cards')) {
            return $this->pendingSection(__('Branch Comparison'));
        }

        return $this->drilldownTable(
            __('Branch Comparison'),
            [__('Branch'), __('Completed'), __('Delayed'), __('Completion %')],
            $this->production->branchComparisonRows($scope),
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function productMix(IntelligenceScope $scope): array
    {
        if (! $this->queries->hasTable('production_job_cards')) {
            return $this->pendingSection(__('Product Mix'));
        }

        if (! $this->queries->hasTable('production_material_consumptions')) {
            return $this->pendingSection(__('Product Mix'));
        }

        return $this->drilldownTable(
            __('Product Mix'),
            [__('Material'), __('Quantity'), __('Cost')],
            $this->production->productMix($scope),
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function jobTypeMix(IntelligenceScope $scope): array
    {
        if (! $this->queries->hasTable('production_job_cards')) {
            return $this->pendingSection(__('Job Type Mix'));
        }

        return $this->drilldownTable(
            __('Job Type Mix'),
            [__('Job type'), __('Count')],
            $this->production->jobTypeMix($scope),
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function materialConsumption(IntelligenceScope $scope): array
    {
        $material = $this->production->materialConsumption($scope);

        if (! $this->queries->hasTable('production_material_consumptions')) {
            return $this->pendingSection(__('Material Consumption'));
        }

        return [
            'type' => 'split',
            'title' => __('Material Consumption'),
            'kpis' => [
                $this->kpi(__('Consumption lines'), (string) $material['lines'], 'cube'),
                $this->kpi(__('Unique materials'), (string) count($material['top_materials']), 'collection'),
            ],
            'tables' => [
                $this->drilldownTable(
                    __('Top materials by consumption'),
                    [__('Material'), __('Quantity'), __('Cost')],
                    $material['top_materials'],
                ),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function materialCostConsumption(IntelligenceScope $scope): array
    {
        $material = $this->production->materialConsumption($scope);

        if (! $this->queries->hasTable('production_material_consumptions')) {
            return $this->pendingSection(__('Material Cost Consumption'));
        }

        return $this->kpiSection(__('Material Cost Consumption'), [
            $this->kpi(__('Total material cost'), $this->queries->money($material['total_cost']), 'currency-dollar'),
            $this->kpi(__('Avg cost per line'), $material['lines'] > 0
                ? $this->queries->money($material['total_cost'] / $material['lines'])
                : $this->queries->money(0), 'chart-bar'),
            $this->kpi(__('Consumption lines'), (string) $material['lines'], 'cube'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function delayIntelligence(IntelligenceScope $scope): array
    {
        if (! $this->queries->hasTable('production_job_cards')) {
            return $this->pendingSection(__('Delay Intelligence'));
        }

        $avgDelay = $this->production->averageDelayDays($scope);

        return [
            'type' => 'split',
            'title' => __('Delay Intelligence'),
            'kpis' => [
                $this->kpi(__('Jobs past planned end'), (string) $this->queries->countDelayedJobs($scope), 'exclamation'),
                $this->kpi(__('Average delay'), $avgDelay !== null ? $avgDelay.' '.__('days') : '—', 'clock'),
            ],
            'tables' => [
                $this->drilldownTable(
                    __('Most Delayed Jobs'),
                    [__('Job'), __('Customer'), __('Branch'), __('Days late'), __('Due')],
                    $this->production->mostDelayedJobs($scope),
                ),
                $this->drilldownTable(
                    __('Most Delayed Departments'),
                    [__('Department'), __('Delayed jobs')],
                    $this->production->mostDelayedDepartments($scope),
                ),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function qualityIntelligence(IntelligenceScope $scope): array
    {
        if (! $this->queries->hasTable('quality_checks')) {
            return $this->pendingSection(__('Quality Intelligence'));
        }

        $rates = $this->production->qualityRates($scope);

        return $this->kpiSection(__('Quality Intelligence'), [
            $this->kpi(__('Pass rate'), $rates['pass_rate'].'%', 'check-circle', __(':count inspections', ['count' => $rates['total_checks']])),
            $this->kpi(__('Failure rate'), $rates['fail_rate'].'%', 'x-circle'),
            $this->kpi(__('Rework rate'), $rates['rework_rate'].'%', 'switch-horizontal'),
            $this->kpi(__('Hold rate'), $rates['hold_rate'].'%', 'pause'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function capacityIntelligence(IntelligenceScope $scope): array
    {
        $capacity = $this->production->capacityMetrics($scope);

        if (! $this->queries->hasTable('work_centers')) {
            return $this->pendingSection(__('Capacity Intelligence'));
        }

        return [
            'type' => 'split',
            'title' => __('Capacity Intelligence'),
            'kpis' => [
                $this->kpi(__('Work center utilization'), $capacity['work_center'].'%', 'cog'),
                $this->kpi(__('Machine utilization'), $capacity['machine'].'%', 'chip'),
                $this->kpi(__('Department utilization'), $capacity['department'].'%', 'office-building'),
            ],
            'tables' => [
                $this->drilldownTable(
                    __('Department utilization'),
                    [__('Department'), __('Utilization')],
                    $capacity['departments'],
                ),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function dispatchIntelligence(IntelligenceScope $scope): array
    {
        if (! $this->queries->hasTable('delivery_notes')) {
            return $this->pendingSection(__('Dispatch Intelligence'));
        }

        $dispatch = $this->production->dispatchMetrics($scope);

        return $this->kpiSection(__('Dispatch Intelligence'), [
            $this->kpi(__('On-time delivery'), (string) $dispatch['on_time'], 'check-circle'),
            $this->kpi(__('Late delivery'), (string) $dispatch['late'], 'exclamation'),
            $this->kpi(__('Delivery throughput'), (string) $dispatch['throughput'], 'truck'),
            $this->kpi(__('On-time rate'), $dispatch['on_time_rate'].'%', 'chart-pie'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function trends(IntelligenceScope $scope): array
    {
        if (! $this->queries->hasTable('production_job_cards')) {
            return $this->pendingSection(__('Trend Charts'));
        }

        return [
            'type' => 'trends',
            'title' => __('Trend Charts'),
            'charts' => [
                $this->chartSection(
                    __('Completed jobs'),
                    $this->production->completedJobsTrend($scope),
                    __('Daily completions in selected period'),
                ),
                $this->chartSection(
                    __('Delayed jobs'),
                    $this->production->delayTrend($scope),
                    __('Open jobs past planned end by day'),
                ),
                $this->chartSection(
                    __('Deliveries'),
                    $this->production->deliveryTrend($scope),
                    __('Delivered notes per day'),
                ),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function performers(IntelligenceScope $scope): array
    {
        $branches = $this->production->branchPerformance($scope);

        if ($branches === []) {
            return $this->pendingSection(__('Top & Bottom Performers'));
        }

        $top = collect($branches)->take(3)->map(fn (array $row) => [
            'label' => $row['branch'],
            'value' => $row['score'].'%',
            'hint' => __(':completed completed · :delayed delayed', [
                'completed' => $row['completed'],
                'delayed' => $row['delayed'],
            ]),
        ])->all();

        $bottom = collect($branches)->sortBy('score')->take(3)->map(fn (array $row) => [
            'label' => $row['branch'],
            'value' => $row['score'].'%',
            'hint' => __(':completed completed · :delayed delayed', [
                'completed' => $row['completed'],
                'delayed' => $row['delayed'],
            ]),
        ])->values()->all();

        $capacity = $this->production->capacityMetrics($scope);
        $deptTop = collect($capacity['departments'])
            ->sortByDesc(fn (array $row) => (int) rtrim($row['cells'][1], '%'))
            ->take(3)
            ->map(fn (array $row) => ['label' => $row['cells'][0], 'value' => $row['cells'][1]])
            ->all();
        $deptBottom = collect($capacity['departments'])
            ->sortBy(fn (array $row) => (int) rtrim($row['cells'][1], '%'))
            ->take(3)
            ->map(fn (array $row) => ['label' => $row['cells'][0], 'value' => $row['cells'][1]])
            ->values()
            ->all();

        return [
            'type' => 'performers',
            'title' => __('Top & Bottom Performers'),
            'groups' => [
                ['heading' => __('Top branches (completion %)'), 'items' => $top],
                ['heading' => __('Bottom branches (completion %)'), 'items' => $bottom],
                ['heading' => __('Highest utilization departments'), 'items' => $deptTop],
                ['heading' => __('Lowest utilization departments'), 'items' => $deptBottom],
            ],
        ];
    }
}
