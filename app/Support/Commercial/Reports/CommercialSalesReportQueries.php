<?php

namespace App\Support\Commercial\Reports;

use App\Enums\CustomerStatus;
use App\Enums\LeadStatus;
use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Branch;
use App\Models\Crm\Customer;
use App\Models\Crm\Lead;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CommercialSalesReportQueries
{
    public const PER_PAGE = 25;

    /**
     * @return list<string>
     */
    public function revenueExcludedStatuses(): array
    {
        return [SalesOrderStatus::Draft->value, SalesOrderStatus::Cancelled->value];
    }

    public function hasTable(string $table): bool
    {
        return Schema::hasTable($table);
    }

    public function money(float $amount): string
    {
        return 'KES '.number_format($amount, 0);
    }

    public function baseOrderQuery(CommercialSalesReportScope $scope): Builder
    {
        $query = SalesOrder::query()
            ->where('sales_orders.company_id', $scope->companyId);

        if ($scope->branchId !== null) {
            $query->where('sales_orders.branch_id', $scope->branchId);
        }

        $query->whereDate('sales_orders.order_date', '>=', $scope->fromDate)
            ->whereDate('sales_orders.order_date', '<=', $scope->toDate);

        if ($scope->customerId !== null) {
            $query->where('sales_orders.customer_id', $scope->customerId);
        }

        if ($scope->salespersonId !== null) {
            $query->where('sales_orders.created_by', $scope->salespersonId);
        }

        if ($scope->status !== null && $scope->status !== '') {
            $query->where('sales_orders.status', $scope->status);
        }

        if ($scope->search !== '') {
            $term = '%'.$scope->search.'%';
            $query->where(function (Builder $inner) use ($term) {
                $inner->where('sales_orders.order_number', 'like', $term)
                    ->orWhereHas('customer', fn (Builder $customer) => $customer->where('company_name', 'like', $term));
            });
        }

        return $query;
    }

    public function revenueOrderQuery(CommercialSalesReportScope $scope): Builder
    {
        return $this->baseOrderQuery($scope)
            ->whereNotIn('sales_orders.status', $this->revenueExcludedStatuses());
    }

    public function totalSales(CommercialSalesReportScope $scope): float
    {
        if (! $this->hasTable('sales_orders')) {
            return 0.0;
        }

        return (float) $this->revenueOrderQuery($scope)->sum('sales_orders.total_amount');
    }

    public function totalOrders(CommercialSalesReportScope $scope): int
    {
        if (! $this->hasTable('sales_orders')) {
            return 0;
        }

        return (int) $this->revenueOrderQuery($scope)->count();
    }

    public function averageOrderValue(CommercialSalesReportScope $scope): float
    {
        $orders = $this->totalOrders($scope);

        return $orders > 0 ? $this->totalSales($scope) / $orders : 0.0;
    }

    public function activeCustomers(CommercialSalesReportScope $scope): int
    {
        if (! $this->hasTable('sales_orders') || ! $this->hasTable('customers')) {
            return 0;
        }

        return (int) $this->revenueOrderQuery($scope)
            ->join('customers', 'customers.id', '=', 'sales_orders.customer_id')
            ->where('customers.status', CustomerStatus::Active)
            ->distinct('sales_orders.customer_id')
            ->count('sales_orders.customer_id');
    }

    public function salesGrowthPercent(CommercialSalesReportScope $scope): ?float
    {
        if (! $this->hasTable('sales_orders')) {
            return null;
        }

        $from = Carbon::parse($scope->fromDate);
        $to = Carbon::parse($scope->toDate);
        $days = max(1, $from->diffInDays($to) + 1);

        $previousFrom = $from->copy()->subDays($days)->toDateString();
        $previousTo = $from->copy()->subDay()->toDateString();

        $current = $this->totalSales($scope);

        $previousScope = new CommercialSalesReportScope(
            companyId: $scope->companyId,
            branchId: $scope->branchId,
            fromDate: $previousFrom,
            toDate: $previousTo,
            customerId: $scope->customerId,
            salespersonId: $scope->salespersonId,
            status: $scope->status,
            search: $scope->search,
        );

        $previous = $this->totalSales($previousScope);

        if ($previous <= 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    public function ordersAwaitingProduction(CommercialSalesReportScope $scope): int
    {
        if (! $this->hasTable('sales_orders')) {
            return 0;
        }

        return (int) $this->baseOrderQuery($scope)
            ->where('sales_orders.status', SalesOrderStatus::ReadyForProduction)
            ->count();
    }

    public function cancelledOrders(CommercialSalesReportScope $scope): int
    {
        if (! $this->hasTable('sales_orders')) {
            return 0;
        }

        return (int) $this->baseOrderQuery($scope)
            ->where('sales_orders.status', SalesOrderStatus::Cancelled)
            ->count();
    }

    public function salesForPeriod(CommercialSalesReportScope $scope, Carbon $from, Carbon $to): float
    {
        $periodScope = new CommercialSalesReportScope(
            companyId: $scope->companyId,
            branchId: $scope->branchId,
            fromDate: $from->toDateString(),
            toDate: $to->toDateString(),
            customerId: $scope->customerId,
            salespersonId: $scope->salespersonId,
            status: $scope->status,
            search: $scope->search,
        );

        return $this->totalSales($periodScope);
    }

    /**
     * @return array<string, mixed>
     */
    public function summaryMetrics(CommercialSalesReportScope $scope): array
    {
        $orders = $this->totalOrders($scope);
        $revenue = $this->totalSales($scope);

        return [
            'orders' => $orders,
            'revenue' => $revenue,
            'average_order_value' => $this->averageOrderValue($scope),
            'customer_count' => (int) $this->revenueOrderQuery($scope)->distinct('customer_id')->count('customer_id'),
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    public function branchBreakdown(CommercialSalesReportScope $scope): array
    {
        if (! $this->hasTable('sales_orders')) {
            return [];
        }

        $rows = $this->revenueOrderQuery($scope)
            ->select(
                'sales_orders.branch_id',
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(sales_orders.total_amount) as revenue'),
                DB::raw('COUNT(DISTINCT sales_orders.customer_id) as customers'),
            )
            ->groupBy('sales_orders.branch_id')
            ->orderByDesc('revenue')
            ->get();

        $names = Branch::query()->whereIn('id', $rows->pluck('branch_id'))->pluck('name', 'id');

        return $rows->map(function ($row) use ($names) {
            $orders = (int) $row->orders;
            $revenue = (float) $row->revenue;

            return [
                'branch' => $names[$row->branch_id] ?? '—',
                'orders' => (string) $orders,
                'revenue' => $this->money($revenue),
                'customers' => (string) $row->customers,
                'average_order' => $this->money($orders > 0 ? $revenue / $orders : 0),
            ];
        })->all();
    }

    /**
     * @return list<array<string, string>>
     */
    public function salespersonBreakdown(CommercialSalesReportScope $scope): array
    {
        if (! $this->hasTable('sales_orders')) {
            return [];
        }

        $rows = $this->revenueOrderQuery($scope)
            ->select(
                'sales_orders.created_by',
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(sales_orders.total_amount) as revenue'),
                DB::raw('COUNT(DISTINCT sales_orders.customer_id) as customers'),
            )
            ->groupBy('sales_orders.created_by')
            ->orderByDesc('revenue')
            ->get();

        $names = User::query()->whereIn('id', $rows->pluck('created_by'))->pluck('name', 'id');
        $conversion = $this->salespersonConversionRates($scope, $rows->pluck('created_by')->all());

        return $rows->map(function ($row) use ($names, $conversion) {
            $orders = (int) $row->orders;
            $revenue = (float) $row->revenue;
            $userId = (int) $row->created_by;

            return [
                'salesperson' => $names[$userId] ?? '—',
                'orders' => (string) $orders,
                'revenue' => $this->money($revenue),
                'customers' => (string) $row->customers,
                'average_order' => $this->money($orders > 0 ? $revenue / $orders : 0),
                'conversion' => ($conversion[$userId] ?? 0).'%',
            ];
        })->all();
    }

    /**
     * @param  list<int>  $userIds
     * @return array<int, float>
     */
    protected function salespersonConversionRates(CommercialSalesReportScope $scope, array $userIds): array
    {
        if (! $this->hasTable('quotations') || $userIds === []) {
            return [];
        }

        $quotes = Quotation::query()
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->whereDate('quotation_date', '>=', $scope->fromDate)
            ->whereDate('quotation_date', '<=', $scope->toDate)
            ->whereIn('prepared_by', $userIds)
            ->select('prepared_by', DB::raw('COUNT(*) as total'))
            ->groupBy('prepared_by')
            ->pluck('total', 'prepared_by');

        $converted = Quotation::query()
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->whereDate('quotation_date', '>=', $scope->fromDate)
            ->whereDate('quotation_date', '<=', $scope->toDate)
            ->whereIn('prepared_by', $userIds)
            ->where('status', QuotationStatus::Converted)
            ->select('prepared_by', DB::raw('COUNT(*) as total'))
            ->groupBy('prepared_by')
            ->pluck('total', 'prepared_by');

        $rates = [];
        foreach ($userIds as $userId) {
            $total = (int) ($quotes[$userId] ?? 0);
            $won = (int) ($converted[$userId] ?? 0);
            $rates[$userId] = $total > 0 ? round(($won / $total) * 100, 1) : 0.0;
        }

        return $rates;
    }

    public function paginateByDay(CommercialSalesReportScope $scope): LengthAwarePaginator
    {
        return $this->paginateGroupedPeriod($scope, 'day', 'sales_orders.order_date');
    }

    public function paginateByWeek(CommercialSalesReportScope $scope): LengthAwarePaginator
    {
        return $this->paginateGroupedPeriod($scope, 'week', DB::raw('YEARWEEK(sales_orders.order_date, 3)'));
    }

    public function paginateByMonth(CommercialSalesReportScope $scope): LengthAwarePaginator
    {
        return $this->paginateGroupedPeriod($scope, 'month', DB::raw("DATE_FORMAT(sales_orders.order_date, '%Y-%m')"));
    }

    public function paginateByCustomer(CommercialSalesReportScope $scope): LengthAwarePaginator
    {
        if (! $this->hasTable('sales_orders')) {
            return $this->emptyPaginator($scope);
        }

        $paginator = $this->revenueOrderQuery($scope)
            ->select(
                'sales_orders.customer_id',
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(sales_orders.total_amount) as revenue'),
                DB::raw('MAX(sales_orders.order_date) as last_order'),
                DB::raw('AVG(sales_orders.total_amount) as average_order'),
            )
            ->groupBy('sales_orders.customer_id')
            ->orderByDesc('revenue')
            ->paginate(self::PER_PAGE, ['*'], 'page', $scope->page);

        $customerIds = $paginator->getCollection()->pluck('customer_id');
        $names = Customer::query()->whereIn('id', $customerIds)->pluck('company_name', 'id');
        $lifetime = $this->customerLifetimeValues($scope, $customerIds->all());

        return $paginator->through(function ($row) use ($names, $lifetime) {
            return [
                'customer' => $names[$row->customer_id] ?? '—',
                'orders' => (string) $row->orders,
                'revenue' => $this->money((float) $row->revenue),
                'last_order' => $row->last_order ? Carbon::parse($row->last_order)->format('d M Y') : '—',
                'average_order' => $this->money((float) $row->average_order),
                'lifetime_value' => $this->money((float) ($lifetime[$row->customer_id] ?? 0)),
            ];
        });
    }

    public function paginateByBranch(CommercialSalesReportScope $scope): LengthAwarePaginator
    {
        $rows = collect($this->branchBreakdown($scope));

        return $this->paginateCollection($rows, $scope->page);
    }

    public function paginateBySalesperson(CommercialSalesReportScope $scope): LengthAwarePaginator
    {
        $rows = collect($this->salespersonBreakdown($scope));

        return $this->paginateCollection($rows, $scope->page);
    }

    public function topCustomers(CommercialSalesReportScope $scope): Collection
    {
        if (! $this->hasTable('sales_orders')) {
            return collect();
        }

        $orderColumn = match ($scope->topBy) {
            'orders' => 'orders',
            'lifetime' => 'lifetime_value',
            default => 'revenue',
        };

        if ($scope->topBy === 'lifetime') {
            $rows = SalesOrder::query()
                ->where('sales_orders.company_id', $scope->companyId)
                ->when($scope->branchId, fn ($q) => $q->where('sales_orders.branch_id', $scope->branchId))
                ->whereNotIn('sales_orders.status', $this->revenueExcludedStatuses())
                ->select(
                    'sales_orders.customer_id',
                    DB::raw('COUNT(*) as orders'),
                    DB::raw('SUM(sales_orders.total_amount) as lifetime_value'),
                )
                ->groupBy('sales_orders.customer_id')
                ->orderByDesc('lifetime_value')
                ->limit($scope->topLimit)
                ->get();
        } else {
            $rows = $this->revenueOrderQuery($scope)
                ->select(
                    'sales_orders.customer_id',
                    DB::raw('COUNT(*) as orders'),
                    DB::raw('SUM(sales_orders.total_amount) as revenue'),
                )
                ->groupBy('sales_orders.customer_id')
                ->orderByDesc($orderColumn)
                ->limit($scope->topLimit)
                ->get();
        }

        $names = Customer::query()->whereIn('id', $rows->pluck('customer_id'))->pluck('company_name', 'id');
        $lifetime = $this->customerLifetimeValues($scope, $rows->pluck('customer_id')->all());

        return $rows->map(function ($row) use ($names, $lifetime, $scope) {
            $revenue = (float) ($row->revenue ?? $row->lifetime_value ?? 0);

            return [
                'customer' => $names[$row->customer_id] ?? '—',
                'orders' => (string) $row->orders,
                'revenue' => $this->money($revenue),
                'lifetime_value' => $this->money((float) ($lifetime[$row->customer_id] ?? $revenue)),
            ];
        });
    }

    /**
     * @return array{cancelled: list<array<string, string>>, expired: list<array<string, string>>, lost: list<array<string, string>>, reasons: list<array<string, string>>}
     */
    public function lostOrders(CommercialSalesReportScope $scope): array
    {
        $cancelled = [];
        $expired = [];
        $lost = [];
        $reasons = [];

        if ($this->hasTable('sales_orders')) {
            $cancelledRows = $this->baseOrderQuery($scope)
                ->where('sales_orders.status', SalesOrderStatus::Cancelled)
                ->join('customers', 'customers.id', '=', 'sales_orders.customer_id')
                ->select('sales_orders.order_number', 'customers.company_name', 'sales_orders.total_amount', 'sales_orders.order_date')
                ->orderByDesc('sales_orders.order_date')
                ->limit(50)
                ->get();

            $cancelled = $cancelledRows->map(fn ($row) => [
                'reference' => $row->order_number,
                'customer' => $row->company_name,
                'value' => $this->money((float) $row->total_amount),
                'date' => Carbon::parse($row->order_date)->format('d M Y'),
                'reason' => __('Order cancelled'),
            ])->all();

            $reasons[] = ['reason' => __('Cancelled orders'), 'count' => (string) count($cancelled)];
        }

        if ($this->hasTable('quotations')) {
            $expiredRows = Quotation::query()
                ->where('company_id', $scope->companyId)
                ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
                ->whereDate('quotation_date', '>=', $scope->fromDate)
                ->whereDate('quotation_date', '<=', $scope->toDate)
                ->where(function ($q) {
                    $q->where('status', QuotationStatus::Expired)
                        ->orWhere(function ($inner) {
                            $inner->whereNotIn('status', [QuotationStatus::Converted, QuotationStatus::Accepted])
                                ->whereDate('valid_until', '<', now()->toDateString());
                        });
                })
                ->join('customers', 'customers.id', '=', 'quotations.customer_id')
                ->select('quotations.quotation_number', 'customers.company_name', 'quotations.total_amount', 'quotations.valid_until')
                ->orderByDesc('quotations.valid_until')
                ->limit(50)
                ->get();

            $expired = $expiredRows->map(fn ($row) => [
                'reference' => $row->quotation_number,
                'customer' => $row->company_name,
                'value' => $this->money((float) $row->total_amount),
                'date' => $row->valid_until ? Carbon::parse($row->valid_until)->format('d M Y') : '—',
                'reason' => __('Quotation expired'),
            ])->all();

            $reasons[] = ['reason' => __('Expired quotations'), 'count' => (string) count($expired)];
        }

        if ($this->hasTable('leads')) {
            $lostRows = Lead::query()
                ->where('company_id', $scope->companyId)
                ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
                ->whereDate('updated_at', '>=', $scope->fromDate)
                ->whereDate('updated_at', '<=', $scope->toDate)
                ->where('status', LeadStatus::Lost)
                ->select('lead_name', 'company_name', 'estimated_value', 'updated_at', 'notes')
                ->orderByDesc('updated_at')
                ->limit(50)
                ->get();

            $lost = $lostRows->map(fn ($row) => [
                'reference' => $row->lead_name ?? '—',
                'customer' => $row->company_name ?? '—',
                'value' => $this->money((float) ($row->estimated_value ?? 0)),
                'date' => Carbon::parse($row->updated_at)->format('d M Y'),
                'reason' => $row->notes ?: __('Lost opportunity'),
            ])->all();

            $reasons[] = ['reason' => __('Lost leads'), 'count' => (string) count($lost)];
        }

        return compact('cancelled', 'expired', 'lost', 'reasons');
    }

    /**
     * @return array{daily: list<array<string, mixed>>, weekly: list<array<string, mixed>>, monthly: list<array<string, mixed>>, quarterly: list<array<string, mixed>>, yearly: list<array<string, mixed>>}
     */
    public function trendSeries(CommercialSalesReportScope $scope): array
    {
        if (! $this->hasTable('sales_orders')) {
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

    /**
     * @return list<array{label: string, orders: int, revenue: float}>
     */
    protected function trendBuckets(CommercialSalesReportScope $scope, string $granularity, int $limit): array
    {
        $group = match ($granularity) {
            'week' => DB::raw('YEARWEEK(sales_orders.order_date, 3)'),
            'month' => DB::raw("DATE_FORMAT(sales_orders.order_date, '%Y-%m')"),
            'quarter' => DB::raw("CONCAT(YEAR(sales_orders.order_date), '-Q', QUARTER(sales_orders.order_date))"),
            'year' => DB::raw('YEAR(sales_orders.order_date)'),
            default => DB::raw('DATE(sales_orders.order_date)'),
        };

        $rows = $this->revenueOrderQuery($scope)
            ->select(
                $group.' as bucket',
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(sales_orders.total_amount) as revenue'),
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
            'revenue' => (float) $row->revenue,
        ])->all();
    }

    /**
     * @return array<int, float>
     */
    protected function customerLifetimeValues(CommercialSalesReportScope $scope, array $customerIds): array
    {
        if ($customerIds === [] || ! $this->hasTable('sales_orders')) {
            return [];
        }

        return SalesOrder::query()
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->whereIn('customer_id', $customerIds)
            ->whereNotIn('status', $this->revenueExcludedStatuses())
            ->select('customer_id', DB::raw('SUM(total_amount) as lifetime_value'))
            ->groupBy('customer_id')
            ->pluck('lifetime_value', 'customer_id')
            ->map(fn ($value) => (float) $value)
            ->all();
    }

    protected function paginateGroupedPeriod(CommercialSalesReportScope $scope, string $period, mixed $groupExpression): LengthAwarePaginator
    {
        if (! $this->hasTable('sales_orders')) {
            return $this->emptyPaginator($scope);
        }

        $sub = $this->revenueOrderQuery($scope)
            ->select(
                $groupExpression.' as period_key',
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(sales_orders.total_amount) as revenue'),
                DB::raw('COUNT(DISTINCT sales_orders.customer_id) as customers'),
            )
            ->groupBy('period_key');

        $rows = DB::query()
            ->fromSub($sub, 'grouped_sales')
            ->orderByDesc('period_key')
            ->paginate(self::PER_PAGE, ['*'], 'page', $scope->page);

        $collection = $rows->getCollection();
        $previousRevenue = null;

        $mapped = $collection->map(function ($row) use ($period, &$previousRevenue) {
            $orders = (int) $row->orders;
            $revenue = (float) $row->revenue;
            $growth = null;

            if ($previousRevenue !== null && $previousRevenue > 0) {
                $growth = round((($revenue - $previousRevenue) / $previousRevenue) * 100, 1).'%';
            } elseif ($previousRevenue === 0.0 && $revenue > 0) {
                $growth = '100%';
            } else {
                $growth = '0%';
            }

            $previousRevenue = $revenue;

            $label = $this->formatPeriodLabel($period, (string) $row->period_key);

            return [
                'period' => $label,
                'orders' => (string) $orders,
                'revenue' => $this->money($revenue),
                'customers' => (string) ($row->customers ?? 0),
                'average_order_value' => $this->money($orders > 0 ? $revenue / $orders : 0),
                'growth' => $growth,
                'trend' => $growth,
            ];
        });

        return new Paginator(
            $mapped,
            $rows->total(),
            $rows->perPage(),
            $rows->currentPage(),
            ['path' => request()->url(), 'query' => request()->query()],
        );
    }

    protected function formatPeriodLabel(string $period, string $key): string
    {
        return match ($period) {
            'week' => __('Week :week', ['week' => $key]),
            'month' => Carbon::createFromFormat('Y-m', $key)->format('M Y'),
            default => Carbon::parse($key)->format('d M Y'),
        };
    }

    protected function paginateCollection(Collection $rows, int $page): LengthAwarePaginator
    {
        $items = $rows->forPage($page, self::PER_PAGE)->values();
        $total = $rows->count();

        return new Paginator(
            $items,
            $total,
            self::PER_PAGE,
            $page,
            ['path' => request()->url(), 'query' => request()->query()],
        );
    }

    protected function emptyPaginator(CommercialSalesReportScope $scope): LengthAwarePaginator
    {
        return new Paginator([], 0, self::PER_PAGE, $scope->page, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);
    }
}
