<?php

namespace App\Support\Procurement\Performance;

use App\Enums\GoodsReceiptStatus;
use App\Enums\PurchaseOrderStatus;
use App\Enums\SupplierQuotationStatus;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\Vendor;
use App\Support\Procurement\Reports\ProcurementReportQueries;
use App\Support\Procurement\Reports\ProcurementReportScope;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SupplierPerformanceQueries
{
    public const PER_PAGE = 25;

    public function __construct(
        protected ProcurementReportQueries $procurementQueries,
        protected SupplierPerformanceScoreCalculator $scoreCalculator,
    ) {}

    public function money(float $amount): string
    {
        return $this->procurementQueries->money($amount);
    }

    public function days(?float $value): string
    {
        return $this->procurementQueries->days($value);
    }

    public function percent(?float $value): string
    {
        return $this->procurementQueries->percent($value);
    }

    public function hasTable(string $table): bool
    {
        return Schema::hasTable($table);
    }

    protected function procurementScope(SupplierPerformanceScope $scope, ?int $supplierId = null): ProcurementReportScope
    {
        return new ProcurementReportScope(
            companyId: $scope->companyId,
            branchId: $scope->branchId,
            fromDate: $scope->fromDate,
            toDate: $scope->toDate,
            supplierId: $supplierId ?? $scope->supplierId,
            warehouseId: $scope->warehouseId,
            categoryId: $scope->categoryId,
            search: $scope->search,
        );
    }

    /**
     * @return array{
     *     total_purchase_value: float,
     *     purchase_count: int,
     *     average_delivery_time: ?float,
     *     on_time_percent: ?float,
     *     late_percent: ?float,
     *     quality_acceptance_percent: ?float,
     *     rejection_percent: ?float,
     *     average_response_days: ?float,
     *     rfq_participation_percent: ?float,
     *     award_win_percent: ?float,
     * }
     */
    public function summaryMetrics(SupplierPerformanceScope $scope): array
    {
        $procScope = $this->procurementScope($scope);
        $onTime = $this->procurementQueries->onTimeDeliveryPercent($procScope);
        $avgDelivery = $this->procurementQueries->averageCycleTimeDays($procScope);
        $quality = $this->aggregateQualityMetrics($scope);

        return [
            'total_purchase_value' => $this->procurementQueries->totalSpend($procScope),
            'purchase_count' => $this->procurementQueries->totalOrders($procScope),
            'average_delivery_time' => $avgDelivery,
            'on_time_percent' => $onTime,
            'late_percent' => $onTime === null ? null : round(100 - $onTime, 1),
            'quality_acceptance_percent' => $quality['acceptance_percent'],
            'rejection_percent' => $quality['rejection_percent'],
            'average_response_days' => $this->averageResponseDays($scope),
            'rfq_participation_percent' => $this->rfqParticipationPercent($scope),
            'award_win_percent' => $this->awardWinPercent($scope),
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function supplierScorecardRows(SupplierPerformanceScope $scope): Collection
    {
        if (! $this->hasTable('purchase_orders') || ! $this->hasTable('vendors')) {
            return collect();
        }

        $vendorIds = $this->procurementQueries
            ->baseOrderQuery($this->procurementScope($scope))
            ->distinct()
            ->pluck('purchase_orders.vendor_id');

        if ($vendorIds->isEmpty()) {
            return collect();
        }

        $vendors = Vendor::query()->whereIn('id', $vendorIds)->orderBy('vendor_name')->get(['id', 'vendor_name']);

        return $vendors->map(function (Vendor $vendor) use ($scope) {
            $metrics = $this->vendorMetrics($scope, $vendor->id);
            $overall = $this->scoreCalculator->overallScore($metrics);

            return [
                'supplier' => $vendor->vendor_name,
                'purchase_count' => $metrics['purchase_count'],
                'total_purchase_value' => $metrics['total_purchase_value'],
                'average_delivery_time' => $metrics['average_delivery_time'],
                'on_time_percent' => $metrics['on_time_percent'],
                'late_percent' => $metrics['late_percent'],
                'quality_acceptance_percent' => $metrics['quality_acceptance_percent'],
                'rejection_percent' => $metrics['rejection_percent'],
                'rfq_participation_percent' => $metrics['rfq_participation_percent'],
                'award_win_percent' => $metrics['award_win_percent'],
                'responsiveness_days' => $metrics['average_response_days'],
                'overall_score' => $overall,
                'grade' => $this->scoreCalculator->grade($overall),
            ];
        })->sortByDesc('overall_score')->values();
    }

    public function paginateScorecard(SupplierPerformanceScope $scope): LengthAwarePaginator
    {
        return $this->paginateCollection($this->supplierScorecardRows($scope), $scope);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function deliveryAnalysisRows(SupplierPerformanceScope $scope): Collection
    {
        if (! $this->hasTable('purchase_orders')) {
            return collect();
        }

        $orders = $this->procurementQueries
            ->deliveredOrderQuery($this->procurementScope($scope))
            ->with('vendor:id,vendor_name')
            ->get([
                'purchase_orders.id',
                'purchase_orders.po_number',
                'purchase_orders.vendor_id',
                'purchase_orders.expected_delivery_date',
            ]);

        return $orders->map(function (PurchaseOrder $order) {
            $actual = $this->firstReceiptDate($order->id);
            $expected = $order->expected_delivery_date?->toDateString();
            $variance = null;
            $daysLate = 0;
            $daysEarly = 0;

            if ($expected !== null && $actual !== null) {
                $variance = Carbon::parse($expected)->diffInDays(Carbon::parse($actual), false);
                if ($variance > 0) {
                    $daysLate = $variance;
                } elseif ($variance < 0) {
                    $daysEarly = abs($variance);
                }
            }

            return [
                'po_number' => $order->po_number,
                'supplier' => $order->vendor?->vendor_name ?? '—',
                'expected_date' => $expected ?? '—',
                'actual_date' => $actual ?? '—',
                'variance' => $variance === null ? '—' : (string) $variance,
                'days_late' => (string) $daysLate,
                'days_early' => (string) $daysEarly,
            ];
        })->sortByDesc('days_late')->values();
    }

    public function paginateDeliveryAnalysis(SupplierPerformanceScope $scope): LengthAwarePaginator
    {
        return $this->paginateCollection($this->deliveryAnalysisRows($scope), $scope);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function qualityAnalysisRows(SupplierPerformanceScope $scope): Collection
    {
        if (! $this->hasTable('purchase_orders') || ! $this->hasTable('vendors')) {
            return collect();
        }

        $vendorIds = $this->procurementQueries
            ->baseOrderQuery($this->procurementScope($scope))
            ->distinct()
            ->pluck('purchase_orders.vendor_id');

        return Vendor::query()
            ->whereIn('id', $vendorIds)
            ->orderBy('vendor_name')
            ->get(['id', 'vendor_name'])
            ->map(function (Vendor $vendor) use ($scope) {
                $quality = $this->vendorQualityMetrics($scope, $vendor->id);

                return [
                    'supplier' => $vendor->vendor_name,
                    'items_received' => $quality['items_received'],
                    'items_rejected' => $quality['items_rejected'],
                    'defect_rate' => $quality['defect_rate'],
                    'return_rate' => $quality['return_rate'],
                ];
            });
    }

    public function paginateQualityAnalysis(SupplierPerformanceScope $scope): LengthAwarePaginator
    {
        return $this->paginateCollection($this->qualityAnalysisRows($scope), $scope);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function spendAnalysisRows(SupplierPerformanceScope $scope): Collection
    {
        if (! $this->hasTable('purchase_orders') || ! $this->hasTable('vendors')) {
            return collect();
        }

        $rows = $this->procurementQueries
            ->spendOrderQuery($this->procurementScope($scope))
            ->join('vendors', 'vendors.id', '=', 'purchase_orders.vendor_id')
            ->select(
                'vendors.vendor_name as supplier',
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(purchase_orders.total_amount) as spend'),
                DB::raw('AVG(purchase_orders.total_amount) as average_order'),
            )
            ->groupBy('vendors.id', 'vendors.vendor_name')
            ->orderByDesc('spend')
            ->get();

        return $rows->map(fn ($row) => [
            'supplier' => (string) $row->supplier,
            'spend' => (float) $row->spend,
            'orders' => (int) $row->orders,
            'average_order_value' => (float) $row->average_order,
        ]);
    }

    public function paginateSpendAnalysis(SupplierPerformanceScope $scope): LengthAwarePaginator
    {
        return $this->paginateCollection($this->spendAnalysisRows($scope), $scope);
    }

    /**
     * @return array{monthly: list<array<string, mixed>>, quarterly: list<array<string, mixed>>, annual: list<array<string, mixed>>}
     */
    public function performanceTrendSeries(SupplierPerformanceScope $scope): array
    {
        if (! $this->hasTable('purchase_orders')) {
            return ['monthly' => [], 'quarterly' => [], 'annual' => []];
        }

        return [
            'monthly' => $this->trendBuckets($scope, 'month', 12),
            'quarterly' => $this->trendBuckets($scope, 'quarter', 8),
            'annual' => $this->trendBuckets($scope, 'year', 5),
        ];
    }

    /**
     * @return array{
     *     top_suppliers: list<array<string, mixed>>,
     *     most_reliable: list<array<string, mixed>>,
     *     fastest_delivery: list<array<string, mixed>>,
     *     best_price: list<array<string, mixed>>,
     *     highest_spend: list<array<string, mixed>>,
     * }
     */
    public function rankings(SupplierPerformanceScope $scope): array
    {
        $scorecard = $this->supplierScorecardRows($scope);
        $spend = $this->spendAnalysisRows($scope);

        return [
            'top_suppliers' => $scorecard->sortByDesc('overall_score')->take($scope->topLimit)->values()->all(),
            'most_reliable' => $scorecard->sortByDesc('on_time_percent')->take($scope->topLimit)->values()->all(),
            'fastest_delivery' => $scorecard
                ->filter(fn (array $row) => $row['average_delivery_time'] !== null)
                ->sortBy('average_delivery_time')
                ->take($scope->topLimit)
                ->values()
                ->all(),
            'best_price' => $this->bestPriceRankings($scope),
            'highest_spend' => $spend->take($scope->topLimit)->values()->all(),
        ];
    }

    public function withPage(SupplierPerformanceScope $scope, int $page): SupplierPerformanceScope
    {
        return new SupplierPerformanceScope(
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

    /**
     * @return array{
     *     purchase_count: int,
     *     total_purchase_value: float,
     *     average_delivery_time: ?float,
     *     on_time_percent: ?float,
     *     late_percent: ?float,
     *     quality_acceptance_percent: ?float,
     *     rejection_percent: ?float,
     *     average_response_days: ?float,
     *     responsiveness_score: ?float,
     *     rfq_participation_percent: ?float,
     *     award_win_percent: ?float,
     *     price_competitiveness: ?float,
     * }
     */
    protected function vendorMetrics(SupplierPerformanceScope $scope, int $vendorId): array
    {
        $procScope = $this->procurementScope($scope, $vendorId);
        $onTime = $this->vendorOnTimePercent($procScope);
        $quality = $this->vendorQualityMetrics($scope, $vendorId);
        $avgResponse = $this->vendorAverageResponseDays($scope, $vendorId);
        $priceScore = $this->vendorPriceCompetitiveness($scope, $vendorId);

        return [
            'purchase_count' => $this->procurementQueries->totalOrders($procScope),
            'total_purchase_value' => $this->procurementQueries->totalSpend($procScope),
            'average_delivery_time' => $this->procurementQueries->averageCycleTimeDays($procScope),
            'on_time_percent' => $onTime,
            'late_percent' => $onTime === null ? null : round(100 - $onTime, 1),
            'quality_acceptance_percent' => $quality['acceptance_percent'],
            'rejection_percent' => $quality['rejection_percent'],
            'average_response_days' => $avgResponse,
            'responsiveness_score' => $this->scoreCalculator->responsivenessScore($avgResponse),
            'rfq_participation_percent' => $this->vendorRfqParticipationPercent($scope, $vendorId),
            'award_win_percent' => $this->vendorAwardWinPercent($scope, $vendorId),
            'price_competitiveness' => $priceScore,
        ];
    }

    /**
     * @return array{acceptance_percent: ?float, rejection_percent: ?float}
     */
    protected function aggregateQualityMetrics(SupplierPerformanceScope $scope): array
    {
        if (! $this->hasTable('purchase_order_items') || ! $this->hasTable('goods_receipt_items')) {
            return ['acceptance_percent' => null, 'rejection_percent' => null];
        }

        $orderIds = $this->procurementQueries
            ->deliveredOrderQuery($this->procurementScope($scope))
            ->pluck('purchase_orders.id');

        if ($orderIds->isEmpty()) {
            return ['acceptance_percent' => null, 'rejection_percent' => null];
        }

        $ordered = (float) DB::table('purchase_order_items')->whereIn('purchase_order_id', $orderIds)->sum('quantity');
        $received = (float) DB::table('goods_receipt_items')
            ->join('goods_receipts', 'goods_receipts.id', '=', 'goods_receipt_items.goods_receipt_id')
            ->whereIn('goods_receipts.purchase_order_id', $orderIds)
            ->where('goods_receipts.status', GoodsReceiptStatus::Posted->value)
            ->sum('goods_receipt_items.quantity_received');

        if ($ordered <= 0) {
            return ['acceptance_percent' => null, 'rejection_percent' => null];
        }

        $acceptance = min(100, round(($received / $ordered) * 100, 1));

        return [
            'acceptance_percent' => $acceptance,
            'rejection_percent' => round(100 - $acceptance, 1),
        ];
    }

    /**
     * @return array{
     *     items_received: float,
     *     items_rejected: float,
     *     defect_rate: ?float,
     *     return_rate: ?float,
     *     acceptance_percent: ?float,
     *     rejection_percent: ?float,
     * }
     */
    protected function vendorQualityMetrics(SupplierPerformanceScope $scope, int $vendorId): array
    {
        $procScope = $this->procurementScope($scope, $vendorId);
        $orderIds = $this->procurementQueries->deliveredOrderQuery($procScope)->pluck('purchase_orders.id');

        $ordered = 0.0;
        $received = 0.0;

        if ($orderIds->isNotEmpty() && $this->hasTable('purchase_order_items')) {
            $ordered = (float) DB::table('purchase_order_items')->whereIn('purchase_order_id', $orderIds)->sum('quantity');
            $received = (float) DB::table('goods_receipt_items')
                ->join('goods_receipts', 'goods_receipts.id', '=', 'goods_receipt_items.goods_receipt_id')
                ->whereIn('goods_receipts.purchase_order_id', $orderIds)
                ->where('goods_receipts.status', GoodsReceiptStatus::Posted->value)
                ->sum('goods_receipt_items.quantity_received');
        }

        $itemsRejected = max(0, $ordered - $received);
        $acceptance = $ordered > 0 ? min(100, round(($received / $ordered) * 100, 1)) : null;
        $defectRate = $ordered > 0 ? round(($itemsRejected / $ordered) * 100, 1) : null;

        $returnRate = $this->vendorQuotationRejectionRate($scope, $vendorId);

        return [
            'items_received' => $received,
            'items_rejected' => $itemsRejected,
            'defect_rate' => $defectRate,
            'return_rate' => $returnRate,
            'acceptance_percent' => $acceptance,
            'rejection_percent' => $acceptance === null ? null : round(100 - $acceptance, 1),
        ];
    }

    protected function vendorOnTimePercent(ProcurementReportScope $scope): ?float
    {
        return $this->procurementQueries->onTimeDeliveryPercent($scope);
    }

    protected function averageResponseDays(SupplierPerformanceScope $scope): ?float
    {
        if (! $this->hasTable('rfq_vendors') || ! $this->hasTable('rfqs')) {
            return null;
        }

        $rows = DB::table('rfq_vendors')
            ->join('rfqs', 'rfqs.id', '=', 'rfq_vendors.rfq_id')
            ->where('rfqs.company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('rfqs.branch_id', $scope->branchId))
            ->whereDate('rfqs.issue_date', '>=', $scope->fromDate)
            ->whereDate('rfqs.issue_date', '<=', $scope->toDate)
            ->whereNotNull('rfq_vendors.responded_at')
            ->whereNotNull('rfq_vendors.invited_at')
            ->select('rfq_vendors.invited_at', 'rfq_vendors.responded_at')
            ->get();

        if ($rows->isEmpty()) {
            return null;
        }

        $days = $rows->map(fn ($row) => Carbon::parse($row->invited_at)->diffInDays(Carbon::parse($row->responded_at)));

        return round((float) $days->avg(), 1);
    }

    protected function vendorAverageResponseDays(SupplierPerformanceScope $scope, int $vendorId): ?float
    {
        if (! $this->hasTable('rfq_vendors') || ! $this->hasTable('rfqs')) {
            return null;
        }

        $rows = DB::table('rfq_vendors')
            ->join('rfqs', 'rfqs.id', '=', 'rfq_vendors.rfq_id')
            ->where('rfqs.company_id', $scope->companyId)
            ->where('rfq_vendors.vendor_id', $vendorId)
            ->when($scope->branchId, fn ($q) => $q->where('rfqs.branch_id', $scope->branchId))
            ->whereDate('rfqs.issue_date', '>=', $scope->fromDate)
            ->whereDate('rfqs.issue_date', '<=', $scope->toDate)
            ->whereNotNull('rfq_vendors.responded_at')
            ->whereNotNull('rfq_vendors.invited_at')
            ->select('rfq_vendors.invited_at', 'rfq_vendors.responded_at')
            ->get();

        if ($rows->isEmpty()) {
            return null;
        }

        $days = $rows->map(fn ($row) => Carbon::parse($row->invited_at)->diffInDays(Carbon::parse($row->responded_at)));

        return round((float) $days->avg(), 1);
    }

    protected function rfqParticipationPercent(SupplierPerformanceScope $scope): ?float
    {
        if (! $this->hasTable('rfq_vendors') || ! $this->hasTable('rfqs')) {
            return null;
        }

        $invited = (int) DB::table('rfq_vendors')
            ->join('rfqs', 'rfqs.id', '=', 'rfq_vendors.rfq_id')
            ->where('rfqs.company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('rfqs.branch_id', $scope->branchId))
            ->whereDate('rfqs.issue_date', '>=', $scope->fromDate)
            ->whereDate('rfqs.issue_date', '<=', $scope->toDate)
            ->count();

        if ($invited === 0) {
            return null;
        }

        $responded = (int) DB::table('rfq_vendors')
            ->join('rfqs', 'rfqs.id', '=', 'rfq_vendors.rfq_id')
            ->where('rfqs.company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('rfqs.branch_id', $scope->branchId))
            ->whereDate('rfqs.issue_date', '>=', $scope->fromDate)
            ->whereDate('rfqs.issue_date', '<=', $scope->toDate)
            ->where('rfq_vendors.invitation_status', 'responded')
            ->count();

        return round(($responded / $invited) * 100, 1);
    }

    protected function vendorRfqParticipationPercent(SupplierPerformanceScope $scope, int $vendorId): ?float
    {
        if (! $this->hasTable('rfq_vendors') || ! $this->hasTable('rfqs')) {
            return null;
        }

        $invited = (int) DB::table('rfq_vendors')
            ->join('rfqs', 'rfqs.id', '=', 'rfq_vendors.rfq_id')
            ->where('rfqs.company_id', $scope->companyId)
            ->where('rfq_vendors.vendor_id', $vendorId)
            ->when($scope->branchId, fn ($q) => $q->where('rfqs.branch_id', $scope->branchId))
            ->whereDate('rfqs.issue_date', '>=', $scope->fromDate)
            ->whereDate('rfqs.issue_date', '<=', $scope->toDate)
            ->count();

        if ($invited === 0) {
            return null;
        }

        $responded = (int) DB::table('rfq_vendors')
            ->join('rfqs', 'rfqs.id', '=', 'rfq_vendors.rfq_id')
            ->where('rfqs.company_id', $scope->companyId)
            ->where('rfq_vendors.vendor_id', $vendorId)
            ->when($scope->branchId, fn ($q) => $q->where('rfqs.branch_id', $scope->branchId))
            ->whereDate('rfqs.issue_date', '>=', $scope->fromDate)
            ->whereDate('rfqs.issue_date', '<=', $scope->toDate)
            ->where('rfq_vendors.invitation_status', 'responded')
            ->count();

        return round(($responded / $invited) * 100, 1);
    }

    protected function awardWinPercent(SupplierPerformanceScope $scope): ?float
    {
        if (! $this->hasTable('rfqs')) {
            return null;
        }

        $invited = (int) DB::table('rfq_vendors')
            ->join('rfqs', 'rfqs.id', '=', 'rfq_vendors.rfq_id')
            ->where('rfqs.company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('rfqs.branch_id', $scope->branchId))
            ->whereDate('rfqs.issue_date', '>=', $scope->fromDate)
            ->whereDate('rfqs.issue_date', '<=', $scope->toDate)
            ->count();

        if ($invited === 0) {
            return null;
        }

        $awarded = (int) DB::table('rfqs')
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->whereDate('issue_date', '>=', $scope->fromDate)
            ->whereDate('issue_date', '<=', $scope->toDate)
            ->whereNotNull('awarded_vendor_id')
            ->count();

        return round(($awarded / $invited) * 100, 1);
    }

    protected function vendorAwardWinPercent(SupplierPerformanceScope $scope, int $vendorId): ?float
    {
        if (! $this->hasTable('rfqs') || ! $this->hasTable('rfq_vendors')) {
            return null;
        }

        $invited = (int) DB::table('rfq_vendors')
            ->join('rfqs', 'rfqs.id', '=', 'rfq_vendors.rfq_id')
            ->where('rfqs.company_id', $scope->companyId)
            ->where('rfq_vendors.vendor_id', $vendorId)
            ->when($scope->branchId, fn ($q) => $q->where('rfqs.branch_id', $scope->branchId))
            ->whereDate('rfqs.issue_date', '>=', $scope->fromDate)
            ->whereDate('rfqs.issue_date', '<=', $scope->toDate)
            ->count();

        if ($invited === 0) {
            return null;
        }

        $awarded = (int) DB::table('rfqs')
            ->where('company_id', $scope->companyId)
            ->where('awarded_vendor_id', $vendorId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->whereDate('issue_date', '>=', $scope->fromDate)
            ->whereDate('issue_date', '<=', $scope->toDate)
            ->count();

        return round(($awarded / $invited) * 100, 1);
    }

    protected function vendorQuotationRejectionRate(SupplierPerformanceScope $scope, int $vendorId): ?float
    {
        if (! $this->hasTable('supplier_quotations')) {
            return null;
        }

        $total = (int) DB::table('supplier_quotations')
            ->where('company_id', $scope->companyId)
            ->where('vendor_id', $vendorId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->whereDate('quotation_date', '>=', $scope->fromDate)
            ->whereDate('quotation_date', '<=', $scope->toDate)
            ->count();

        if ($total === 0) {
            return null;
        }

        $rejected = (int) DB::table('supplier_quotations')
            ->where('company_id', $scope->companyId)
            ->where('vendor_id', $vendorId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->whereDate('quotation_date', '>=', $scope->fromDate)
            ->whereDate('quotation_date', '<=', $scope->toDate)
            ->where('status', SupplierQuotationStatus::Rejected->value)
            ->count();

        return round(($rejected / $total) * 100, 1);
    }

    protected function vendorPriceCompetitiveness(SupplierPerformanceScope $scope, int $vendorId): ?float
    {
        if (! $this->hasTable('vendor_comparisons')) {
            return null;
        }

        $comparisons = DB::table('vendor_comparisons')
            ->join('rfqs', 'rfqs.id', '=', 'vendor_comparisons.rfq_id')
            ->where('vendor_comparisons.company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('vendor_comparisons.branch_id', $scope->branchId))
            ->whereDate('vendor_comparisons.comparison_date', '>=', $scope->fromDate)
            ->whereDate('vendor_comparisons.comparison_date', '<=', $scope->toDate)
            ->whereNotNull('vendor_comparisons.matrix')
            ->get(['vendor_comparisons.matrix']);

        $scores = collect();

        foreach ($comparisons as $comparison) {
            $matrix = json_decode((string) $comparison->matrix, true);
            if (! is_array($matrix)) {
                continue;
            }

            $vendorEntry = collect($matrix)->firstWhere('vendor_id', $vendorId);
            if (! is_array($vendorEntry)) {
                continue;
            }

            if (isset($vendorEntry['score'])) {
                $scores->push((float) $vendorEntry['score']);
            }
        }

        return $scores->isEmpty() ? null : round((float) $scores->avg(), 1);
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function bestPriceRankings(SupplierPerformanceScope $scope): array
    {
        if (! $this->hasTable('vendor_comparisons') || ! $this->hasTable('vendors')) {
            return [];
        }

        $scores = [];
        $counts = [];

        $comparisons = DB::table('vendor_comparisons')
            ->join('rfqs', 'rfqs.id', '=', 'vendor_comparisons.rfq_id')
            ->where('vendor_comparisons.company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('vendor_comparisons.branch_id', $scope->branchId))
            ->whereDate('vendor_comparisons.comparison_date', '>=', $scope->fromDate)
            ->whereDate('vendor_comparisons.comparison_date', '<=', $scope->toDate)
            ->whereNotNull('vendor_comparisons.matrix')
            ->get(['vendor_comparisons.matrix']);

        foreach ($comparisons as $comparison) {
            $matrix = json_decode((string) $comparison->matrix, true);
            if (! is_array($matrix)) {
                continue;
            }

            foreach ($matrix as $entry) {
                if (! is_array($entry) || ! isset($entry['vendor_id'], $entry['score'])) {
                    continue;
                }

                $vendorId = (int) $entry['vendor_id'];
                $scores[$vendorId] = ($scores[$vendorId] ?? 0) + (float) $entry['score'];
                $counts[$vendorId] = ($counts[$vendorId] ?? 0) + 1;
            }
        }

        if (empty($scores)) {
            return [];
        }

        $vendorNames = Vendor::query()->whereIn('id', array_keys($scores))->pluck('vendor_name', 'id');

        return collect($scores)
            ->map(fn (float $total, int $vendorId) => [
                'supplier' => $vendorNames[$vendorId] ?? __('Vendor #:id', ['id' => $vendorId]),
                'price_score' => round($total / ($counts[$vendorId] ?? 1), 1),
            ])
            ->sortByDesc('price_score')
            ->take($scope->topLimit)
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function trendBuckets(SupplierPerformanceScope $scope, string $granularity, int $limit): array
    {
        $group = match ($granularity) {
            'quarter' => DB::raw("CONCAT(YEAR(purchase_orders.order_date), '-Q', QUARTER(purchase_orders.order_date))"),
            'year' => DB::raw('YEAR(purchase_orders.order_date)'),
            default => DB::raw("DATE_FORMAT(purchase_orders.order_date, '%Y-%m')"),
        };

        $rows = $this->procurementQueries
            ->spendOrderQuery($this->procurementScope($scope))
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

        return $rows->map(function ($row) use ($scope, $granularity) {
            $bucketScope = $this->bucketScope($scope, (string) $row->bucket, $granularity);
            $onTime = $this->procurementQueries->onTimeDeliveryPercent($this->procurementScope($bucketScope));

            return [
                'label' => (string) $row->bucket,
                'orders' => (int) $row->orders,
                'spend' => (float) $row->spend,
                'on_time_percent' => $onTime,
            ];
        })->all();
    }

    protected function bucketScope(SupplierPerformanceScope $scope, string $bucket, string $granularity): SupplierPerformanceScope
    {
        if ($granularity === 'year') {
            return new SupplierPerformanceScope(
                companyId: $scope->companyId,
                branchId: $scope->branchId,
                fromDate: "{$bucket}-01-01",
                toDate: "{$bucket}-12-31",
                supplierId: $scope->supplierId,
                warehouseId: $scope->warehouseId,
                categoryId: $scope->categoryId,
                search: $scope->search,
                tab: $scope->tab,
                topLimit: $scope->topLimit,
                page: $scope->page,
            );
        }

        if ($granularity === 'quarter' && preg_match('/^(\d{4})-Q([1-4])$/', $bucket, $matches)) {
            $year = (int) $matches[1];
            $quarter = (int) $matches[2];
            $startMonth = (($quarter - 1) * 3) + 1;
            $from = sprintf('%04d-%02d-01', $year, $startMonth);
            $to = Carbon::parse($from)->endOfQuarter()->toDateString();

            return new SupplierPerformanceScope(
                companyId: $scope->companyId,
                branchId: $scope->branchId,
                fromDate: $from,
                toDate: $to,
                supplierId: $scope->supplierId,
                warehouseId: $scope->warehouseId,
                categoryId: $scope->categoryId,
                search: $scope->search,
                tab: $scope->tab,
                topLimit: $scope->topLimit,
                page: $scope->page,
            );
        }

        $from = "{$bucket}-01";
        $to = Carbon::parse($from)->endOfMonth()->toDateString();

        return new SupplierPerformanceScope(
            companyId: $scope->companyId,
            branchId: $scope->branchId,
            fromDate: $from,
            toDate: $to,
            supplierId: $scope->supplierId,
            warehouseId: $scope->warehouseId,
            categoryId: $scope->categoryId,
            search: $scope->search,
            tab: $scope->tab,
            topLimit: $scope->topLimit,
            page: $scope->page,
        );
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

        return $date ? (string) $date : null;
    }

    protected function paginateCollection(Collection $rows, SupplierPerformanceScope $scope): LengthAwarePaginator
    {
        $total = $rows->count();
        $page = $scope->page;
        $slice = $rows->slice(($page - 1) * self::PER_PAGE, self::PER_PAGE)->values();

        return new Paginator($slice, $total, self::PER_PAGE, $page, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);
    }
}
