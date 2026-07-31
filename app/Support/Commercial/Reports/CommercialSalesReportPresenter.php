<?php

namespace App\Support\Commercial\Reports;

use App\Support\Platform\PlatformCacheService;
use Illuminate\Http\Request;

class CommercialSalesReportPresenter
{
    public function __construct(
        protected CommercialSalesReportScopeResolver $scopeResolver,
        protected CommercialSalesReportQueries $queries,
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

        $tab = $this->presentTab($scope);

        return [
            'title' => __('Sales Reports'),
            'description' => __('Commercial department sales performance from operational orders, customers, and quotations.'),
            'filters' => $resolved['filters'],
            'branches' => $resolved['branches'],
            'customers' => $resolved['customers'],
            'salespersons' => $resolved['salespersons'],
            'can_export' => $resolved['can_export'],
            'can_manage' => $resolved['can_manage'],
            'readiness' => $resolved['readiness'],
            'report_ready' => $resolved['report_ready'],
            'kpis' => $kpis,
            'tabs' => $this->tabs(),
            'active_tab' => $scope->tab,
            'tab_data' => $tab,
        ];
    }

    /**
     * @return list<array{key: string, label: string}>
     */
    public function tabs(): array
    {
        return [
            ['key' => 'summary', 'label' => __('Sales Summary')],
            ['key' => 'by_day', 'label' => __('Sales By Day')],
            ['key' => 'by_week', 'label' => __('Sales By Week')],
            ['key' => 'by_month', 'label' => __('Sales By Month')],
            ['key' => 'by_customer', 'label' => __('Sales By Customer')],
            ['key' => 'by_branch', 'label' => __('Sales By Branch')],
            ['key' => 'by_salesperson', 'label' => __('Sales By Salesperson')],
            ['key' => 'top_customers', 'label' => __('Top Customers')],
            ['key' => 'lost_orders', 'label' => __('Lost Orders')],
            ['key' => 'trends', 'label' => __('Sales Trends')],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function cachedKpis(CommercialSalesReportScope $scope): array
    {
        $ttl = (int) config('platform.cache.dashboard', 60);

        return $this->cache->remember(
            'dashboard',
            "commercial-sales-kpis:{$scope->companyId}:{$scope->cacheKey()}",
            fn () => $this->buildKpis($scope),
            $ttl,
        );
    }

    /**
     * @return list<array{label: string, value: string, icon: string, hint: ?string}>
     */
    protected function buildKpis(CommercialSalesReportScope $scope): array
    {
        $growth = $this->queries->salesGrowthPercent($scope);

        return [
            ['label' => __('Revenue'), 'value' => $this->queries->money($this->queries->totalSales($scope)), 'icon' => 'currency-dollar'],
            ['label' => __('Orders'), 'value' => (string) $this->queries->totalOrders($scope), 'icon' => 'clipboard-list'],
            ['label' => __('Customers'), 'value' => (string) $this->queries->activeCustomers($scope), 'icon' => 'user-circle'],
            ['label' => __('Growth %'), 'value' => $growth !== null ? $growth.'%' : '—', 'icon' => 'trending-up'],
            ['label' => __('Average Order Value'), 'value' => $this->queries->money($this->queries->averageOrderValue($scope)), 'icon' => 'chart-bar'],
        ];
    }

    /**
     * @return list<array{label: string, value: string, icon: string, hint: ?string}>
     */
    protected function emptyKpis(): array
    {
        $labels = [
            [__('Revenue'), 'currency-dollar'],
            [__('Orders'), 'clipboard-list'],
            [__('Customers'), 'user-circle'],
            [__('Growth %'), 'trending-up'],
            [__('Average Order Value'), 'chart-bar'],
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
    protected function presentTab(CommercialSalesReportScope $scope): array
    {
        if (! $this->queries->hasTable('sales_orders')) {
            return [
                'type' => 'placeholder',
                'message' => __('Sales order data is not available yet.'),
            ];
        }

        return match ($scope->tab) {
            'by_day' => [
                'type' => 'table',
                'columns' => [__('Date'), __('Orders'), __('Revenue'), __('Average Order Value'), __('Trend')],
                'rows' => $this->queries->paginateByDay($scope),
            ],
            'by_week' => [
                'type' => 'table',
                'columns' => [__('Week'), __('Orders'), __('Revenue'), __('Customers'), __('Growth %')],
                'rows' => $this->queries->paginateByWeek($scope),
            ],
            'by_month' => [
                'type' => 'table',
                'columns' => [__('Month'), __('Orders'), __('Revenue'), __('Customers'), __('Growth %')],
                'rows' => $this->queries->paginateByMonth($scope),
            ],
            'by_customer' => [
                'type' => 'table',
                'columns' => [__('Customer'), __('Orders'), __('Revenue'), __('Last Order'), __('Average Order'), __('Lifetime Value')],
                'rows' => $this->queries->paginateByCustomer($scope),
            ],
            'by_branch' => [
                'type' => 'table',
                'columns' => [__('Branch'), __('Orders'), __('Revenue'), __('Customers'), __('Average Order')],
                'rows' => $this->queries->paginateByBranch($scope),
            ],
            'by_salesperson' => [
                'type' => 'table',
                'columns' => [__('Salesperson'), __('Orders'), __('Revenue'), __('Customers'), __('Average Order'), __('Conversion %')],
                'rows' => $this->queries->paginateBySalesperson($scope),
            ],
            'top_customers' => [
                'type' => 'top_customers',
                'columns' => [__('Customer'), __('Orders'), __('Revenue'), __('Lifetime Value')],
                'rows' => $this->queries->topCustomers($scope),
            ],
            'lost_orders' => [
                'type' => 'lost_orders',
                'data' => $this->queries->lostOrders($scope),
            ],
            'trends' => [
                'type' => 'trends',
                'series' => $this->queries->trendSeries($scope),
            ],
            default => [
                'type' => 'summary',
                'branch_breakdown' => $this->queries->branchBreakdown($scope),
                'salesperson_breakdown' => $this->queries->salespersonBreakdown($scope),
            ],
        };
    }
}
