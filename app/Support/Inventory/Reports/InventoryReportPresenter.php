<?php

namespace App\Support\Inventory\Reports;

use App\Support\Platform\PlatformCacheService;
use Illuminate\Http\Request;

class InventoryReportPresenter
{
    public function __construct(
        protected InventoryReportScopeResolver $scopeResolver,
        protected InventoryReportQueries $queries,
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
            'title' => __('Inventory Reports'),
            'description' => __('Operational stock visibility — on-hand balances, health alerts, movement aging, and warehouse summaries.'),
            'filters' => $resolved['filters'],
            'branches' => $resolved['branches'],
            'warehouses' => $resolved['warehouses'],
            'categories' => $resolved['categories'],
            'suppliers' => $resolved['suppliers'],
            'items' => $resolved['items'],
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
            ['key' => 'stock_on_hand', 'label' => __('Stock On Hand')],
            ['key' => 'low_stock', 'label' => __('Low Stock')],
            ['key' => 'out_of_stock', 'label' => __('Out Of Stock')],
            ['key' => 'slow_moving', 'label' => __('Slow Moving Stock')],
            ['key' => 'dead_stock', 'label' => __('Dead Stock')],
            ['key' => 'stock_aging', 'label' => __('Stock Aging')],
            ['key' => 'warehouse_summary', 'label' => __('Warehouse Summary')],
        ];
    }

    /**
     * @return list<array{label: string, value: string, icon: string, hint: ?string}>
     */
    protected function cachedKpis(InventoryReportScope $scope): array
    {
        $ttl = (int) config('platform.cache.dashboard', 60);

        return $this->cache->remember(
            'dashboard',
            "inventory-reports-kpis:{$scope->companyId}:{$scope->cacheKey()}",
            fn () => $this->buildKpis($scope),
            $ttl,
        );
    }

    /**
     * @return list<array{label: string, value: string, icon: string, hint: ?string}>
     */
    protected function buildKpis(InventoryReportScope $scope): array
    {
        return [
            ['label' => __('Inventory Value'), 'value' => $this->queries->money($this->queries->totalInventoryValue($scope)), 'icon' => 'currency-dollar', 'hint' => null],
            ['label' => __('Items On Hand'), 'value' => (string) $this->queries->countItemsOnHand($scope), 'icon' => 'cube', 'hint' => null],
            ['label' => __('Low Stock'), 'value' => (string) $this->queries->countLowStock($scope), 'icon' => 'exclamation', 'hint' => null],
            ['label' => __('Out Of Stock'), 'value' => (string) $this->queries->countOutOfStock($scope), 'icon' => 'x-circle', 'hint' => null],
            ['label' => __('Slow Moving'), 'value' => (string) $this->queries->countSlowMoving($scope), 'icon' => 'clock', 'hint' => __('No issue/consumption in :days+ days', ['days' => InventoryReportQueries::SLOW_MOVING_DAYS])],
            ['label' => __('Dead Stock Value'), 'value' => $this->queries->money($this->queries->deadStockValue($scope)), 'icon' => 'archive', 'hint' => __('No movement in :days+ days', ['days' => InventoryReportQueries::DEAD_STOCK_DAYS])],
            ['label' => __('Warehouses'), 'value' => (string) $this->queries->countWarehouses($scope), 'icon' => 'office-building', 'hint' => null],
        ];
    }

    /**
     * @return list<array{label: string, value: string, icon: string, hint: ?string}>
     */
    protected function emptyKpis(): array
    {
        $labels = [
            [__('Inventory Value'), 'currency-dollar'],
            [__('Items On Hand'), 'cube'],
            [__('Low Stock'), 'exclamation'],
            [__('Out Of Stock'), 'x-circle'],
            [__('Slow Moving'), 'clock'],
            [__('Dead Stock Value'), 'archive'],
            [__('Warehouses'), 'office-building'],
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
    protected function presentTab(InventoryReportScope $scope): array
    {
        if (! $this->queries->hasTable('inventory_valuations')) {
            return [
                'type' => 'placeholder',
                'message' => __('Inventory valuation data is not available yet.'),
            ];
        }

        return match ($scope->tab) {
            'low_stock' => [
                'type' => 'table',
                'columns' => [__('Item'), __('Min Level'), __('Current Qty'), __('Shortfall'), __('Days Remaining')],
                'rows' => $this->formatRows($this->queries->paginateLowStock($scope), [
                    'current_qty' => 'qty',
                    'min_level' => 'qty',
                    'shortfall' => 'qty',
                    'days_remaining' => 'days',
                ]),
            ],
            'out_of_stock' => [
                'type' => 'table',
                'columns' => [__('Item'), __('Warehouse'), __('Last Movement'), __('Last Purchase')],
                'rows' => $this->formatRows($this->queries->paginateOutOfStock($scope), [
                    'last_movement' => 'date',
                    'last_purchase' => 'date',
                ]),
            ],
            'slow_moving' => [
                'type' => 'table',
                'columns' => [__('Item'), __('Last Sale'), __('Last Consumption'), __('Days Idle'), __('Value Locked')],
                'rows' => $this->formatRows($this->queries->paginateSlowMoving($scope), [
                    'last_sale' => 'date',
                    'last_consumption' => 'date',
                    'days_idle' => 'integer',
                    'value_locked' => 'money',
                ]),
            ],
            'dead_stock' => [
                'type' => 'table',
                'columns' => [__('Item'), __('Days Without Movement'), __('Qty'), __('Value')],
                'rows' => $this->formatRows($this->queries->paginateDeadStock($scope), [
                    'days_without_movement' => 'integer',
                    'qty' => 'qty',
                    'value' => 'money',
                ]),
            ],
            'stock_aging' => [
                'type' => 'aging',
                'buckets' => $this->formatAgingBuckets($this->queries->stockAgingBuckets($scope)),
                'columns' => [__('Item'), __('Warehouse'), __('Last Receipt'), __('Age (Days)'), __('Qty'), __('Value')],
                'rows' => $this->formatRows($this->queries->paginateStockAging($scope), [
                    'last_receipt' => 'date',
                    'age_days' => 'integer',
                    'qty' => 'qty',
                    'value' => 'money',
                ]),
            ],
            'warehouse_summary' => [
                'type' => 'table',
                'columns' => [__('Warehouse'), __('Items'), __('Qty'), __('Value')],
                'rows' => $this->formatRows($this->queries->paginateWarehouseSummary($scope), [
                    'items' => 'integer',
                    'qty' => 'qty',
                    'value' => 'money',
                ]),
            ],
            default => [
                'type' => 'table',
                'columns' => [__('Item'), __('SKU'), __('Category'), __('Warehouse'), __('Available Qty'), __('Reserved Qty'), __('On Hand Qty'), __('Unit Cost'), __('Inventory Value')],
                'rows' => $this->formatRows($this->queries->paginateStockOnHand($scope), [
                    'available_qty' => 'qty',
                    'reserved_qty' => 'qty',
                    'on_hand_qty' => 'qty',
                    'unit_cost' => 'money',
                    'inventory_value' => 'money',
                ]),
            ],
        };
    }

    /**
     * @param  array<string, string>  $formatters
     */
    protected function formatRows(\Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator, array $formatters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $paginator->through(function ($row) use ($formatters) {
            $formatted = (array) $row;

            foreach ($formatters as $key => $type) {
                if (! array_key_exists($key, $formatted)) {
                    continue;
                }

                $formatted[$key] = match ($type) {
                    'money' => $this->queries->money((float) $formatted[$key]),
                    'qty' => $this->queries->qty((float) $formatted[$key]),
                    'integer' => $formatted[$key] === null ? '—' : (string) (int) $formatted[$key],
                    'days' => $formatted[$key] === null ? '—' : (string) (int) $formatted[$key],
                    'date' => $formatted[$key] ? (string) $formatted[$key] : '—',
                    default => (string) $formatted[$key],
                };
            }

            return $formatted;
        });

        return $paginator;
    }

    /**
     * @param  array<string, array{qty: float, value: float, items: int}>  $buckets
     * @return list<array{label: string, items: string, qty: string, value: string}>
     */
    protected function formatAgingBuckets(array $buckets): array
    {
        $labels = [
            '0_30' => __('0–30 days'),
            '31_60' => __('31–60 days'),
            '61_90' => __('61–90 days'),
            '90_plus' => __('90+ days'),
        ];

        return collect($labels)->map(function (string $label, string $key) use ($buckets) {
            $bucket = $buckets[$key] ?? ['qty' => 0, 'value' => 0, 'items' => 0];

            return [
                'label' => $label,
                'items' => (string) $bucket['items'],
                'qty' => $this->queries->qty((float) $bucket['qty']),
                'value' => $this->queries->money((float) $bucket['value']),
            ];
        })->values()->all();
    }
}
