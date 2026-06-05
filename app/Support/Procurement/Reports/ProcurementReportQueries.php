<?php

namespace App\Support\Procurement\Reports;

use App\Enums\GoodsReceiptStatus;
use App\Enums\PurchaseOrderStatus;
use App\Models\Inventory\Warehouse;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\Vendor;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProcurementReportQueries
{
    public const PER_PAGE = 25;

    /**
     * @return list<string>
     */
    public function spendExcludedStatuses(): array
    {
        return [
            PurchaseOrderStatus::Draft->value,
            PurchaseOrderStatus::Cancelled->value,
            PurchaseOrderStatus::Rejected->value,
        ];
    }

    /**
     * @return list<PurchaseOrderStatus>
     */
    public function openStatuses(): array
    {
        return [
            PurchaseOrderStatus::PendingApproval,
            PurchaseOrderStatus::Approved,
            PurchaseOrderStatus::Sent,
            PurchaseOrderStatus::PartiallyReceived,
        ];
    }

    /**
     * @return list<PurchaseOrderStatus>
     */
    public function closedStatuses(): array
    {
        return [
            PurchaseOrderStatus::Received,
            PurchaseOrderStatus::Closed,
        ];
    }

    public function hasTable(string $table): bool
    {
        return Schema::hasTable($table);
    }

    public function money(float $amount): string
    {
        return 'KES '.number_format($amount, 0);
    }

    public function days(?float $value): string
    {
        return $value === null ? '—' : (string) round($value).' '.__('days');
    }

    public function percent(?float $value): string
    {
        return $value === null ? '—' : round($value, 1).'%';
    }

    public function baseOrderQuery(ProcurementReportScope $scope): Builder
    {
        $query = PurchaseOrder::query()
            ->where('purchase_orders.company_id', $scope->companyId);

        if ($scope->branchId !== null) {
            $query->where('purchase_orders.branch_id', $scope->branchId);
        }

        $query->whereDate('purchase_orders.order_date', '>=', $scope->fromDate)
            ->whereDate('purchase_orders.order_date', '<=', $scope->toDate);

        if ($scope->supplierId !== null) {
            $query->where('purchase_orders.vendor_id', $scope->supplierId);
        }

        if ($scope->warehouseId !== null) {
            $warehouse = Warehouse::query()->find($scope->warehouseId);
            $query->where(function (Builder $inner) use ($scope, $warehouse) {
                $inner->whereHas('goodsReceipts', fn (Builder $receipt) => $receipt->where('warehouse_id', $scope->warehouseId))
                    ->orWhere(function (Builder $open) use ($warehouse) {
                        $open->whereDoesntHave('goodsReceipts')
                            ->whereIn('purchase_orders.status', array_map(
                                fn (PurchaseOrderStatus $status) => $status->value,
                                $this->openStatuses(),
                            ));

                        if ($warehouse !== null) {
                            $open->where('purchase_orders.branch_id', $warehouse->branch_id);
                        }
                    });
            });
        }

        if ($scope->categoryId !== null) {
            $query->whereHas('items.inventoryItem', fn (Builder $item) => $item->where('category_id', $scope->categoryId));
        }

        if ($scope->search !== '') {
            $term = '%'.$scope->search.'%';
            $query->where(function (Builder $inner) use ($term) {
                $inner->where('purchase_orders.po_number', 'like', $term)
                    ->orWhereHas('vendor', fn (Builder $vendor) => $vendor->where('vendor_name', 'like', $term));
            });
        }

        return $query;
    }

    public function spendOrderQuery(ProcurementReportScope $scope): Builder
    {
        return $this->baseOrderQuery($scope)
            ->whereNotIn('purchase_orders.status', $this->spendExcludedStatuses());
    }

    public function totalSpend(ProcurementReportScope $scope): float
    {
        if (! $this->hasTable('purchase_orders')) {
            return 0.0;
        }

        return (float) $this->spendOrderQuery($scope)->sum('purchase_orders.total_amount');
    }

    public function totalOrders(ProcurementReportScope $scope): int
    {
        if (! $this->hasTable('purchase_orders')) {
            return 0;
        }

        return (int) $this->spendOrderQuery($scope)->count();
    }

    public function averageOrderValue(ProcurementReportScope $scope): float
    {
        $orders = $this->totalOrders($scope);

        return $orders > 0 ? $this->totalSpend($scope) / $orders : 0.0;
    }

    public function activeSuppliers(ProcurementReportScope $scope): int
    {
        if (! $this->hasTable('purchase_orders')) {
            return 0;
        }

        return (int) $this->spendOrderQuery($scope)->distinct('purchase_orders.vendor_id')->count('purchase_orders.vendor_id');
    }

    public function openOrdersCount(ProcurementReportScope $scope): int
    {
        if (! $this->hasTable('purchase_orders')) {
            return 0;
        }

        return (int) $this->baseOrderQuery($scope)
            ->whereIn('purchase_orders.status', array_map(
                fn (PurchaseOrderStatus $status) => $status->value,
                $this->openStatuses(),
            ))
            ->count();
    }

    public function closedOrdersCount(ProcurementReportScope $scope): int
    {
        if (! $this->hasTable('purchase_orders')) {
            return 0;
        }

        return (int) $this->baseOrderQuery($scope)
            ->whereIn('purchase_orders.status', array_map(
                fn (PurchaseOrderStatus $status) => $status->value,
                $this->closedStatuses(),
            ))
            ->count();
    }

    public function cancelledOrdersCount(ProcurementReportScope $scope): int
    {
        if (! $this->hasTable('purchase_orders')) {
            return 0;
        }

        return (int) $this->baseOrderQuery($scope)
            ->where('purchase_orders.status', PurchaseOrderStatus::Cancelled)
            ->count();
    }

    public function lateDeliveriesCount(ProcurementReportScope $scope): int
    {
        if (! $this->hasTable('purchase_orders')) {
            return 0;
        }

        return (int) $this->lateDeliveryQuery($scope)->count();
    }

    public function onTimeDeliveryPercent(ProcurementReportScope $scope): ?float
    {
        if (! $this->hasTable('purchase_orders') || ! $this->hasTable('goods_receipts')) {
            return null;
        }

        $delivered = $this->deliveredOrderQuery($scope)->get(['purchase_orders.id', 'purchase_orders.expected_delivery_date']);
        if ($delivered->isEmpty()) {
            return null;
        }

        $onTime = $delivered->filter(function (PurchaseOrder $order) {
            $firstReceipt = $this->firstReceiptDate($order->id);
            if ($firstReceipt === null) {
                return false;
            }

            if ($order->expected_delivery_date === null) {
                return true;
            }

            return $firstReceipt <= $order->expected_delivery_date->toDateString();
        })->count();

        return round(($onTime / $delivered->count()) * 100, 1);
    }

    public function averageCycleTimeDays(ProcurementReportScope $scope): ?float
    {
        if (! $this->hasTable('purchase_orders') || ! $this->hasTable('goods_receipts')) {
            return null;
        }

        $rows = $this->deliveredOrderQuery($scope)
            ->get(['purchase_orders.id', 'purchase_orders.order_date']);

        $leadTimes = $rows->map(function (PurchaseOrder $order) {
            $firstReceipt = $this->firstReceiptDate($order->id);

            return $firstReceipt === null
                ? null
                : Carbon::parse($order->order_date)->diffInDays(Carbon::parse($firstReceipt));
        })->filter(fn ($days) => $days !== null);

        return $leadTimes->isEmpty() ? null : round((float) $leadTimes->avg(), 1);
    }

    /**
     * @return array{orders: int, spend: float, average_order_value: float, suppliers: int, open_orders: int, closed_orders: int, cancelled_orders: int, late_deliveries: int, on_time_percent: ?float, average_cycle_days: ?float}
     */
    public function summaryMetrics(ProcurementReportScope $scope): array
    {
        return [
            'orders' => $this->totalOrders($scope),
            'spend' => $this->totalSpend($scope),
            'average_order_value' => $this->averageOrderValue($scope),
            'suppliers' => $this->activeSuppliers($scope),
            'open_orders' => $this->openOrdersCount($scope),
            'closed_orders' => $this->closedOrdersCount($scope),
            'cancelled_orders' => $this->cancelledOrdersCount($scope),
            'late_deliveries' => $this->lateDeliveriesCount($scope),
            'on_time_percent' => $this->onTimeDeliveryPercent($scope),
            'average_cycle_days' => $this->averageCycleTimeDays($scope),
        ];
    }

    /**
     * @return list<array{label: string, orders: int, spend: float}>
     */
    public function branchBreakdown(ProcurementReportScope $scope): array
    {
        if (! $this->hasTable('purchase_orders') || ! $this->hasTable('branches')) {
            return [];
        }

        $rows = $this->spendOrderQuery($scope)
            ->join('branches', 'branches.id', '=', 'purchase_orders.branch_id')
            ->select(
                'branches.name as branch',
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(purchase_orders.total_amount) as spend'),
            )
            ->groupBy('branches.id', 'branches.name')
            ->orderByDesc('spend')
            ->get();

        return $rows->map(fn ($row) => [
            'branch' => (string) $row->branch,
            'orders' => (string) $row->orders,
            'spend' => $this->money((float) $row->spend),
            'average_order' => $this->money((int) $row->orders > 0 ? (float) $row->spend / (int) $row->orders : 0),
        ])->all();
    }

    /**
     * @return array{daily: list<array{label: string, orders: int, spend: float}>, weekly: list<array{label: string, orders: int, spend: float}>, monthly: list<array{label: string, orders: int, spend: float}>, quarterly: list<array{label: string, orders: int, spend: float}>, yearly: list<array{label: string, orders: int, spend: float}>}
     */
    public function trendSeries(ProcurementReportScope $scope): array
    {
        if (! $this->hasTable('purchase_orders')) {
            return [
                'daily' => [],
                'weekly' => [],
                'monthly' => [],
                'quarterly' => [],
                'yearly' => [],
            ];
        }

        return [
            'daily' => $this->trendBuckets($scope, 'day', 30),
            'weekly' => $this->trendBuckets($scope, 'week', 12),
            'monthly' => $this->trendBuckets($scope, 'month', 12),
            'quarterly' => $this->trendBuckets($scope, 'quarter', 8),
            'yearly' => $this->trendBuckets($scope, 'year', 5),
        ];
    }

    public function paginateSupplierSpend(ProcurementReportScope $scope): LengthAwarePaginator
    {
        if (! $this->hasTable('purchase_orders') || ! $this->hasTable('vendors')) {
            return $this->emptyPaginator($scope);
        }

        $query = $this->spendOrderQuery($scope)
            ->join('vendors', 'vendors.id', '=', 'purchase_orders.vendor_id')
            ->select(
                'vendors.vendor_name as supplier',
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(purchase_orders.total_amount) as spend'),
                DB::raw('AVG(purchase_orders.total_amount) as average_order'),
            )
            ->groupBy('vendors.id', 'vendors.vendor_name')
            ->orderByDesc('spend');

        return $this->paginate($query, $scope, grouped: true);
    }

    /**
     * @return Collection<int, array{supplier: string, orders: int, spend: float}>
     */
    public function topSuppliers(ProcurementReportScope $scope): Collection
    {
        if (! $this->hasTable('purchase_orders') || ! $this->hasTable('vendors')) {
            return collect();
        }

        return $this->spendOrderQuery($scope)
            ->join('vendors', 'vendors.id', '=', 'purchase_orders.vendor_id')
            ->select(
                'vendors.vendor_name as supplier',
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(purchase_orders.total_amount) as spend'),
            )
            ->groupBy('vendors.id', 'vendors.vendor_name')
            ->orderByDesc('spend')
            ->limit($scope->topLimit)
            ->get()
            ->map(fn ($row) => [
                'supplier' => (string) $row->supplier,
                'orders' => (int) $row->orders,
                'spend' => (float) $row->spend,
            ]);
    }

    public function paginateSupplierScorecard(ProcurementReportScope $scope): LengthAwarePaginator
    {
        $rows = $this->supplierScorecardRows($scope);

        return $this->paginateCollection($rows, $scope);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function supplierScorecardRows(ProcurementReportScope $scope): Collection
    {
        if (! $this->hasTable('purchase_orders') || ! $this->hasTable('vendors')) {
            return collect();
        }

        $vendorIds = $this->baseOrderQuery($scope)->distinct()->pluck('purchase_orders.vendor_id');
        if ($vendorIds->isEmpty()) {
            return collect();
        }

        $vendors = Vendor::query()->whereIn('id', $vendorIds)->orderBy('vendor_name')->get(['id', 'vendor_name']);

        return $vendors->map(function (Vendor $vendor) use ($scope) {
            $scoped = new ProcurementReportScope(
                companyId: $scope->companyId,
                branchId: $scope->branchId,
                fromDate: $scope->fromDate,
                toDate: $scope->toDate,
                supplierId: $vendor->id,
                warehouseId: $scope->warehouseId,
                categoryId: $scope->categoryId,
                search: $scope->search,
            );

            $orders = (int) $this->baseOrderQuery($scoped)->count();
            $delivered = (int) $this->deliveredOrderQuery($scoped)->count();
            $late = (int) $this->lateDeliveryQuery($scoped)->count();
            $spend = $this->totalSpend($scoped);
            $avgLead = $this->averageCycleTimeDays($scoped);
            $performance = $delivered > 0
                ? round((($delivered - $late) / $delivered) * 100, 1)
                : null;

            return [
                'supplier' => $vendor->vendor_name,
                'orders' => $orders,
                'delivered' => $delivered,
                'late' => $late,
                'average_lead_time' => $avgLead,
                'spend' => $spend,
                'performance_percent' => $performance,
            ];
        })->sortByDesc('spend')->values();
    }

    public function paginateLateDeliveries(ProcurementReportScope $scope): LengthAwarePaginator
    {
        if (! $this->hasTable('purchase_orders')) {
            return $this->emptyPaginator($scope);
        }

        $query = $this->lateDeliveryQuery($scope)
            ->with('vendor:id,vendor_name')
            ->select('purchase_orders.*')
            ->orderByDesc('purchase_orders.expected_delivery_date');

        return $this->paginate($query, $scope);
    }

    public function paginateCycleTime(ProcurementReportScope $scope): LengthAwarePaginator
    {
        if (! $this->hasTable('purchase_orders')) {
            return $this->emptyPaginator($scope);
        }

        $orders = $this->deliveredOrderQuery($scope)
            ->with('vendor:id,vendor_name')
            ->get(['purchase_orders.id', 'purchase_orders.po_number', 'purchase_orders.vendor_id', 'purchase_orders.order_date', 'purchase_orders.expected_delivery_date', 'purchase_orders.total_amount']);

        $rows = $orders->map(function (PurchaseOrder $order) {
            $firstReceipt = $this->firstReceiptDate($order->id);
            $cycleDays = $firstReceipt === null
                ? null
                : Carbon::parse($order->order_date)->diffInDays(Carbon::parse($firstReceipt));
            $late = $order->expected_delivery_date !== null
                && $firstReceipt !== null
                && $firstReceipt > $order->expected_delivery_date->toDateString();

            return [
                'po_number' => $order->po_number,
                'supplier' => $order->vendor?->vendor_name ?? '—',
                'order_date' => $order->order_date?->toDateString(),
                'expected_delivery' => $order->expected_delivery_date?->toDateString() ?? '—',
                'first_receipt' => $firstReceipt ?? '—',
                'cycle_days' => $cycleDays,
                'late' => $late ? __('Yes') : __('No'),
                'spend' => (float) $order->total_amount,
            ];
        })->sortByDesc('cycle_days')->values();

        return $this->paginateCollection($rows, $scope);
    }

    public function paginateOpenOrders(ProcurementReportScope $scope): LengthAwarePaginator
    {
        return $this->paginateStatusOrders($scope, $this->openStatuses());
    }

    public function paginateClosedOrders(ProcurementReportScope $scope): LengthAwarePaginator
    {
        return $this->paginateStatusOrders($scope, $this->closedStatuses());
    }

    public function paginateCancelledOrders(ProcurementReportScope $scope): LengthAwarePaginator
    {
        return $this->paginateStatusOrders($scope, [PurchaseOrderStatus::Cancelled]);
    }

    public function withPage(ProcurementReportScope $scope, int $page): ProcurementReportScope
    {
        return new ProcurementReportScope(
            companyId: $scope->companyId,
            branchId: $scope->branchId,
            fromDate: $scope->fromDate,
            toDate: $scope->toDate,
            supplierId: $scope->supplierId,
            warehouseId: $scope->warehouseId,
            categoryId: $scope->categoryId,
            search: $scope->search,
            tab: $scope->tab,
            topLimit: $scope->topLimit,
            page: $page,
        );
    }

    public function deliveredOrderQuery(ProcurementReportScope $scope): Builder
    {
        return $this->baseOrderQuery($scope)
            ->whereIn('purchase_orders.status', array_map(
                fn (PurchaseOrderStatus $status) => $status->value,
                array_merge($this->closedStatuses(), [PurchaseOrderStatus::PartiallyReceived]),
            ))
            ->whereHas('goodsReceipts', fn (Builder $receipt) => $receipt->where('status', GoodsReceiptStatus::Posted));
    }

    protected function lateDeliveryQuery(ProcurementReportScope $scope): Builder
    {
        $openStatuses = array_map(fn (PurchaseOrderStatus $status) => $status->value, [
            PurchaseOrderStatus::Approved,
            PurchaseOrderStatus::Sent,
            PurchaseOrderStatus::PartiallyReceived,
        ]);

        return $this->baseOrderQuery($scope)
            ->whereNotNull('purchase_orders.expected_delivery_date')
            ->where(function (Builder $inner) use ($openStatuses) {
                $inner->whereRaw(
                    '(SELECT MIN(gr.receipt_date) FROM goods_receipts gr WHERE gr.purchase_order_id = purchase_orders.id AND gr.status = ?) > purchase_orders.expected_delivery_date',
                    [GoodsReceiptStatus::Posted->value],
                )->orWhere(function (Builder $overdue) use ($openStatuses) {
                    $overdue->whereIn('purchase_orders.status', $openStatuses)
                        ->whereDate('purchase_orders.expected_delivery_date', '<', now()->toDateString())
                        ->whereDoesntHave('goodsReceipts', fn (Builder $receipt) => $receipt->where('status', GoodsReceiptStatus::Posted));
                });
            });
    }

    /**
     * @param  list<PurchaseOrderStatus>  $statuses
     */
    protected function paginateStatusOrders(ProcurementReportScope $scope, array $statuses): LengthAwarePaginator
    {
        if (! $this->hasTable('purchase_orders')) {
            return $this->emptyPaginator($scope);
        }

        $query = $this->baseOrderQuery($scope)
            ->with('vendor:id,vendor_name')
            ->whereIn('purchase_orders.status', array_map(
                fn (PurchaseOrderStatus $status) => $status->value,
                $statuses,
            ))
            ->orderByDesc('purchase_orders.order_date');

        return $this->paginate($query, $scope);
    }

    protected function firstReceiptDate(int $purchaseOrderId): ?string
    {
        if (! $this->hasTable('goods_receipts')) {
            return null;
        }

        $date = DB::table('goods_receipts')
            ->where('purchase_order_id', $purchaseOrderId)
            ->where('status', GoodsReceiptStatus::Posted->value)
            ->min('receipt_date');

        return $date ? Carbon::parse((string) $date)->toDateString() : null;
    }

    /**
     * @return list<array{label: string, orders: int, spend: float}>
     */
    protected function trendBuckets(ProcurementReportScope $scope, string $granularity, int $limit): array
    {
        $group = match ($granularity) {
            'week' => DB::raw('YEARWEEK(purchase_orders.order_date, 3)'),
            'month' => DB::raw("DATE_FORMAT(purchase_orders.order_date, '%Y-%m')"),
            'quarter' => DB::raw("CONCAT(YEAR(purchase_orders.order_date), '-Q', QUARTER(purchase_orders.order_date))"),
            'year' => DB::raw('YEAR(purchase_orders.order_date)'),
            default => DB::raw('DATE(purchase_orders.order_date)'),
        };

        $rows = $this->spendOrderQuery($scope)
            ->select(
                $group.' as bucket',
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(purchase_orders.total_amount) as spend'),
            )
            ->groupBy('bucket')
            ->orderByDesc('bucket')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();

        return $rows->map(fn ($row) => [
            'label' => (string) $row->bucket,
            'orders' => (int) $row->orders,
            'spend' => (float) $row->spend,
        ])->all();
    }

    protected function paginate(Builder $query, ProcurementReportScope $scope, bool $grouped = false): LengthAwarePaginator
    {
        if ($grouped) {
            $items = $query->get();
            $total = $items->count();
            $page = $scope->page;
            $slice = $items->slice(($page - 1) * self::PER_PAGE, self::PER_PAGE)->values();

            return new Paginator($slice, $total, self::PER_PAGE, $page, [
                'path' => request()->url(),
                'query' => request()->query(),
            ]);
        }

        return $query->paginate(self::PER_PAGE, ['*'], 'page', $scope->page)->withQueryString();
    }

    protected function paginateCollection(Collection $rows, ProcurementReportScope $scope): LengthAwarePaginator
    {
        $total = $rows->count();
        $page = $scope->page;
        $slice = $rows->slice(($page - 1) * self::PER_PAGE, self::PER_PAGE)->values();

        return new Paginator($slice, $total, self::PER_PAGE, $page, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);
    }

    protected function emptyPaginator(ProcurementReportScope $scope): LengthAwarePaginator
    {
        return new Paginator([], 0, self::PER_PAGE, $scope->page, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function mapOrderRows(LengthAwarePaginator $paginator): array
    {
        return collect($paginator->items())->map(function ($order) {
            if ($order instanceof PurchaseOrder) {
                return [
                    'po_number' => $order->po_number,
                    'supplier' => $order->vendor?->vendor_name ?? '—',
                    'order_date' => $order->order_date?->toDateString() ?? '—',
                    'expected_delivery' => $order->expected_delivery_date?->toDateString() ?? '—',
                    'status' => $order->status instanceof PurchaseOrderStatus ? $order->status->value : (string) $order->status,
                    'spend' => $this->money((float) $order->total_amount),
                ];
            }

            return (array) $order;
        })->all();
    }
}
