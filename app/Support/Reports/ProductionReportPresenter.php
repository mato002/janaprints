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
            'tabs' => $this->tabs(),
            'active_tab' => $tab,
            'active_tab_label' => $this->tabs()[$this->tabIndex($tab)]['label'] ?? $tab,
            'period_label' => $resolved['filters']['from_date'].' — '.$resolved['filters']['to_date'],
            'branch_label' => $this->branchLabel($resolved['branches'], $resolved['filters']['branch_id'] ?? null),
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

    protected function tabIndex(string $tab): int
    {
        $keys = array_keys(config('production_reports.tabs', []));

        $index = array_search($tab, $keys, true);

        return $index === false ? 0 : (int) $index;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \App\Models\Branch>  $branches
     */
    protected function branchLabel($branches, mixed $branchId): string
    {
        if (! $branchId) {
            return __('All branches');
        }

        return $branches->firstWhere('id', (int) $branchId)?->name ?? __('Branch');
    }

    /**
     * @param  array<string, mixed>  $tabData
     * @param  list<string>  $keys
     * @param  list<string>  $fullWidth
     * @return array<string, mixed>
     */
    protected function withSections(array $tabData, array $keys, array $fullWidth = []): array
    {
        $tabData['sections'] = collect($keys)
            ->filter(fn (string $key) => isset($tabData[$key]))
            ->map(fn (string $key) => [
                'key' => $key,
                'full_width' => in_array($key, $fullWidth, true),
                'table' => $tabData[$key],
            ])
            ->values()
            ->all();

        return $tabData;
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

        return $this->withSections([
            'type' => 'throughput',
            'summary' => [
                ['label' => __('Jobs Completed'), 'value' => (string) $this->queries->countJobsCompleted($scope), 'icon' => 'check-circle'],
                ['label' => __('Jobs Delayed'), 'value' => (string) $this->queries->countJobsDelayed($scope), 'icon' => 'clock'],
                ['label' => __('Jobs Cancelled'), 'value' => (string) $this->queries->countJobsCancelled($scope), 'icon' => 'x-circle'],
                ['label' => __('Average Turnaround'), 'value' => $avgTurnaround !== null ? $avgTurnaround.' '.__('days') : '—', 'icon' => 'calendar'],
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
                'highlight_utilization' => true,
            ],
        ], ['daily', 'departments', 'machines'], ['machines']);
    }

    /**
     * @return array<string, mixed>
     */
    protected function qualityTab(IntelligenceScope $scope): array
    {
        $rates = $this->queries->qualityRates($scope);

        return $this->withSections([
            'type' => 'quality',
            'summary' => [
                ['label' => __('Pass Rate'), 'value' => $rates['pass_rate'].'%', 'icon' => 'check-circle'],
                ['label' => __('Fail Rate'), 'value' => $rates['fail_rate'].'%', 'icon' => 'x-circle'],
                ['label' => __('Rework Rate'), 'value' => $rates['rework_rate'].'%', 'icon' => 'refresh'],
                ['label' => __('Hold Rate'), 'value' => $rates['hold_rate'].'%', 'icon' => 'pause'],
            ],
            'daily' => [
                'title' => __('Quality Checks By Day'),
                'columns' => [__('Date'), __('Checks'), __('Pass %'), __('Fail %'), __('Rework %')],
                'rows' => $this->queries->qualityByDay($scope),
            ],
            'fail_reasons' => [
                'title' => __('Quality Fail Reasons'),
                'columns' => [__('Reason'), __('Count'), __('Est. rework qty')],
                'rows' => $this->queries->qualityFailReasonRows($scope),
            ],
            'rework_summary' => [
                'title' => __('Rework Summary'),
                'columns' => [__('Job'), __('Reason'), __('Est. qty'), __('Actual qty'), __('Date')],
                'rows' => $this->queries->reworkSummaryRows($scope),
            ],
        ], ['daily', 'fail_reasons', 'rework_summary'], ['daily']);
    }

    /**
     * @return array<string, mixed>
     */
    protected function materialsTab(IntelligenceScope $scope): array
    {
        $cost = $this->queries->materialCostSummary($scope);

        return $this->withSections([
            'type' => 'materials',
            'summary' => [
                ['label' => __('Consumption Lines'), 'value' => (string) $cost['lines'], 'icon' => 'collection'],
                ['label' => __('Material Cost'), 'value' => $cost['total_cost'], 'icon' => 'currency-dollar'],
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
            'production_usage' => [
                'title' => __('Production Material Usage'),
                'columns' => [__('Material'), __('Issued Qty'), __('Issued Cost')],
                'rows' => $this->queries->productionMaterialUsageRows($scope),
            ],
            'variance' => [
                'title' => __('Material Variance'),
                'columns' => [__('Material'), __('Required'), __('Consumed'), __('Variance')],
                'rows' => $this->queries->materialVarianceRows($scope),
            ],
        ], ['consumption', 'waste', 'production_usage', 'variance']);
    }

    /**
     * @return array<string, mixed>
     */
    protected function dispatchTab(IntelligenceScope $scope): array
    {
        $metrics = $this->queries->dispatchMetrics($scope);
        $fulfilment = $this->queries->fulfilmentMetrics($scope);

        return $this->withSections([
            'type' => 'dispatch',
            'summary' => [
                ['label' => __('Ready For Collection'), 'value' => (string) $fulfilment['ready_for_collection'], 'icon' => 'inbox'],
                ['label' => __('Collected'), 'value' => (string) $fulfilment['collected'], 'icon' => 'check-circle'],
                ['label' => __('Delivered'), 'value' => (string) $fulfilment['delivered'], 'icon' => 'truck'],
                ['label' => __('Outstanding Collections'), 'value' => (string) $fulfilment['outstanding_collections'], 'icon' => 'clock'],
                ['label' => __('Outstanding Deliveries'), 'value' => (string) $fulfilment['outstanding_deliveries'], 'icon' => 'exclamation'],
                ['label' => __('Delivered Jobs (DN)'), 'value' => (string) $metrics['throughput'], 'icon' => 'document-text'],
            ],
            'ready_for_collection' => [
                'title' => __('Ready For Collection'),
                'columns' => [__('Job Card'), __('Order'), __('Prepared At'), __('Prepared By')],
                'rows' => $this->queries->readyForCollectionRows($scope),
            ],
            'collected' => [
                'title' => __('Collected Orders'),
                'columns' => [__('Job Card'), __('Order'), __('Collected By'), __('Collected At')],
                'rows' => $this->queries->collectedOrdersRows($scope),
            ],
            'delivered_orders' => [
                'title' => __('Delivered Orders'),
                'columns' => [__('Job Card'), __('Order'), __('Recipient'), __('Delivered At')],
                'rows' => $this->queries->fulfilmentDeliveredRows($scope),
            ],
            'outstanding_collections' => [
                'title' => __('Outstanding Collections'),
                'columns' => [__('Job Card'), __('Order'), __('Prepared At'), __('Days Waiting')],
                'rows' => $this->queries->outstandingCollectionsRows($scope),
            ],
            'outstanding_deliveries' => [
                'title' => __('Outstanding Deliveries'),
                'columns' => [__('Job Card'), __('Order'), __('Recipient'), __('Dispatched At')],
                'rows' => $this->queries->outstandingDeliveriesRows($scope),
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
        ], [
            'ready_for_collection',
            'collected',
            'delivered_orders',
            'outstanding_collections',
            'outstanding_deliveries',
            'delivered',
            'late',
        ], ['late']);
    }

    /**
     * @return array<string, mixed>
     */
    protected function profitabilityTab(IntelligenceScope $scope): array
    {
        return $this->withSections([
            'type' => 'profitability',
            'summary' => [],
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
        ], ['jobs', 'departments', 'customers'], ['jobs', 'departments', 'customers']);
    }
}
