<?php

namespace App\Support\Commercial\Reports;

use App\Support\Platform\PlatformCacheService;
use Illuminate\Http\Request;

class CommercialPosReportPresenter
{
    public function __construct(
        protected CommercialPosReportScopeResolver $scopeResolver,
        protected CommercialPosReportQueries $queries,
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
            'title' => __('POS Intelligence'),
            'description' => __('Departmental POS operational intelligence — counter sales, sessions, payments, and returns from live POS data.'),
            'filters' => $resolved['filters'],
            'branches' => $resolved['branches'],
            'cashiers' => $resolved['cashiers'],
            'can_export' => $resolved['can_export'],
            'readiness' => $resolved['readiness'],
            'report_ready' => $resolved['report_ready'],
            'kpis' => $resolved['report_ready'] ? $this->cachedKpis($scope) : $this->emptyKpis(),
            'report_views' => $this->tabs(),
            'report_label' => collect($this->tabs())->firstWhere('key', $scope->tab)['label'] ?? __('Report'),
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
            ['key' => 'sales_by_cashier', 'label' => __('Sales By Cashier')],
            ['key' => 'sales_by_branch', 'label' => __('Sales By Branch')],
            ['key' => 'sales_by_day', 'label' => __('Sales By Day')],
            ['key' => 'sales_by_hour', 'label' => __('Sales By Hour')],
            ['key' => 'returns_analysis', 'label' => __('Returns Analysis')],
            ['key' => 'refund_analysis', 'label' => __('Refund Analysis')],
            ['key' => 'session_performance', 'label' => __('Session Performance')],
            ['key' => 'payment_method_analysis', 'label' => __('Payment Method Analysis')],
        ];
    }

    /**
     * @return list<array{label: string, value: string, icon: string, hint: ?string}>
     */
    protected function cachedKpis(CommercialPosReportScope $scope): array
    {
        return $this->cache->remember(
            'dashboard',
            "commercial-pos-kpis:{$scope->companyId}:{$scope->cacheKey()}",
            fn () => $this->buildKpis($scope),
            (int) config('platform.cache.dashboard', 60),
        );
    }

    /**
     * @return list<array{label: string, value: string, icon: string, hint: ?string}>
     */
    protected function buildKpis(CommercialPosReportScope $scope): array
    {
        $todaySales = $this->queries->todaySalesValue($scope);
        $todayCount = $this->queries->todaySalesCount($scope);
        $trend = $this->queries->salesTrendPercent($scope);
        $returnRate = $this->queries->returnRatePercent($scope);

        return [
            ['label' => __('Today\'s Sales'), 'value' => $this->queries->formatMoney($todaySales).' ('.$todayCount.')', 'icon' => 'cash'],
            ['label' => __('Open Sessions'), 'value' => (string) $this->queries->openSessionsCount($scope), 'icon' => 'clock'],
            ['label' => __('Average Sale Value'), 'value' => ($avg = $this->queries->averageSaleValue($scope)) !== null ? $this->queries->formatMoney($avg) : '—', 'icon' => 'chart-bar'],
            ['label' => __('Return Rate'), 'value' => $returnRate !== null ? $returnRate.'%' : '—', 'icon' => 'switch-horizontal'],
            ['label' => __('Sales Trend'), 'value' => $trend !== null ? ($trend >= 0 ? '+' : '').$trend.'%' : '—', 'icon' => 'trending-up', 'hint' => __('vs prior period')],
        ];
    }

    /**
     * @return list<array{label: string, value: string, icon: string, hint: ?string}>
     */
    protected function emptyKpis(): array
    {
        $labels = [
            [__('Today\'s Sales'), 'cash'],
            [__('Open Sessions'), 'clock'],
            [__('Average Sale Value'), 'chart-bar'],
            [__('Return Rate'), 'switch-horizontal'],
            [__('Sales Trend'), 'trending-up'],
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
    protected function presentTab(CommercialPosReportScope $scope): array
    {
        if (! $this->queries->hasTable('pos_sales')) {
            return [
                'type' => 'placeholder',
                'message' => __('POS sales data is not available yet.'),
            ];
        }

        return match ($scope->tab) {
            'sales_by_branch' => [
                'type' => 'table',
                'columns' => [__('Branch'), __('Sales'), __('Revenue'), __('Avg Sale')],
                'rows' => $this->queries->paginateSalesByBranch($scope),
            ],
            'sales_by_day' => [
                'type' => 'table',
                'columns' => [__('Day'), __('Sales'), __('Revenue')],
                'rows' => $this->queries->paginateSalesByDay($scope),
            ],
            'sales_by_hour' => [
                'type' => 'table',
                'columns' => [__('Hour'), __('Sales'), __('Revenue')],
                'rows' => $this->queries->paginateSalesByHour($scope),
            ],
            'returns_analysis' => [
                'type' => 'table',
                'columns' => [__('Day'), __('Returns'), __('Value'), __('Rate')],
                'rows' => $this->queries->paginateReturnsAnalysis($scope),
            ],
            'refund_analysis' => [
                'type' => 'table',
                'columns' => [__('Payment Method'), __('Refunds'), __('Value'), __('Avg Refund')],
                'rows' => $this->queries->paginateRefundAnalysis($scope),
            ],
            'session_performance' => [
                'type' => 'table',
                'columns' => [__('Session'), __('Branch'), __('Cashier'), __('Status'), __('Sales'), __('Revenue'), __('Variance')],
                'rows' => $this->queries->paginateSessionPerformance($scope),
            ],
            'payment_method_analysis' => [
                'type' => 'table',
                'columns' => [__('Payment Method'), __('Sales'), __('Collected'), __('Share'), __('Avg Payment')],
                'rows' => $this->queries->paginatePaymentMethodAnalysis($scope),
            ],
            default => [
                'type' => 'table',
                'columns' => [__('Cashier'), __('Sales'), __('Revenue'), __('Avg Sale')],
                'rows' => $this->queries->paginateSalesByCashier($scope),
            ],
        };
    }
}
