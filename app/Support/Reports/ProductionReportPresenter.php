<?php

namespace App\Support\Reports;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class ProductionReportPresenter
{
    public function __construct(
        protected ProductionReportScopeResolver $scopeResolver,
        protected ProductionReportQueries $queries,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function present(Request $request): array
    {
        $resolved = $this->scopeResolver->resolve($request);
        $scope = $resolved['scope'];
        $tab = $resolved['tab'];

        return [
            'title' => __(config('production_reports.title', 'Production Reports')),
            'description' => __(config('production_reports.description', 'Historical production performance reports.')),
            'filters' => $resolved['filters'],
            'branches' => $resolved['branches'],
            'can_export' => $resolved['can_export'],
            'catalog' => $this->catalog(),
            'tabs' => $this->tabs(),
            'active_tab' => $tab,
            'tab_data' => $this->presentTab($scope, $tab),
            'schedule_frequencies' => config('production_reports.schedule_frequencies', []),
            'export_url' => Route::has('admin.reports.production.export')
                ? route('admin.reports.production.export', $request->query())
                : null,
            'print_url' => Route::has('admin.reports.production.print')
                ? route('admin.reports.production.print', $request->query())
                : null,
        ];
    }

    /**
     * @return list<array{group: string, reports: list<array{key: string, label: string}>}>
     */
    public function catalog(): array
    {
        return collect(config('production_reports.tabs', []))
            ->map(fn (array $group, string $key) => [
                'group' => $group['label'] ?? $key,
                'reports' => collect($group['reports'] ?? [])
                    ->map(fn (string $label, string $reportKey) => [
                        'key' => $reportKey,
                        'label' => $label,
                    ])
                    ->values()
                    ->all(),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{key: string, label: string}>
     */
    public function tabs(): array
    {
        return collect(config('production_reports.tabs', []))
            ->map(fn (array $group, string $key) => [
                'key' => $key,
                'label' => __($group['label'] ?? $key),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function presentTab(IntelligenceScope $scope, string $tab): array
    {
        return match ($tab) {
            'throughput' => $this->throughputTab($scope),
            'quality' => $this->qualityTab($scope),
            'materials' => $this->materialsTab($scope),
            'dispatch' => $this->dispatchTab($scope),
            'profitability' => $this->profitabilityTab($scope),
            default => ['type' => 'placeholder', 'message' => __('Select a report tab.')],
        };
    }

    /**
     * @return array<string, mixed>
     */
    protected function throughputTab(IntelligenceScope $scope): array
    {
        $avgTurnaround = $this->queries->averageTurnaroundDays($scope);

        return [
            'type' => 'throughput',
            'summary' => [
                ['label' => __('Jobs Completed'), 'value' => (string) $this->queries->countJobsCompleted($scope)],
                ['label' => __('Jobs Delayed'), 'value' => (string) $this->queries->countJobsDelayed($scope)],
                ['label' => __('Jobs Cancelled'), 'value' => (string) $this->queries->countJobsCancelled($scope)],
                ['label' => __('Average Turnaround'), 'value' => $avgTurnaround !== null ? $avgTurnaround.' '.__('days') : '—'],
            ],
            'daily' => [
                'title' => __('Daily Throughput'),
                'columns' => [__('Date'), __('Completed'), __('Delayed'), __('Cancelled')],
                'rows' => $this->queries->throughputByDay($scope),
            ],
            'departments' => [
                'title' => __('Department Throughput'),
                'columns' => [__('Department'), __('Jobs Completed')],
                'rows' => $this->queries->departmentThroughput($scope),
            ],
            'machines' => [
                'title' => __('Machine Utilization'),
                'columns' => [__('Work Center'), __('Code'), __('Jobs Completed'), __('Utilization')],
                'rows' => $this->queries->machineUtilization($scope),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function qualityTab(IntelligenceScope $scope): array
    {
        $rates = $this->queries->qualityRates($scope);

        return [
            'type' => 'quality',
            'summary' => [
                ['label' => __('Pass Rate'), 'value' => $rates['pass_rate'].'%'],
                ['label' => __('Fail Rate'), 'value' => $rates['fail_rate'].'%'],
                ['label' => __('Rework Rate'), 'value' => $rates['rework_rate'].'%'],
                ['label' => __('Hold Rate'), 'value' => $rates['hold_rate'].'%'],
            ],
            'daily' => [
                'title' => __('Quality Checks By Day'),
                'columns' => [__('Date'), __('Checks'), __('Pass %'), __('Fail %'), __('Rework %')],
                'rows' => $this->queries->qualityByDay($scope),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function materialsTab(IntelligenceScope $scope): array
    {
        $cost = $this->queries->materialCostSummary($scope);

        return [
            'type' => 'materials',
            'summary' => [
                ['label' => __('Consumption Lines'), 'value' => (string) $cost['lines']],
                ['label' => __('Material Cost'), 'value' => $cost['total_cost']],
            ],
            'consumption' => [
                'title' => __('Material Consumption'),
                'columns' => [__('Material'), __('Quantity'), __('Cost')],
                'rows' => $this->queries->materialConsumptionRows($scope),
            ],
            'waste' => [
                'title' => __('Waste Analysis'),
                'columns' => [__('Material'), __('Waste Qty'), __('Waste Cost')],
                'rows' => $this->queries->wasteAnalysisRows($scope),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function dispatchTab(IntelligenceScope $scope): array
    {
        $metrics = $this->queries->dispatchMetrics($scope);

        return [
            'type' => 'dispatch',
            'summary' => [
                ['label' => __('Delivered Jobs'), 'value' => (string) $metrics['throughput']],
                ['label' => __('Late Deliveries'), 'value' => (string) $metrics['late']],
                ['label' => __('Delivery Success'), 'value' => $metrics['on_time_rate'].'%'],
            ],
            'delivered' => [
                'title' => __('Delivered Jobs'),
                'columns' => [__('Job Card'), __('Delivery Note'), __('Delivered At'), __('Planned Date')],
                'rows' => $this->queries->deliveredJobsRows($scope),
            ],
            'late' => [
                'title' => __('Late Deliveries'),
                'columns' => [__('Job Card'), __('Delivery Note'), __('Planned Date'), __('Delivered At'), __('Days Late')],
                'rows' => $this->queries->lateDeliveriesRows($scope),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function profitabilityTab(IntelligenceScope $scope): array
    {
        return [
            'type' => 'profitability',
            'jobs' => [
                'title' => __('Job Profitability'),
                'columns' => [__('Job Card'), __('Revenue'), __('Cost'), __('Profit'), __('Margin')],
                'rows' => $this->queries->jobProfitabilityRows($scope),
            ],
            'departments' => [
                'title' => __('Department Profitability'),
                'columns' => [__('Production Type'), __('Jobs'), __('Revenue'), __('Cost'), __('Profit'), __('Margin')],
                'rows' => $this->queries->departmentProfitabilityRows($scope),
            ],
            'customers' => [
                'title' => __('Customer Profitability'),
                'columns' => [__('Customer'), __('Jobs'), __('Revenue'), __('Cost'), __('Profit'), __('Margin')],
                'rows' => $this->queries->customerProfitabilityRows($scope),
            ],
        ];
    }
}
