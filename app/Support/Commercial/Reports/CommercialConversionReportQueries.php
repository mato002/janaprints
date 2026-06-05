<?php

namespace App\Support\Commercial\Reports;

use App\Enums\Dispatch\DeliveryNoteStatus;
use App\Enums\ProductionJobCardStatus;
use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Branch;
use App\Models\Crm\Lead;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CommercialConversionReportQueries
{
    public function __construct(
        protected CommercialConversionReportReadiness $readiness,
    ) {}

    public function hasTable(string $table): bool
    {
        return Schema::hasTable($table);
    }

    /**
     * @return array{leads: int, quotes: int, orders: int, production: int, dispatch: int, delivered: int}
     */
    public function funnelCounts(CommercialConversionReportScope $scope): array
    {
        return [
            'leads' => $this->countLeads($scope),
            'quotes' => $this->countQuotations($scope),
            'orders' => $this->countOrders($scope),
            'production' => $this->countProduction($scope),
            'dispatch' => $this->countDispatch($scope),
            'delivered' => $this->countDelivered($scope),
        ];
    }

    /**
     * @return list<array{key: string, label: string, count: int, conversion: ?string, drop_off: ?string, highlight?: bool}>
     */
    public function funnelStages(CommercialConversionReportScope $scope, ?string $highlightKey = null): array
    {
        $counts = $this->funnelCounts($scope);
        $keys = ['leads', 'quotes', 'orders', 'production', 'dispatch', 'delivered'];
        $labels = [
            'leads' => __('Leads'),
            'quotes' => __('Quotes'),
            'orders' => __('Orders'),
            'production' => __('Production'),
            'dispatch' => __('Dispatch'),
            'delivered' => __('Delivered'),
        ];

        $stages = [];
        $previous = null;

        foreach ($keys as $key) {
            $count = $counts[$key];
            $conversion = null;
            $dropOff = null;

            if ($previous !== null && $previous > 0) {
                $rate = round(($count / $previous) * 100, 1);
                $conversion = $rate.'%';
                $dropOff = round(100 - $rate, 1).'%';
            }

            $stages[] = [
                'key' => $key,
                'label' => $labels[$key],
                'count' => $count,
                'conversion' => $conversion,
                'drop_off' => $dropOff,
                'highlight' => $highlightKey === $key,
            ];

            $previous = $count;
        }

        return $stages;
    }

    /**
     * @return list<array{stage: string, count: string, conversion: string, drop_off: string}>
     */
    public function dropOffTable(CommercialConversionReportScope $scope): array
    {
        return collect($this->funnelStages($scope))
            ->map(fn (array $stage) => [
                'stage' => $stage['label'],
                'count' => (string) $stage['count'],
                'conversion' => $stage['conversion'] ?? '—',
                'drop_off' => $stage['drop_off'] ?? '—',
            ])
            ->all();
    }

    public function leadToQuotePercent(CommercialConversionReportScope $scope): ?float
    {
        return $this->stageConversionPercent($this->countLeads($scope), $this->countQuotations($scope));
    }

    public function quoteToOrderPercent(CommercialConversionReportScope $scope): ?float
    {
        return $this->stageConversionPercent($this->countQuotations($scope), $this->countOrders($scope));
    }

    public function orderToProductionPercent(CommercialConversionReportScope $scope): ?float
    {
        return $this->stageConversionPercent($this->countOrders($scope), $this->countProduction($scope));
    }

    public function productionToDispatchPercent(CommercialConversionReportScope $scope): ?float
    {
        return $this->stageConversionPercent($this->countProduction($scope), $this->countDispatch($scope));
    }

    public function dispatchToDeliveryPercent(CommercialConversionReportScope $scope): ?float
    {
        return $this->stageConversionPercent($this->countDispatch($scope), $this->countDelivered($scope));
    }

    public function totalFunnelDropOffPercent(CommercialConversionReportScope $scope): ?float
    {
        $leads = $this->countLeads($scope);
        $delivered = $this->countDelivered($scope);

        if ($leads === 0) {
            return 0.0;
        }

        return round((1 - ($delivered / $leads)) * 100, 1);
    }

    public function bestConvertingBranch(CommercialConversionReportScope $scope): string
    {
        $rows = $this->branchConversionRows($scope);
        if ($rows === []) {
            return '—';
        }

        $best = collect($rows)->sortByDesc(fn (array $row) => (float) str_replace('%', '', $row['quote_to_order']))->first();

        return $best['branch'] ?? '—';
    }

    public function bestConvertingSalesperson(CommercialConversionReportScope $scope): string
    {
        $rows = $this->salespersonConversionRows($scope);
        if ($rows === []) {
            return '—';
        }

        $best = collect($rows)->sortByDesc(fn (array $row) => (float) str_replace('%', '', $row['quote_to_order']))->first();

        return $best['salesperson'] ?? '—';
    }

    /**
     * @return list<array<string, string>>
     */
    public function branchConversionRows(CommercialConversionReportScope $scope): array
    {
        if (! $this->hasTable('branches')) {
            return [];
        }

        $branchIds = Branch::query()
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('id', $scope->branchId))
            ->where('is_active', true)
            ->pluck('id');

        if ($branchIds->isEmpty()) {
            return [];
        }

        $leads = $this->groupedLeadCounts($scope);
        $quotes = $this->groupedQuotationCounts($scope);
        $orders = $this->groupedOrderCounts($scope);
        $production = $this->groupedProductionCounts($scope);
        $dispatch = $this->groupedDispatchCounts($scope);
        $delivered = $this->groupedDeliveredCounts($scope);

        $names = Branch::query()->whereIn('id', $branchIds)->pluck('name', 'id');

        return $branchIds->map(function (int $branchId) use ($leads, $quotes, $orders, $production, $dispatch, $delivered, $names) {
            $leadCount = (int) ($leads[$branchId] ?? 0);
            $quoteCount = (int) ($quotes[$branchId] ?? 0);
            $orderCount = (int) ($orders[$branchId] ?? 0);

            return [
                'branch' => $names[$branchId] ?? '—',
                'leads' => (string) $leadCount,
                'quotes' => (string) $quoteCount,
                'orders' => (string) $orderCount,
                'production' => (string) ($production[$branchId] ?? 0),
                'dispatch' => (string) ($dispatch[$branchId] ?? 0),
                'delivered' => (string) ($delivered[$branchId] ?? 0),
                'lead_to_quote' => $this->formatPercent($this->stageConversionPercent($leadCount, $quoteCount)),
                'quote_to_order' => $this->formatPercent($this->stageConversionPercent($quoteCount, $orderCount)),
            ];
        })->sortByDesc(fn (array $row) => (float) str_replace('%', '', $row['quote_to_order']))->values()->all();
    }

    /**
     * @return list<array<string, string>>
     */
    public function salespersonConversionRows(CommercialConversionReportScope $scope): array
    {
        $leads = $this->groupedLeadCountsByAssignee($scope);
        $quotes = $this->groupedQuotationCountsByPreparer($scope);
        $orders = $this->groupedOrderCountsByCreator($scope);

        $userIds = collect(array_keys($leads))
            ->merge(array_keys($quotes))
            ->merge(array_keys($orders))
            ->unique()
            ->filter();

        if ($userIds->isEmpty()) {
            return [];
        }

        $names = User::query()->whereIn('id', $userIds)->pluck('name', 'id');

        return $userIds->map(function (int $userId) use ($leads, $quotes, $orders, $names) {
            $leadCount = (int) ($leads[$userId] ?? 0);
            $quoteCount = (int) ($quotes[$userId] ?? 0);
            $orderCount = (int) ($orders[$userId] ?? 0);

            return [
                'salesperson' => $names[$userId] ?? '—',
                'leads' => (string) $leadCount,
                'quotes' => (string) $quoteCount,
                'orders' => (string) $orderCount,
                'lead_to_quote' => $this->formatPercent($this->stageConversionPercent($leadCount, $quoteCount)),
                'quote_to_order' => $this->formatPercent($this->stageConversionPercent($quoteCount, $orderCount)),
            ];
        })->sortByDesc(fn (array $row) => (float) str_replace('%', '', $row['quote_to_order']))->values()->all();
    }

    public function countLeads(CommercialConversionReportScope $scope): int
    {
        if (! $this->hasTable('leads')) {
            return 0;
        }

        return (int) $this->leadQuery($scope)->count();
    }

    public function countQuotations(CommercialConversionReportScope $scope): int
    {
        if (! $this->hasTable('quotations')) {
            return 0;
        }

        return (int) $this->quotationQuery($scope)->count();
    }

    public function countOrders(CommercialConversionReportScope $scope): int
    {
        if (! $this->hasTable('sales_orders')) {
            return 0;
        }

        return (int) $this->orderQuery($scope)->count();
    }

    public function countProduction(CommercialConversionReportScope $scope): int
    {
        if (! $this->readiness->hasProductionPipeline()) {
            return 0;
        }

        return (int) $this->productionQuery($scope)->count();
    }

    public function countDispatch(CommercialConversionReportScope $scope): int
    {
        if (! $this->readiness->hasDispatchPipeline()) {
            return 0;
        }

        return (int) $this->dispatchQuery($scope)->count();
    }

    public function countDelivered(CommercialConversionReportScope $scope): int
    {
        if (! $this->readiness->hasDispatchPipeline()) {
            return 0;
        }

        return (int) $this->deliveredQuery($scope)->count();
    }

    protected function leadQuery(CommercialConversionReportScope $scope): Builder
    {
        $query = Lead::query()->where('leads.company_id', $scope->companyId);

        if ($scope->branchId !== null) {
            $query->where('leads.branch_id', $scope->branchId);
        }

        $query->whereDate('leads.created_at', '>=', $scope->fromDate)
            ->whereDate('leads.created_at', '<=', $scope->toDate);

        if ($scope->salespersonId !== null) {
            $query->where('leads.assigned_to', $scope->salespersonId);
        }

        if ($scope->leadSourceId !== null) {
            $query->where('leads.lead_source_id', $scope->leadSourceId);
        }

        if ($scope->status !== null && $scope->status !== '') {
            $query->where('leads.status', $scope->status);
        }

        if ($scope->customerType !== null) {
            $query->whereHas('customer', fn (Builder $customer) => $customer->where('customer_type', $scope->customerType));
        }

        if ($scope->search !== '') {
            $term = '%'.$scope->search.'%';
            $query->where(function (Builder $inner) use ($term) {
                $inner->where('leads.lead_name', 'like', $term)
                    ->orWhere('leads.company_name', 'like', $term);
            });
        }

        return $query;
    }

    protected function quotationQuery(CommercialConversionReportScope $scope): Builder
    {
        $query = Quotation::query()
            ->where('quotations.company_id', $scope->companyId)
            ->where('quotations.status', '!=', QuotationStatus::Draft);

        if ($scope->branchId !== null) {
            $query->where('quotations.branch_id', $scope->branchId);
        }

        $query->whereDate('quotations.quotation_date', '>=', $scope->fromDate)
            ->whereDate('quotations.quotation_date', '<=', $scope->toDate);

        if ($scope->salespersonId !== null) {
            $query->where('quotations.prepared_by', $scope->salespersonId);
        }

        $this->applyCustomerFilters($query, $scope, 'quotations.customer_id');

        if ($scope->search !== '') {
            $term = '%'.$scope->search.'%';
            $query->where(function (Builder $inner) use ($term) {
                $inner->where('quotations.quotation_number', 'like', $term)
                    ->orWhereHas('customer', fn (Builder $customer) => $customer->where('company_name', 'like', $term));
            });
        }

        return $query;
    }

    protected function orderQuery(CommercialConversionReportScope $scope): Builder
    {
        $query = SalesOrder::query()
            ->where('sales_orders.company_id', $scope->companyId)
            ->whereNotIn('sales_orders.status', [SalesOrderStatus::Draft, SalesOrderStatus::Cancelled]);

        if ($scope->branchId !== null) {
            $query->where('sales_orders.branch_id', $scope->branchId);
        }

        $query->whereDate('sales_orders.order_date', '>=', $scope->fromDate)
            ->whereDate('sales_orders.order_date', '<=', $scope->toDate);

        if ($scope->salespersonId !== null) {
            $query->where('sales_orders.created_by', $scope->salespersonId);
        }

        $this->applyCustomerFilters($query, $scope, 'sales_orders.customer_id');

        if ($scope->search !== '') {
            $term = '%'.$scope->search.'%';
            $query->where(function (Builder $inner) use ($term) {
                $inner->where('sales_orders.order_number', 'like', $term)
                    ->orWhereHas('customer', fn (Builder $customer) => $customer->where('company_name', 'like', $term));
            });
        }

        return $query;
    }

    protected function productionQuery(CommercialConversionReportScope $scope): Builder
    {
        $query = ProductionJobCard::query()
            ->where('production_job_cards.company_id', $scope->companyId)
            ->whereNotIn('production_job_cards.status', [
                ProductionJobCardStatus::Draft,
                ProductionJobCardStatus::Cancelled,
            ]);

        if ($scope->branchId !== null) {
            $query->where('production_job_cards.branch_id', $scope->branchId);
        }

        $query->whereDate('production_job_cards.created_at', '>=', $scope->fromDate)
            ->whereDate('production_job_cards.created_at', '<=', $scope->toDate);

        if ($scope->salespersonId !== null) {
            $query->where('production_job_cards.created_by', $scope->salespersonId);
        }

        $this->applyCustomerFilters($query, $scope, 'production_job_cards.customer_id');

        return $query;
    }

    protected function dispatchQuery(CommercialConversionReportScope $scope): Builder
    {
        $query = DeliveryNote::query()
            ->where('delivery_notes.company_id', $scope->companyId)
            ->whereIn('delivery_notes.status', [
                DeliveryNoteStatus::Dispatched,
                DeliveryNoteStatus::Delivered,
            ]);

        if ($scope->branchId !== null) {
            $query->where('delivery_notes.branch_id', $scope->branchId);
        }

        $query->whereDate('delivery_notes.delivery_date', '>=', $scope->fromDate)
            ->whereDate('delivery_notes.delivery_date', '<=', $scope->toDate);

        if ($scope->salespersonId !== null) {
            $query->where('delivery_notes.dispatched_by', $scope->salespersonId);
        }

        $this->applyCustomerFilters($query, $scope, 'delivery_notes.customer_id');

        return $query;
    }

    protected function deliveredQuery(CommercialConversionReportScope $scope): Builder
    {
        $query = DeliveryNote::query()
            ->where('delivery_notes.company_id', $scope->companyId)
            ->where('delivery_notes.status', DeliveryNoteStatus::Delivered);

        if ($scope->branchId !== null) {
            $query->where('delivery_notes.branch_id', $scope->branchId);
        }

        $query->whereDate('delivery_notes.delivery_date', '>=', $scope->fromDate)
            ->whereDate('delivery_notes.delivery_date', '<=', $scope->toDate);

        if ($scope->salespersonId !== null) {
            $query->where('delivery_notes.delivered_by', $scope->salespersonId);
        }

        $this->applyCustomerFilters($query, $scope, 'delivery_notes.customer_id');

        return $query;
    }

    protected function applyCustomerFilters(Builder $query, CommercialConversionReportScope $scope, string $customerColumn): void
    {
        if ($scope->customerType !== null && $this->hasTable('customers')) {
            $query->whereIn($customerColumn, function ($sub) use ($scope) {
                $sub->select('id')
                    ->from('customers')
                    ->where('company_id', $scope->companyId)
                    ->where('customer_type', $scope->customerType);
            });
        }

        if ($scope->leadSourceId !== null && $this->hasTable('leads')) {
            $query->whereIn($customerColumn, function ($sub) use ($scope) {
                $sub->select('customer_id')
                    ->from('leads')
                    ->where('company_id', $scope->companyId)
                    ->whereNotNull('customer_id')
                    ->where('lead_source_id', $scope->leadSourceId);
            });
        }

        if ($scope->status !== null && $scope->status !== '' && $this->hasTable('leads')) {
            $query->whereIn($customerColumn, function ($sub) use ($scope) {
                $sub->select('customer_id')
                    ->from('leads')
                    ->where('company_id', $scope->companyId)
                    ->whereNotNull('customer_id')
                    ->where('status', $scope->status);
            });
        }
    }

    /**
     * @return array<int, int>
     */
    protected function groupedLeadCounts(CommercialConversionReportScope $scope): array
    {
        return $this->leadQuery($scope)
            ->select('leads.branch_id', DB::raw('COUNT(*) as total'))
            ->groupBy('leads.branch_id')
            ->pluck('total', 'branch_id')
            ->map(fn ($value) => (int) $value)
            ->all();
    }

    /**
     * @return array<int, int>
     */
    protected function groupedQuotationCounts(CommercialConversionReportScope $scope): array
    {
        return $this->quotationQuery($scope)
            ->select('quotations.branch_id', DB::raw('COUNT(*) as total'))
            ->groupBy('quotations.branch_id')
            ->pluck('total', 'branch_id')
            ->map(fn ($value) => (int) $value)
            ->all();
    }

    /**
     * @return array<int, int>
     */
    protected function groupedOrderCounts(CommercialConversionReportScope $scope): array
    {
        return $this->orderQuery($scope)
            ->select('sales_orders.branch_id', DB::raw('COUNT(*) as total'))
            ->groupBy('sales_orders.branch_id')
            ->pluck('total', 'branch_id')
            ->map(fn ($value) => (int) $value)
            ->all();
    }

    /**
     * @return array<int, int>
     */
    protected function groupedProductionCounts(CommercialConversionReportScope $scope): array
    {
        if (! $this->readiness->hasProductionPipeline()) {
            return [];
        }

        return $this->productionQuery($scope)
            ->select('production_job_cards.branch_id', DB::raw('COUNT(*) as total'))
            ->groupBy('production_job_cards.branch_id')
            ->pluck('total', 'branch_id')
            ->map(fn ($value) => (int) $value)
            ->all();
    }

    /**
     * @return array<int, int>
     */
    protected function groupedDispatchCounts(CommercialConversionReportScope $scope): array
    {
        if (! $this->readiness->hasDispatchPipeline()) {
            return [];
        }

        return $this->dispatchQuery($scope)
            ->select('delivery_notes.branch_id', DB::raw('COUNT(*) as total'))
            ->groupBy('delivery_notes.branch_id')
            ->pluck('total', 'branch_id')
            ->map(fn ($value) => (int) $value)
            ->all();
    }

    /**
     * @return array<int, int>
     */
    protected function groupedDeliveredCounts(CommercialConversionReportScope $scope): array
    {
        if (! $this->readiness->hasDispatchPipeline()) {
            return [];
        }

        return $this->deliveredQuery($scope)
            ->select('delivery_notes.branch_id', DB::raw('COUNT(*) as total'))
            ->groupBy('delivery_notes.branch_id')
            ->pluck('total', 'branch_id')
            ->map(fn ($value) => (int) $value)
            ->all();
    }

    /**
     * @return array<int, int>
     */
    protected function groupedLeadCountsByAssignee(CommercialConversionReportScope $scope): array
    {
        return $this->leadQuery($scope)
            ->whereNotNull('leads.assigned_to')
            ->select('leads.assigned_to', DB::raw('COUNT(*) as total'))
            ->groupBy('leads.assigned_to')
            ->pluck('total', 'assigned_to')
            ->map(fn ($value) => (int) $value)
            ->all();
    }

    /**
     * @return array<int, int>
     */
    protected function groupedQuotationCountsByPreparer(CommercialConversionReportScope $scope): array
    {
        return $this->quotationQuery($scope)
            ->select('quotations.prepared_by', DB::raw('COUNT(*) as total'))
            ->groupBy('quotations.prepared_by')
            ->pluck('total', 'prepared_by')
            ->map(fn ($value) => (int) $value)
            ->all();
    }

    /**
     * @return array<int, int>
     */
    protected function groupedOrderCountsByCreator(CommercialConversionReportScope $scope): array
    {
        return $this->orderQuery($scope)
            ->select('sales_orders.created_by', DB::raw('COUNT(*) as total'))
            ->groupBy('sales_orders.created_by')
            ->pluck('total', 'created_by')
            ->map(fn ($value) => (int) $value)
            ->all();
    }

    protected function stageConversionPercent(int $from, int $to): ?float
    {
        if ($from === 0) {
            return $to > 0 ? 100.0 : 0.0;
        }

        return round(($to / $from) * 100, 1);
    }

    protected function formatPercent(?float $value): string
    {
        return $value !== null ? $value.'%' : '—';
    }
}
