<?php

namespace App\Support\Commercial\Reports;

use App\Support\Platform\PlatformCacheService;
use Illuminate\Http\Request;

class CommercialSalesOrderReportPresenter
{
    public function __construct(
        protected CommercialSalesOrderReportScopeResolver $scopeResolver,
        protected CommercialSalesOrderReportQueries $queries,
        protected PlatformCacheService $cache,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function present(Request $request): array
    {
        $resolved = $this->scopeResolver->resolve($request);
        $scope = $resolved['scope'];

        $kpis = $resolved['report_ready']
            ? $this->cachedKpis($scope)
            : $this->emptyKpis();

        return [
            'title' => __('Sales Order Reports'),
            'description' => __('Commercial order pipeline, fulfillment status, and conversion from quotations.'),
            'filters' => $resolved['filters'],
            'branches' => $resolved['branches'],
            'customers' => $resolved['customers'],
            'salespersons' => $resolved['salespersons'],
            'can_export' => $resolved['can_export'],
            'readiness' => $resolved['readiness'],
            'report_ready' => $resolved['report_ready'],
            'kpis' => $kpis,
            'tabs' => $this->tabs(),
            'active_tab' => $scope->tab,
            'tab_data' => $this->presentTab($scope),
        ];
    }

    /**
     * @return list<array{key: string, label: string}>
     */
    public function tabs(): array
    {
        return [
            ['key' => 'summary', 'label' => __('Sales Order Summary')],
            ['key' => 'open', 'label' => __('Open Orders')],
            ['key' => 'pending', 'label' => __('Pending Orders')],
            ['key' => 'completed', 'label' => __('Completed Orders')],
            ['key' => 'cancelled', 'label' => __('Cancelled Orders')],
            ['key' => 'by_customer', 'label' => __('Orders By Customer')],
            ['key' => 'by_branch', 'label' => __('Orders By Branch')],
            ['key' => 'by_salesperson', 'label' => __('Orders By Salesperson')],
            ['key' => 'aging', 'label' => __('Order Aging')],
            ['key' => 'value_analysis', 'label' => __('Order Value Analysis')],
            ['key' => 'awaiting_production', 'label' => __('Orders Awaiting Production')],
            ['key' => 'from_quotations', 'label' => __('Orders Converted From Quotations')],
        ];
    }

    /**
     * @return list<array{label: string, value: string, icon: string, hint: ?string}>
     */
    protected function cachedKpis(CommercialSalesOrderReportScope $scope): array
    {
        $ttl = (int) config('platform.cache.dashboard', 60);

        return $this->cache->remember(
            'dashboard',
            "commercial-sales-order-kpis:{$scope->companyId}:{$scope->cacheKey()}",
            fn () => $this->buildKpis($scope),
            $ttl,
        );
    }

    /**
     * @return list<array{label: string, value: string, icon: string, hint: ?string}>
     */
    protected function buildKpis(CommercialSalesOrderReportScope $scope): array
    {
        $conversion = $this->queries->quoteToOrderConversionPercent($scope);
        $completion = $this->queries->orderCompletionRatePercent($scope);

        return [
            ['label' => __('Total Orders'), 'value' => (string) $this->queries->totalOrders($scope), 'icon' => 'clipboard-list'],
            ['label' => __('Open Orders'), 'value' => (string) $this->queries->openOrders($scope), 'icon' => 'folder-open'],
            ['label' => __('Completed Orders'), 'value' => (string) $this->queries->completedOrders($scope), 'icon' => 'check-circle'],
            ['label' => __('Cancelled Orders'), 'value' => (string) $this->queries->cancelledOrders($scope), 'icon' => 'x-circle'],
            ['label' => __('Total Order Value'), 'value' => $this->queries->money($this->queries->totalOrderValue($scope)), 'icon' => 'currency-dollar'],
            ['label' => __('Average Order Value'), 'value' => $this->queries->money($this->queries->averageOrderValue($scope)), 'icon' => 'chart-bar'],
            ['label' => __('Orders Awaiting Production'), 'value' => (string) $this->queries->ordersAwaitingProduction($scope), 'icon' => 'clock'],
            ['label' => __('Quote-to-Order Conversion'), 'value' => $conversion !== null ? $conversion.'%' : '—', 'icon' => 'document-text'],
            ['label' => __('Order Completion Rate'), 'value' => $completion !== null ? $completion.'%' : '—', 'icon' => 'trending-up'],
        ];
    }

    /**
     * @return list<array{label: string, value: string, icon: string, hint: ?string}>
     */
    protected function emptyKpis(): array
    {
        $labels = [
            [__('Total Orders'), 'clipboard-list'],
            [__('Open Orders'), 'folder-open'],
            [__('Completed Orders'), 'check-circle'],
            [__('Cancelled Orders'), 'x-circle'],
            [__('Total Order Value'), 'currency-dollar'],
            [__('Average Order Value'), 'chart-bar'],
            [__('Orders Awaiting Production'), 'clock'],
            [__('Quote-to-Order Conversion'), 'document-text'],
            [__('Order Completion Rate'), 'trending-up'],
        ];

        return collect($labels)->map(fn (array $item) => [
            'label' => $item[0],
            'value' => '—',
            'icon' => $item[1],
            'hint' => __('Awaiting operational data sources'),
        ])->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function presentTab(CommercialSalesOrderReportScope $scope): array
    {
        if (! $this->queries->hasTable('sales_orders')) {
            return [
                'type' => 'placeholder',
                'message' => __('Sales order data is not available yet.'),
            ];
        }

        return match ($scope->tab) {
            'open' => [
                'type' => 'table',
                'columns' => [__('Order'), __('Customer'), __('Branch'), __('Salesperson'), __('Status'), __('Order Date'), __('Required Date'), __('Value'), __('Age (days)')],
                'rows' => $this->queries->paginateOpenOrders($scope),
            ],
            'pending' => [
                'type' => 'table',
                'columns' => [__('Order'), __('Customer'), __('Branch'), __('Salesperson'), __('Status'), __('Order Date'), __('Required Date'), __('Value'), __('Age (days)')],
                'rows' => $this->queries->paginatePendingOrders($scope),
            ],
            'completed' => [
                'type' => 'table',
                'columns' => [__('Order'), __('Customer'), __('Branch'), __('Salesperson'), __('Status'), __('Order Date'), __('Required Date'), __('Value'), __('Age (days)')],
                'rows' => $this->queries->paginateCompletedOrders($scope),
            ],
            'cancelled' => [
                'type' => 'table',
                'columns' => [__('Order'), __('Customer'), __('Branch'), __('Salesperson'), __('Status'), __('Order Date'), __('Required Date'), __('Value'), __('Age (days)')],
                'rows' => $this->queries->paginateCancelledOrders($scope),
            ],
            'by_customer' => [
                'type' => 'table',
                'columns' => [__('Customer'), __('Orders'), __('Value'), __('Average'), __('Open')],
                'rows' => $this->queries->paginateByCustomer($scope),
            ],
            'by_branch' => [
                'type' => 'table',
                'columns' => [__('Branch'), __('Orders'), __('Value'), __('Average'), __('Open')],
                'rows' => $this->queries->paginateByBranch($scope),
            ],
            'by_salesperson' => [
                'type' => 'table',
                'columns' => [__('Salesperson'), __('Orders'), __('Value'), __('Average'), __('Completed')],
                'rows' => $this->queries->paginateBySalesperson($scope),
            ],
            'aging' => [
                'type' => 'table',
                'columns' => [__('Age Bucket'), __('Orders'), __('Value')],
                'rows' => $this->queries->orderAgingBuckets($scope),
            ],
            'value_analysis' => [
                'type' => 'table',
                'columns' => [__('Value Bucket'), __('Orders'), __('Total Value'), __('Average')],
                'rows' => $this->queries->orderValueBuckets($scope),
            ],
            'awaiting_production' => [
                'type' => 'table',
                'columns' => [__('Order'), __('Customer'), __('Branch'), __('Salesperson'), __('Status'), __('Order Date'), __('Required Date'), __('Value'), __('Age (days)')],
                'rows' => $this->queries->paginateAwaitingProduction($scope),
            ],
            'from_quotations' => [
                'type' => 'table',
                'columns' => [__('Order'), __('Quotation'), __('Quote Date'), __('Customer'), __('Branch'), __('Salesperson'), __('Status'), __('Order Date'), __('Value')],
                'rows' => $this->queries->paginateFromQuotations($scope),
            ],
            default => [
                'type' => 'summary',
                'metrics' => $this->queries->summaryMetrics($scope),
                'status_breakdown' => $this->queries->statusBreakdown($scope),
            ],
        };
    }
}
