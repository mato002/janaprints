<?php

namespace App\Support\Procurement\Reports;

use App\Support\Platform\PlatformCacheService;
use Illuminate\Http\Request;

class ProcurementReportPresenter
{
    public function __construct(
        protected ProcurementReportScopeResolver $scopeResolver,
        protected ProcurementReportQueries $queries,
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
            'title' => __('Procurement Reports'),
            'description' => __('Purchasing intelligence — spend, supplier performance, delivery compliance, and purchase order lifecycle.'),
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
            ['key' => 'summary', 'label' => __('Purchase Summary')],
            ['key' => 'trends', 'label' => __('Purchase Trends')],
            ['key' => 'supplier_spend', 'label' => __('Supplier Spend Analysis')],
            ['key' => 'top_suppliers', 'label' => __('Top Suppliers')],
            ['key' => 'supplier_performance', 'label' => __('Supplier Performance')],
            ['key' => 'late_deliveries', 'label' => __('Late Deliveries')],
            ['key' => 'cycle_time', 'label' => __('Purchase Cycle Time')],
            ['key' => 'open_orders', 'label' => __('Open Purchase Orders')],
            ['key' => 'closed_orders', 'label' => __('Closed Purchase Orders')],
            ['key' => 'cancelled_orders', 'label' => __('Cancelled Purchase Orders')],
        ];
    }

    /**
     * @return list<array{label: string, value: string, icon: string, hint: ?string}>
     */
    protected function cachedKpis(ProcurementReportScope $scope): array
    {
        $ttl = (int) config('platform.cache.dashboard', 60);

        return $this->cache->remember(
            'dashboard',
            "procurement-reports-kpis:{$scope->companyId}:{$scope->cacheKey()}",
            fn () => $this->buildKpis($scope),
            $ttl,
        );
    }

    /**
     * @return list<array{label: string, value: string, icon: string, hint: ?string}>
     */
    protected function buildKpis(ProcurementReportScope $scope): array
    {
        return [
            ['label' => __('Total Purchase Spend'), 'value' => $this->queries->money($this->queries->totalSpend($scope)), 'icon' => 'currency-dollar', 'hint' => null],
            ['label' => __('Total Orders'), 'value' => (string) $this->queries->totalOrders($scope), 'icon' => 'clipboard-list', 'hint' => null],
            ['label' => __('Average Order Value'), 'value' => $this->queries->money($this->queries->averageOrderValue($scope)), 'icon' => 'chart-bar', 'hint' => null],
            ['label' => __('Active Suppliers'), 'value' => (string) $this->queries->activeSuppliers($scope), 'icon' => 'truck', 'hint' => null],
            ['label' => __('On-Time Delivery %'), 'value' => $this->queries->percent($this->queries->onTimeDeliveryPercent($scope)), 'icon' => 'check-circle', 'hint' => null],
            ['label' => __('Late Deliveries'), 'value' => (string) $this->queries->lateDeliveriesCount($scope), 'icon' => 'exclamation', 'hint' => null],
            ['label' => __('Open POs'), 'value' => (string) $this->queries->openOrdersCount($scope), 'icon' => 'inbox', 'hint' => null],
            ['label' => __('Cancelled POs'), 'value' => (string) $this->queries->cancelledOrdersCount($scope), 'icon' => 'x-circle', 'hint' => null],
            ['label' => __('Avg Cycle Time'), 'value' => $this->queries->days($this->queries->averageCycleTimeDays($scope)), 'icon' => 'clock', 'hint' => __('Order date to first posted receipt')],
        ];
    }

    /**
     * @return list<array{label: string, value: string, icon: string, hint: ?string}>
     */
    protected function emptyKpis(): array
    {
        $labels = [
            [__('Total Purchase Spend'), 'currency-dollar'],
            [__('Total Orders'), 'clipboard-list'],
            [__('Average Order Value'), 'chart-bar'],
            [__('Active Suppliers'), 'truck'],
            [__('On-Time Delivery %'), 'check-circle'],
            [__('Late Deliveries'), 'exclamation'],
            [__('Open POs'), 'inbox'],
            [__('Cancelled POs'), 'x-circle'],
            [__('Avg Cycle Time'), 'clock'],
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
    protected function presentTab(ProcurementReportScope $scope): array
    {
        if (! $this->queries->hasTable('purchase_orders')) {
            return [
                'type' => 'placeholder',
                'message' => __('Purchase order data is not available yet.'),
            ];
        }

        return match ($scope->tab) {
            'trends' => [
                'type' => 'trends',
                'series' => $this->queries->trendSeries($scope),
            ],
            'supplier_spend' => [
                'type' => 'table',
                'columns' => [__('Supplier'), __('Orders'), __('Spend'), __('Average Order')],
                'rows' => $this->formatSpendRows($this->queries->paginateSupplierSpend($scope)),
            ],
            'top_suppliers' => [
                'type' => 'top_suppliers',
                'columns' => [__('Supplier'), __('Orders'), __('Spend')],
                'rows' => $this->queries->topSuppliers($scope)->map(fn (array $row) => [
                    'supplier' => $row['supplier'],
                    'orders' => (string) $row['orders'],
                    'spend' => $this->queries->money($row['spend']),
                ])->all(),
            ],
            'supplier_performance' => [
                'type' => 'scorecard',
                'columns' => [__('Supplier'), __('Orders'), __('Delivered'), __('Late'), __('Average Lead Time'), __('Spend'), __('Performance %')],
                'rows' => $this->formatScorecardRows($this->queries->paginateSupplierScorecard($scope)),
            ],
            'late_deliveries' => [
                'type' => 'table',
                'columns' => [__('PO Number'), __('Supplier'), __('Order Date'), __('Expected Delivery'), __('Status'), __('Spend')],
                'rows' => $this->formatOrderRows($this->queries->paginateLateDeliveries($scope)),
            ],
            'cycle_time' => [
                'type' => 'table',
                'columns' => [__('PO Number'), __('Supplier'), __('Order Date'), __('Expected Delivery'), __('First Receipt'), __('Cycle Days'), __('Late'), __('Spend')],
                'rows' => $this->formatCycleRows($this->queries->paginateCycleTime($scope)),
            ],
            'open_orders' => [
                'type' => 'table',
                'columns' => [__('PO Number'), __('Supplier'), __('Order Date'), __('Expected Delivery'), __('Status'), __('Spend')],
                'rows' => $this->formatOrderRows($this->queries->paginateOpenOrders($scope)),
            ],
            'closed_orders' => [
                'type' => 'table',
                'columns' => [__('PO Number'), __('Supplier'), __('Order Date'), __('Expected Delivery'), __('Status'), __('Spend')],
                'rows' => $this->formatOrderRows($this->queries->paginateClosedOrders($scope)),
            ],
            'cancelled_orders' => [
                'type' => 'table',
                'columns' => [__('PO Number'), __('Supplier'), __('Order Date'), __('Expected Delivery'), __('Status'), __('Spend')],
                'rows' => $this->formatOrderRows($this->queries->paginateCancelledOrders($scope)),
            ],
            default => [
                'type' => 'summary',
                'metrics' => $this->queries->summaryMetrics($scope),
                'branch_breakdown' => $this->queries->branchBreakdown($scope),
            ],
        };
    }

    protected function formatSpendRows(\Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $paginator->through(function ($row) {
            return [
                'supplier' => (string) $row->supplier,
                'orders' => (string) $row->orders,
                'spend' => $this->queries->money((float) $row->spend),
                'average_order' => $this->queries->money((float) $row->average_order),
            ];
        });

        return $paginator;
    }

    protected function formatScorecardRows(\Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $paginator->through(function (array $row) {
            return [
                'supplier' => $row['supplier'],
                'orders' => (string) $row['orders'],
                'delivered' => (string) $row['delivered'],
                'late' => (string) $row['late'],
                'average_lead_time' => $this->queries->days($row['average_lead_time']),
                'spend' => $this->queries->money((float) $row['spend']),
                'performance_percent' => $this->queries->percent($row['performance_percent']),
            ];
        });

        return $paginator;
    }

    protected function formatOrderRows(\Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $paginator->through(function ($order) {
            return [
                'po_number' => $order->po_number,
                'supplier' => $order->vendor?->vendor_name ?? '—',
                'order_date' => $order->order_date?->toDateString() ?? '—',
                'expected_delivery' => $order->expected_delivery_date?->toDateString() ?? '—',
                'status' => $order->status instanceof \App\Enums\PurchaseOrderStatus ? $order->status->value : (string) $order->status,
                'spend' => $this->queries->money((float) $order->total_amount),
            ];
        });

        return $paginator;
    }

    protected function formatCycleRows(\Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $paginator->through(function (array $row) {
            return [
                'po_number' => $row['po_number'],
                'supplier' => $row['supplier'],
                'order_date' => $row['order_date'] ?? '—',
                'expected_delivery' => $row['expected_delivery'],
                'first_receipt' => $row['first_receipt'],
                'cycle_days' => $row['cycle_days'] === null ? '—' : (string) $row['cycle_days'],
                'late' => $row['late'],
                'spend' => $this->queries->money((float) $row['spend']),
            ];
        });

        return $paginator;
    }
}
