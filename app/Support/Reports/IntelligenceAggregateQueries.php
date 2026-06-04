<?php

namespace App\Support\Reports;

use App\Enums\CustomerInvoiceStatus;
use App\Enums\CustomerStatus;
use App\Enums\GoodsReceiptStatus;
use App\Enums\InventoryMovementType;
use App\Enums\LeadStatus;
use App\Enums\ProductionJobCardStatus;
use App\Enums\PurchaseOrderStatus;
use App\Enums\PurchaseRequestStatus;
use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Enums\SupplierBillStatus;
use App\Enums\VendorStatus;
use App\Models\Crm\Customer;
use App\Models\Crm\Lead;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryMovement;
use App\Models\Inventory\InventoryReorderAlert;
use App\Models\Inventory\InventoryValuation;
use App\Models\Procurement\GoodsReceipt;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseRequest;
use App\Models\Procurement\SupplierBill;
use App\Models\Procurement\Vendor;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class IntelligenceAggregateQueries
{
    public function hasTable(string $table): bool
    {
        return Schema::hasTable($table);
    }

    /**
     * @template TModel of \Illuminate\Database\Eloquent\Model
     * @param  class-string<TModel>  $modelClass
     * @return Builder<TModel>
     */
    public function scoped(string $modelClass, IntelligenceScope $scope, bool $branchScoped = true): Builder
    {
        $model = new $modelClass;
        $table = $model->getTable();

        /** @var Builder<TModel> $query */
        $query = $modelClass::query()->where("{$table}.company_id", $scope->companyId);

        if ($branchScoped && $scope->branchId !== null) {
            $query->where("{$table}.branch_id", $scope->branchId);
        }

        return $query;
    }

    public function money(float $amount): string
    {
        return 'KES '.number_format($amount, 0);
    }

    public function countQuotations(IntelligenceScope $scope, ?array $statuses = null): int
    {
        if (! $this->hasTable('quotations')) {
            return 0;
        }

        $q = $this->scoped(Quotation::class, $scope);

        if ($statuses !== null) {
            $q->whereIn('status', $statuses);
        }

        return (int) $q->count();
    }

    public function countQuotationsInPeriod(IntelligenceScope $scope, ?array $statuses = null, string $dateColumn = 'quotation_date'): int
    {
        if (! $this->hasTable('quotations')) {
            return 0;
        }

        $q = $this->scoped(Quotation::class, $scope)
            ->whereDate($dateColumn, '>=', $scope->fromDate)
            ->whereDate($dateColumn, '<=', $scope->toDate);

        if ($statuses !== null) {
            $q->whereIn('status', $statuses);
        }

        return (int) $q->count();
    }

    public function sumQuotationValueInPeriod(IntelligenceScope $scope, ?array $statuses = null): float
    {
        if (! $this->hasTable('quotations')) {
            return 0.0;
        }

        $q = $this->scoped(Quotation::class, $scope)
            ->whereDate('quotation_date', '>=', $scope->fromDate)
            ->whereDate('quotation_date', '<=', $scope->toDate);

        if ($statuses !== null) {
            $q->whereIn('status', $statuses);
        }

        return (float) $q->sum('total_amount');
    }

    public function countLeads(IntelligenceScope $scope, ?LeadStatus $status = null): int
    {
        if (! $this->hasTable('leads')) {
            return 0;
        }

        $q = $this->scoped(Lead::class, $scope);

        if ($status !== null) {
            $q->where('status', $status);
        }

        return (int) $q->count();
    }

    public function countCustomers(IntelligenceScope $scope, bool $activeOnly = false): int
    {
        if (! $this->hasTable('customers')) {
            return 0;
        }

        $q = $this->scoped(Customer::class, $scope);

        if ($activeOnly) {
            $q->where('status', CustomerStatus::Active);
        }

        return (int) $q->count();
    }

    public function countCustomersCreatedInPeriod(IntelligenceScope $scope): int
    {
        if (! $this->hasTable('customers')) {
            return 0;
        }

        return (int) $this->scoped(Customer::class, $scope)
            ->whereDate('created_at', '>=', $scope->fromDate)
            ->whereDate('created_at', '<=', $scope->toDate)
            ->count();
    }

    public function countSalesOrders(IntelligenceScope $scope, ?array $statuses = null, bool $inPeriod = false): int
    {
        if (! $this->hasTable('sales_orders')) {
            return 0;
        }

        $q = $this->scoped(SalesOrder::class, $scope);

        if ($statuses !== null) {
            $q->whereIn('status', $statuses);
        }

        if ($inPeriod) {
            $q->whereDate('order_date', '>=', $scope->fromDate)
                ->whereDate('order_date', '<=', $scope->toDate);
        }

        return (int) $q->count();
    }

    public function sumSalesOrderValue(IntelligenceScope $scope, bool $inPeriod = true): float
    {
        if (! $this->hasTable('sales_orders')) {
            return 0.0;
        }

        $q = $this->scoped(SalesOrder::class, $scope)
            ->whereNotIn('status', [SalesOrderStatus::Draft, SalesOrderStatus::Cancelled]);

        if ($inPeriod) {
            $q->whereDate('order_date', '>=', $scope->fromDate)
                ->whereDate('order_date', '<=', $scope->toDate);
        }

        return (float) $q->sum('total_amount');
    }

    public function sumSalesToday(IntelligenceScope $scope, string $today): float
    {
        if (! $this->hasTable('sales_orders')) {
            return 0.0;
        }

        return (float) $this->scoped(SalesOrder::class, $scope)
            ->whereDate('order_date', $today)
            ->whereNotIn('status', [SalesOrderStatus::Draft, SalesOrderStatus::Cancelled])
            ->sum('total_amount');
    }

    public function countActiveJobs(IntelligenceScope $scope): int
    {
        if (! $this->hasTable('production_job_cards')) {
            return 0;
        }

        return (int) $this->scoped(ProductionJobCard::class, $scope)->whereIn('status', [
            ProductionJobCardStatus::Queued,
            ProductionJobCardStatus::InProduction,
            ProductionJobCardStatus::QualityCheck,
            ProductionJobCardStatus::Rework,
            ProductionJobCardStatus::OnHold,
        ])->count();
    }

    public function countCompletedJobsInPeriod(IntelligenceScope $scope): int
    {
        if (! $this->hasTable('production_job_cards')) {
            return 0;
        }

        return (int) $this->scoped(ProductionJobCard::class, $scope)
            ->where('status', ProductionJobCardStatus::Completed)
            ->where(function ($q) use ($scope) {
                $q->whereDate('actual_end_date', '>=', $scope->fromDate)
                    ->whereDate('actual_end_date', '<=', $scope->toDate)
                    ->orWhere(function ($q2) use ($scope) {
                        $q2->whereNull('actual_end_date')
                            ->whereDate('updated_at', '>=', $scope->fromDate)
                            ->whereDate('updated_at', '<=', $scope->toDate);
                    });
            })
            ->count();
    }

    public function countDelayedJobs(IntelligenceScope $scope, ?string $asOfDate = null): int
    {
        if (! $this->hasTable('production_job_cards')) {
            return 0;
        }

        $asOfDate ??= $scope->toDate;

        return (int) $this->scoped(ProductionJobCard::class, $scope)
            ->whereNotIn('status', [
                ProductionJobCardStatus::Completed,
                ProductionJobCardStatus::ReadyForDispatch,
                ProductionJobCardStatus::Cancelled,
            ])
            ->whereNotNull('planned_end_date')
            ->whereDate('planned_end_date', '<', $asOfDate)
            ->count();
    }

    /**
     * @return list<array{status: string, label: string, count: int}>
     */
    public function productionPipelineCounts(IntelligenceScope $scope): array
    {
        if (! $this->hasTable('production_job_cards')) {
            return [];
        }

        $rows = $this->scoped(ProductionJobCard::class, $scope)
            ->select('status', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return collect(ProductionJobCardStatus::cases())
            ->map(fn (ProductionJobCardStatus $status) => [
                'key' => $status->value,
                'status' => $status->value,
                'label' => __(str_replace('_', ' ', ucfirst($status->value))),
                'count' => (int) ($rows[$status->value] ?? 0),
            ])
            ->all();
    }

    public function inventoryValue(IntelligenceScope $scope): ?float
    {
        if ($this->hasTable('inventory_valuations')) {
            $q = DB::table('inventory_valuations')
                ->where('company_id', $scope->companyId);

            if ($scope->branchId !== null) {
                $q->where('branch_id', $scope->branchId);
            }

            if ($scope->warehouseId !== null) {
                $q->where('warehouse_id', $scope->warehouseId);
            }

            return round((float) ($q->selectRaw('COALESCE(SUM(quantity_on_hand * average_unit_cost), 0) as total')->value('total') ?? 0), 2);
        }

        if (! $this->hasTable('inventory_movements') || ! $this->hasTable('inventory_items')) {
            return null;
        }

        return null;
    }

    public function countLowStockAlerts(IntelligenceScope $scope): int
    {
        if (! $this->hasTable('inventory_reorder_alerts')) {
            return 0;
        }

        return (int) $this->scoped(InventoryReorderAlert::class, $scope)
            ->where('is_resolved', false)
            ->count();
    }

    public function countPendingPurchaseRequests(IntelligenceScope $scope): int
    {
        if (! $this->hasTable('purchase_requests')) {
            return 0;
        }

        return (int) $this->scoped(PurchaseRequest::class, $scope)
            ->whereIn('status', [PurchaseRequestStatus::Submitted, PurchaseRequestStatus::Approved])
            ->count();
    }

    public function countPendingPurchaseOrders(IntelligenceScope $scope): int
    {
        if (! $this->hasTable('purchase_orders')) {
            return 0;
        }

        return (int) $this->scoped(PurchaseOrder::class, $scope)
            ->whereIn('status', [
                PurchaseOrderStatus::Approved,
                PurchaseOrderStatus::Sent,
                PurchaseOrderStatus::PartiallyReceived,
            ])
            ->count();
    }

    public function countGoodsAwaitingReceipt(IntelligenceScope $scope): int
    {
        if (! $this->hasTable('purchase_orders')) {
            return 0;
        }

        return (int) $this->scoped(PurchaseOrder::class, $scope)
            ->whereIn('status', [PurchaseOrderStatus::Sent, PurchaseOrderStatus::PartiallyReceived])
            ->count();
    }

    public function sumReceivables(IntelligenceScope $scope): ?float
    {
        if (! $this->hasTable('customer_invoices')) {
            return null;
        }

        return (float) $this->scoped(CustomerInvoice::class, $scope)
            ->whereIn('status', [CustomerInvoiceStatus::Posted, CustomerInvoiceStatus::Approved])
            ->where('balance_due', '>', 0)
            ->sum('balance_due');
    }

    public function sumPayables(IntelligenceScope $scope): ?float
    {
        if (! $this->hasTable('supplier_bills')) {
            return null;
        }

        return (float) $this->scoped(SupplierBill::class, $scope)
            ->whereIn('status', [SupplierBillStatus::Posted, SupplierBillStatus::Approved])
            ->where('balance_due', '>', 0)
            ->sum('balance_due');
    }

    public function sumRevenueMtd(IntelligenceScope $scope): ?float
    {
        if ($this->hasTable('customer_invoices')) {
            return (float) $this->scoped(CustomerInvoice::class, $scope)
                ->where('status', CustomerInvoiceStatus::Posted)
                ->whereDate('invoice_date', '>=', $scope->fromDate)
                ->whereDate('invoice_date', '<=', $scope->toDate)
                ->sum('total_amount');
        }

        if ($this->hasTable('sales_orders')) {
            return $this->sumSalesOrderValue($scope, true);
        }

        return null;
    }

    public function countVendors(IntelligenceScope $scope, bool $activeOnly = true): int
    {
        if (! $this->hasTable('vendors')) {
            return 0;
        }

        $q = $this->scoped(Vendor::class, $scope, false);

        if ($activeOnly) {
            $q->where('status', VendorStatus::Active);
        }

        return (int) $q->count();
    }

    public function countMovementInPeriod(IntelligenceScope $scope, InventoryMovementType $type): int
    {
        if (! $this->hasTable('inventory_movements')) {
            return 0;
        }

        $q = $this->scoped(InventoryMovement::class, $scope)
            ->where('movement_type', $type)
            ->whereDate('movement_date', '>=', $scope->fromDate)
            ->whereDate('movement_date', '<=', $scope->toDate);

        return (int) $q->count();
    }

    public function countEmployees(IntelligenceScope $scope): int
    {
        if (! $this->hasTable('employees')) {
            return 0;
        }

        $q = DB::table('employees')
            ->where('company_id', $scope->companyId)
            ->where('is_active', true);

        return (int) $q->count();
    }

    /**
     * @return list<array{label: string, bucket: string, amount: float, amount_display: string}>
     */
    public function receivableAgingBuckets(IntelligenceScope $scope): array
    {
        if (! $this->hasTable('customer_invoices')) {
            return [];
        }

        $today = now()->toDateString();
        $buckets = [
            'current' => ['label' => __('Current'), 'min' => null, 'max' => 0],
            '1_30' => ['label' => __('1–30 days'), 'min' => 1, 'max' => 30],
            '31_60' => ['label' => __('31–60 days'), 'min' => 31, 'max' => 60],
            '61_90' => ['label' => __('61–90 days'), 'min' => 61, 'max' => 90],
            '90_plus' => ['label' => __('90+ days'), 'min' => 91, 'max' => null],
        ];

        $rows = $this->scoped(CustomerInvoice::class, $scope)
            ->whereIn('status', [CustomerInvoiceStatus::Posted, CustomerInvoiceStatus::Approved])
            ->where('balance_due', '>', 0)
            ->get(['balance_due', 'due_date']);

        $totals = array_fill_keys(array_keys($buckets), 0.0);

        foreach ($rows as $invoice) {
            $days = $invoice->due_date
                ? max(0, (int) \Carbon\Carbon::parse($invoice->due_date)->diffInDays($today, false))
                : 0;

            $key = match (true) {
                $days <= 0 => 'current',
                $days <= 30 => '1_30',
                $days <= 60 => '31_60',
                $days <= 90 => '61_90',
                default => '90_plus',
            };

            $totals[$key] += (float) $invoice->balance_due;
        }

        return collect($buckets)->map(fn (array $meta, string $key) => [
            'label' => $meta['label'],
            'bucket' => $key,
            'amount' => $totals[$key],
            'amount_display' => $this->money($totals[$key]),
        ])->values()->all();
    }

    /**
     * @return list<array{label: string, bucket: string, amount: float, amount_display: string}>
     */
    public function payableAgingBuckets(IntelligenceScope $scope): array
    {
        if (! $this->hasTable('supplier_bills')) {
            return [];
        }

        $today = now()->toDateString();
        $buckets = [
            'current' => __('Current'),
            '1_30' => __('1–30 days'),
            '31_60' => __('31–60 days'),
            '61_90' => __('61–90 days'),
            '90_plus' => __('90+ days'),
        ];

        $rows = $this->scoped(SupplierBill::class, $scope)
            ->whereIn('status', [SupplierBillStatus::Posted, SupplierBillStatus::Approved])
            ->where('balance_due', '>', 0)
            ->get(['balance_due', 'due_date']);

        $totals = array_fill_keys(array_keys($buckets), 0.0);

        foreach ($rows as $bill) {
            $days = $bill->due_date
                ? max(0, (int) \Carbon\Carbon::parse($bill->due_date)->diffInDays($today, false))
                : 0;

            $key = match (true) {
                $days <= 0 => 'current',
                $days <= 30 => '1_30',
                $days <= 60 => '31_60',
                $days <= 90 => '61_90',
                default => '90_plus',
            };

            $totals[$key] += (float) $bill->balance_due;
        }

        return collect($buckets)->map(fn (string $label, string $key) => [
            'label' => $label,
            'bucket' => $key,
            'amount' => $totals[$key],
            'amount_display' => $this->money($totals[$key]),
        ])->values()->all();
    }

    public function countGoodsReceiptsInPeriod(IntelligenceScope $scope, ?GoodsReceiptStatus $status = null): int
    {
        if (! $this->hasTable('goods_receipts')) {
            return 0;
        }

        $q = $this->scoped(GoodsReceipt::class, $scope)
            ->whereDate('receipt_date', '>=', $scope->fromDate)
            ->whereDate('receipt_date', '<=', $scope->toDate);

        if ($status !== null) {
            $q->where('status', $status);
        }

        return (int) $q->count();
    }

    public function sumProcurementValueInPeriod(IntelligenceScope $scope): float
    {
        if (! $this->hasTable('purchase_orders')) {
            return 0.0;
        }

        return (float) $this->scoped(PurchaseOrder::class, $scope)
            ->whereDate('order_date', '>=', $scope->fromDate)
            ->whereDate('order_date', '<=', $scope->toDate)
            ->whereNotIn('status', [PurchaseOrderStatus::Cancelled, PurchaseOrderStatus::Rejected])
            ->sum('total_amount');
    }
}
