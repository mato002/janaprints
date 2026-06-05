<?php

namespace App\Support\Procurement\Performance;

use App\Support\Platform\PlatformCacheService;
use Illuminate\Http\Request;

class SupplierPerformancePresenter
{
    public function __construct(
        protected SupplierPerformanceScopeResolver $scopeResolver,
        protected SupplierPerformanceQueries $queries,
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
            'title' => __('Supplier Performance'),
            'description' => __('Supplier intelligence — scorecards, delivery compliance, quality fulfillment, spend, and sourcing responsiveness from historical procurement data.'),
            'filters' => $resolved['filters'],
            'branches' => $resolved['branches'],
            'warehouses' => $resolved['warehouses'],
            'categories' => $resolved['categories'],
            'suppliers' => $resolved['suppliers'],
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
            ['key' => 'scorecard', 'label' => __('Supplier Scorecard')],
            ['key' => 'delivery', 'label' => __('Delivery Analysis')],
            ['key' => 'quality', 'label' => __('Quality Analysis')],
            ['key' => 'spend', 'label' => __('Spend Analysis')],
            ['key' => 'trends', 'label' => __('Trend Analysis')],
            ['key' => 'rankings', 'label' => __('Rankings')],
        ];
    }

    /**
     * @return list<array{label: string, value: string, icon: string, hint: ?string}>
     */
    protected function cachedKpis(SupplierPerformanceScope $scope): array
    {
        $ttl = (int) config('platform.cache.dashboard', 60);

        return $this->cache->remember(
            'dashboard',
            "supplier-performance-kpis:{$scope->companyId}:{$scope->cacheKey()}",
            fn () => $this->buildKpis($scope),
            $ttl,
        );
    }

    /**
     * @return list<array{label: string, value: string, icon: string, hint: ?string}>
     */
    protected function buildKpis(SupplierPerformanceScope $scope): array
    {
        $metrics = $this->queries->summaryMetrics($scope);

        return [
            ['label' => __('Total Purchase Value'), 'value' => $this->queries->money($metrics['total_purchase_value']), 'icon' => 'currency-dollar', 'hint' => null],
            ['label' => __('Purchase Count'), 'value' => (string) $metrics['purchase_count'], 'icon' => 'clipboard-list', 'hint' => null],
            ['label' => __('Average Delivery Time'), 'value' => $this->queries->days($metrics['average_delivery_time']), 'icon' => 'clock', 'hint' => __('Order date to first posted receipt')],
            ['label' => __('On-Time Delivery %'), 'value' => $this->queries->percent($metrics['on_time_percent']), 'icon' => 'check-circle', 'hint' => null],
            ['label' => __('Late Delivery %'), 'value' => $this->queries->percent($metrics['late_percent']), 'icon' => 'exclamation', 'hint' => null],
            ['label' => __('Quality Acceptance %'), 'value' => $this->queries->percent($metrics['quality_acceptance_percent']), 'icon' => 'shield-check', 'hint' => __('Received quantity vs ordered quantity')],
            ['label' => __('Rejection %'), 'value' => $this->queries->percent($metrics['rejection_percent']), 'icon' => 'x-circle', 'hint' => __('Short-ship and unfulfilled quantity')],
            ['label' => __('RFQ Participation %'), 'value' => $this->queries->percent($metrics['rfq_participation_percent']), 'icon' => 'users', 'hint' => null],
            ['label' => __('Award Win %'), 'value' => $this->queries->percent($metrics['award_win_percent']), 'icon' => 'badge-check', 'hint' => null],
        ];
    }

    /**
     * @return list<array{label: string, value: string, icon: string, hint: ?string}>
     */
    protected function emptyKpis(): array
    {
        $labels = [
            [__('Total Purchase Value'), 'currency-dollar'],
            [__('Purchase Count'), 'clipboard-list'],
            [__('Average Delivery Time'), 'clock'],
            [__('On-Time Delivery %'), 'check-circle'],
            [__('Late Delivery %'), 'exclamation'],
            [__('Quality Acceptance %'), 'shield-check'],
            [__('Rejection %'), 'x-circle'],
            [__('RFQ Participation %'), 'users'],
            [__('Award Win %'), 'badge-check'],
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
    protected function presentTab(SupplierPerformanceScope $scope): array
    {
        if (! $this->queries->hasTable('purchase_orders')) {
            return [
                'type' => 'placeholder',
                'message' => __('Purchase order data is not available yet.'),
            ];
        }

        return match ($scope->tab) {
            'delivery' => [
                'type' => 'table',
                'columns' => [__('PO Number'), __('Supplier'), __('Expected Date'), __('Actual Date'), __('Variance'), __('Days Late'), __('Days Early')],
                'rows' => $this->formatDeliveryRows($this->queries->paginateDeliveryAnalysis($scope)),
            ],
            'quality' => [
                'type' => 'table',
                'columns' => [__('Supplier'), __('Items Received'), __('Items Rejected'), __('Defect Rate'), __('Return Rate')],
                'rows' => $this->formatQualityRows($this->queries->paginateQualityAnalysis($scope)),
            ],
            'spend' => [
                'type' => 'table',
                'columns' => [__('Supplier'), __('Spend'), __('Orders'), __('Average Order Value')],
                'rows' => $this->formatSpendRows($this->queries->paginateSpendAnalysis($scope)),
            ],
            'trends' => [
                'type' => 'trends',
                'series' => $this->queries->performanceTrendSeries($scope),
            ],
            'rankings' => [
                'type' => 'rankings',
                'rankings' => $this->formatRankings($this->queries->rankings($scope)),
            ],
            default => [
                'type' => 'scorecard',
                'columns' => [
                    __('Supplier'),
                    __('Overall Score'),
                    __('Grade'),
                    __('Purchase Count'),
                    __('On-Time %'),
                    __('Quality %'),
                    __('Avg Delivery'),
                    __('Spend'),
                ],
                'rows' => $this->formatScorecardRows($this->queries->paginateScorecard($scope)),
            ],
        };
    }

    /**
     * @param  array<string, list<array<string, mixed>>>  $rankings
     * @return array<string, list<array<string, string>>>
     */
    protected function formatRankings(array $rankings): array
    {
        return [
            'top_suppliers' => collect($rankings['top_suppliers'])->map(fn (array $row) => [
                'supplier' => $row['supplier'],
                'score' => $row['overall_score'] === null ? '—' : (string) $row['overall_score'],
                'grade' => $row['grade'],
            ])->all(),
            'most_reliable' => collect($rankings['most_reliable'])->map(fn (array $row) => [
                'supplier' => $row['supplier'],
                'on_time_percent' => $this->queries->percent($row['on_time_percent']),
            ])->all(),
            'fastest_delivery' => collect($rankings['fastest_delivery'])->map(fn (array $row) => [
                'supplier' => $row['supplier'],
                'average_delivery_time' => $this->queries->days($row['average_delivery_time']),
            ])->all(),
            'best_price' => collect($rankings['best_price'])->map(fn (array $row) => [
                'supplier' => $row['supplier'],
                'price_score' => (string) $row['price_score'],
            ])->all(),
            'highest_spend' => collect($rankings['highest_spend'])->map(fn (array $row) => [
                'supplier' => $row['supplier'],
                'spend' => $this->queries->money($row['spend']),
            ])->all(),
        ];
    }

    protected function formatScorecardRows(\Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $paginator->through(function (array $row) {
            return [
                'supplier' => $row['supplier'],
                'overall_score' => $row['overall_score'] === null ? '—' : (string) $row['overall_score'],
                'grade' => $row['grade'],
                'purchase_count' => (string) $row['purchase_count'],
                'on_time_percent' => $this->queries->percent($row['on_time_percent']),
                'quality_acceptance_percent' => $this->queries->percent($row['quality_acceptance_percent']),
                'average_delivery_time' => $this->queries->days($row['average_delivery_time']),
                'spend' => $this->queries->money((float) $row['total_purchase_value']),
            ];
        });

        return $paginator;
    }

    protected function formatDeliveryRows(\Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $paginator->through(fn (array $row) => $row);

        return $paginator;
    }

    protected function formatQualityRows(\Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $paginator->through(function (array $row) {
            return [
                'supplier' => $row['supplier'],
                'items_received' => number_format($row['items_received'], 2),
                'items_rejected' => number_format($row['items_rejected'], 2),
                'defect_rate' => $this->queries->percent($row['defect_rate']),
                'return_rate' => $this->queries->percent($row['return_rate']),
            ];
        });

        return $paginator;
    }

    protected function formatSpendRows(\Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $paginator->through(function (array $row) {
            return [
                'supplier' => $row['supplier'],
                'spend' => $this->queries->money($row['spend']),
                'orders' => (string) $row['orders'],
                'average_order_value' => $this->queries->money($row['average_order_value']),
            ];
        });

        return $paginator;
    }
}
