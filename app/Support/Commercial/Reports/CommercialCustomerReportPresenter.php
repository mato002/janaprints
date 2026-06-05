<?php

namespace App\Support\Commercial\Reports;

use App\Enums\CustomerStatus;
use App\Support\Platform\PlatformCacheService;
use Illuminate\Http\Request;

class CommercialCustomerReportPresenter
{
    public function __construct(
        protected CommercialCustomerReportScopeResolver $scopeResolver,
        protected CommercialCustomerReportQueries $queries,
        protected PlatformCacheService $cache,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function present(Request $request): array
    {
        $resolved = $this->scopeResolver->resolve($request);
        $scope = $resolved['scope'];

        return [
            'title' => __('Customer Reports'),
            'description' => __('Commercial customer analytics from operational CRM, orders, and quotations — not Customer 360 or Executive Intelligence.'),
            'filters' => $resolved['filters'],
            'branches' => $resolved['branches'],
            'salespersons' => $resolved['salespersons'],
            'can_export' => $resolved['can_export'],
            'readiness' => $resolved['readiness'],
            'report_ready' => $resolved['report_ready'],
            'kpis' => $resolved['report_ready'] ? $this->cachedKpis($scope) : $this->emptyKpis(),
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
            ['key' => 'summary', 'label' => __('Customer Summary')],
            ['key' => 'new', 'label' => __('New Customers')],
            ['key' => 'active', 'label' => __('Active Customers')],
            ['key' => 'inactive', 'label' => __('Inactive Customers')],
            ['key' => 'revenue', 'label' => __('Customer Revenue')],
            ['key' => 'lifetime', 'label' => __('Customer Lifetime Value')],
            ['key' => 'activity', 'label' => __('Customer Activity')],
            ['key' => 'top', 'label' => __('Top Customers')],
            ['key' => 'growth', 'label' => __('Customer Growth')],
            ['key' => 'no_recent_orders', 'label' => __('Customers Without Recent Orders')],
            ['key' => 'by_branch', 'label' => __('Customer By Branch')],
            ['key' => 'by_salesperson', 'label' => __('Customer By Salesperson')],
        ];
    }

    /**
     * @return list<array{label: string, value: string, icon: string, hint: ?string}>
     */
    protected function cachedKpis(CommercialCustomerReportScope $scope): array
    {
        return $this->cache->remember(
            'dashboard',
            "commercial-customer-kpis:{$scope->companyId}:{$scope->cacheKey()}",
            fn () => $this->buildKpis($scope),
            (int) config('platform.cache.dashboard', 60),
        );
    }

    /**
     * @return list<array{label: string, value: string, icon: string, hint: ?string}>
     */
    protected function buildKpis(CommercialCustomerReportScope $scope): array
    {
        $growth = $this->queries->customerGrowthPercent($scope);

        return [
            ['label' => __('Total Customers'), 'value' => (string) $this->queries->totalCustomers($scope), 'icon' => 'user-circle'],
            ['label' => __('New Customers'), 'value' => (string) $this->queries->newCustomers($scope), 'icon' => 'sparkles'],
            ['label' => __('Active Customers'), 'value' => (string) $this->queries->activeStatusCustomers($scope), 'icon' => 'check-circle'],
            ['label' => __('Inactive Customers'), 'value' => (string) $this->queries->inactiveStatusCustomers($scope), 'icon' => 'x-circle'],
            ['label' => __('Repeat Customers'), 'value' => (string) $this->queries->repeatCustomers($scope), 'icon' => 'refresh'],
            ['label' => __('Customer Growth %'), 'value' => $growth !== null ? $growth.'%' : '—', 'icon' => 'trending-up'],
            ['label' => __('Average Customer Value'), 'value' => $this->queries->money($this->queries->averageCustomerValue($scope)), 'icon' => 'chart-bar'],
            ['label' => __('Top Customer Revenue'), 'value' => $this->queries->money($this->queries->topCustomerRevenue($scope)), 'icon' => 'currency-dollar'],
            ['label' => __('Customers With Open Quotes'), 'value' => (string) $this->queries->customersWithOpenQuotes($scope), 'icon' => 'document-text'],
            ['label' => __('Customers With Open Orders'), 'value' => (string) $this->queries->customersWithOpenOrders($scope), 'icon' => 'clipboard-list'],
        ];
    }

    /**
     * @return list<array{label: string, value: string, icon: string, hint: ?string}>
     */
    protected function emptyKpis(): array
    {
        $labels = [
            [__('Total Customers'), 'user-circle'],
            [__('New Customers'), 'sparkles'],
            [__('Active Customers'), 'check-circle'],
            [__('Inactive Customers'), 'x-circle'],
            [__('Repeat Customers'), 'refresh'],
            [__('Customer Growth %'), 'trending-up'],
            [__('Average Customer Value'), 'chart-bar'],
            [__('Top Customer Revenue'), 'currency-dollar'],
            [__('Customers With Open Quotes'), 'document-text'],
            [__('Customers With Open Orders'), 'clipboard-list'],
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
    protected function presentTab(CommercialCustomerReportScope $scope): array
    {
        if (! $this->queries->hasTable('customers')) {
            return [
                'type' => 'placeholder',
                'message' => __('Customer data is not available yet.'),
            ];
        }

        return match ($scope->tab) {
            'new' => [
                'type' => 'table',
                'columns' => [__('Customer'), __('Code'), __('Type'), __('Status'), __('Orders'), __('Revenue'), __('Open Quotes'), __('Created')],
                'rows' => $this->queries->paginateCustomerList($scope, newOnly: true),
            ],
            'active' => [
                'type' => 'table',
                'columns' => [__('Customer'), __('Code'), __('Type'), __('Status'), __('Orders'), __('Revenue'), __('Last Order'), __('Open Quotes')],
                'rows' => $this->queries->paginateCustomerList($scope, statusFilter: CustomerStatus::Active->value),
            ],
            'inactive' => [
                'type' => 'table',
                'columns' => [__('Customer'), __('Code'), __('Type'), __('Status'), __('Orders'), __('Revenue'), __('Last Order'), __('Open Quotes')],
                'rows' => $this->queries->paginateCustomerList($scope, statusFilter: CustomerStatus::Inactive->value),
            ],
            'revenue' => [
                'type' => 'table',
                'columns' => [__('Customer'), __('Orders'), __('Revenue'), __('Last Order'), __('Lifetime Value')],
                'rows' => $this->queries->paginateRevenue($scope),
            ],
            'lifetime' => [
                'type' => 'table',
                'columns' => [__('Customer'), __('Orders'), __('Revenue'), __('Last Order'), __('Lifetime Value')],
                'rows' => $this->queries->paginateLifetimeValue($scope),
            ],
            'activity' => [
                'type' => 'table',
                'columns' => $this->queries->hasTable('customer_activities')
                    ? [__('Customer'), __('Type'), __('Subject'), __('Date')]
                    : [__('Customer'), __('Type'), __('Subject'), __('Date'), __('Value')],
                'rows' => $this->queries->paginateActivity($scope),
            ],
            'top' => [
                'type' => 'top_customers',
                'columns' => [__('Customer'), __('Orders'), __('Revenue'), __('Lifetime Value')],
                'rows' => $this->queries->topCustomers($scope),
            ],
            'growth' => [
                'type' => 'table',
                'columns' => [__('Period'), __('New Customers'), __('Growth %')],
                'rows' => $this->queries->paginateGrowth($scope),
            ],
            'no_recent_orders' => [
                'type' => 'table',
                'columns' => [__('Customer'), __('Code'), __('Status'), __('Last Order'), __('Days Inactive')],
                'rows' => $this->queries->paginateWithoutRecentOrders($scope),
            ],
            'by_branch' => [
                'type' => 'table',
                'columns' => [__('Branch'), __('Customers'), __('Active'), __('Inactive'), __('Revenue')],
                'rows' => $this->queries->paginateByBranch($scope),
            ],
            'by_salesperson' => [
                'type' => 'table',
                'columns' => [__('Salesperson'), __('Customers'), __('Orders'), __('Revenue'), __('Average Value')],
                'rows' => $this->queries->paginateBySalesperson($scope),
            ],
            default => [
                'type' => 'summary',
                'metrics' => $this->queries->summaryMetrics($scope),
                'branch_breakdown' => $this->queries->branchBreakdown($scope),
                'salesperson_breakdown' => $this->queries->salespersonBreakdown($scope),
            ],
        };
    }
}
