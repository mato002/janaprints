<?php

namespace App\Support\Commercial\Reports;

use App\Enums\CustomerStatus;
use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Branch;
use App\Models\Crm\Customer;
use App\Models\Crm\CustomerActivity;
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

class CommercialCustomerReportQueries
{
    public const PER_PAGE = 25;

    /**
     * @return list<string>
     */
    public function revenueExcludedStatuses(): array
    {
        return [SalesOrderStatus::Draft->value, SalesOrderStatus::Cancelled->value];
    }

    /**
     * @return list<string>
     */
    public function openQuotationStatuses(): array
    {
        return [
            QuotationStatus::Draft->value,
            QuotationStatus::PendingApproval->value,
            QuotationStatus::Sent->value,
            QuotationStatus::Viewed->value,
            QuotationStatus::Accepted->value,
        ];
    }

    /**
     * @return list<string>
     */
    public function openOrderStatuses(): array
    {
        return [
            SalesOrderStatus::Confirmed->value,
            SalesOrderStatus::ReadyForProduction->value,
            SalesOrderStatus::InProduction->value,
            SalesOrderStatus::Completed->value,
            SalesOrderStatus::OnHold->value,
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

    public function baseCustomerQuery(CommercialCustomerReportScope $scope): Builder
    {
        $query = Customer::query()
            ->where('customers.company_id', $scope->companyId);

        if ($scope->branchId !== null) {
            $query->where('customers.branch_id', $scope->branchId);
        }

        if ($scope->customerType !== null && $scope->customerType !== '') {
            $query->where('customers.customer_type', $scope->customerType);
        }

        if ($scope->status !== null && $scope->status !== '') {
            $query->where('customers.status', $scope->status);
        }

        if ($scope->search !== '') {
            $term = '%'.$scope->search.'%';
            $query->where(function (Builder $inner) use ($term) {
                $inner->where('customers.company_name', 'like', $term)
                    ->orWhere('customers.customer_code', 'like', $term)
                    ->orWhere('customers.contact_person', 'like', $term)
                    ->orWhere('customers.email', 'like', $term);
            });
        }

        if ($scope->salespersonId !== null && $this->hasTable('sales_orders')) {
            $query->whereExists(function ($sub) use ($scope) {
                $sub->select(DB::raw(1))
                    ->from('sales_orders')
                    ->whereColumn('sales_orders.customer_id', 'customers.id')
                    ->where('sales_orders.company_id', $scope->companyId)
                    ->when($scope->branchId, fn ($q) => $q->where('sales_orders.branch_id', $scope->branchId))
                    ->where('sales_orders.created_by', $scope->salespersonId)
                    ->whereDate('sales_orders.order_date', '>=', $scope->fromDate)
                    ->whereDate('sales_orders.order_date', '<=', $scope->toDate);
            });
        }

        $this->applyActivityStatusFilter($query, $scope);

        return $query;
    }

    protected function applyActivityStatusFilter(Builder $query, CommercialCustomerReportScope $scope): void
    {
        if ($scope->activityStatus === null || ! $this->hasTable('sales_orders')) {
            return;
        }

        match ($scope->activityStatus) {
            'new' => $query
                ->whereDate('customers.created_at', '>=', $scope->fromDate)
                ->whereDate('customers.created_at', '<=', $scope->toDate),
            'active' => $query->whereExists(fn ($sub) => $this->revenueOrderExistsSubquery($sub, $scope)),
            'inactive' => $query->whereNotExists(fn ($sub) => $this->revenueOrderExistsSubquery($sub, $scope)),
            'dormant' => $query->whereNotExists(function ($sub) use ($scope) {
                $cutoff = Carbon::parse($scope->toDate)->subDays($scope->dormantDays)->toDateString();
                $sub->select(DB::raw(1))
                    ->from('sales_orders')
                    ->whereColumn('sales_orders.customer_id', 'customers.id')
                    ->where('sales_orders.company_id', $scope->companyId)
                    ->when($scope->branchId, fn ($q) => $q->where('sales_orders.branch_id', $scope->branchId))
                    ->whereNotIn('sales_orders.status', $this->revenueExcludedStatuses())
                    ->whereDate('sales_orders.order_date', '>=', $cutoff)
                    ->whereDate('sales_orders.order_date', '<=', $scope->toDate);
            }),
            default => null,
        };
    }

    protected function revenueOrderExistsSubquery($sub, CommercialCustomerReportScope $scope): void
    {
        $sub->select(DB::raw(1))
            ->from('sales_orders')
            ->whereColumn('sales_orders.customer_id', 'customers.id')
            ->where('sales_orders.company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('sales_orders.branch_id', $scope->branchId))
            ->whereNotIn('sales_orders.status', $this->revenueExcludedStatuses())
            ->whereDate('sales_orders.order_date', '>=', $scope->fromDate)
            ->whereDate('sales_orders.order_date', '<=', $scope->toDate);
    }

    public function totalCustomers(CommercialCustomerReportScope $scope): int
    {
        if (! $this->hasTable('customers')) {
            return 0;
        }

        return (int) $this->baseCustomerQuery($scope)->count();
    }

    public function newCustomers(CommercialCustomerReportScope $scope): int
    {
        if (! $this->hasTable('customers')) {
            return 0;
        }

        return (int) Customer::query()
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->when($scope->customerType, fn ($q) => $q->where('customer_type', $scope->customerType))
            ->when($scope->status, fn ($q) => $q->where('status', $scope->status))
            ->whereDate('created_at', '>=', $scope->fromDate)
            ->whereDate('created_at', '<=', $scope->toDate)
            ->count();
    }

    public function activeStatusCustomers(CommercialCustomerReportScope $scope): int
    {
        if (! $this->hasTable('customers')) {
            return 0;
        }

        return (int) $this->baseCustomerQuery($scope)
            ->where('customers.status', CustomerStatus::Active)
            ->count();
    }

    public function inactiveStatusCustomers(CommercialCustomerReportScope $scope): int
    {
        if (! $this->hasTable('customers')) {
            return 0;
        }

        return (int) $this->baseCustomerQuery($scope)
            ->where('customers.status', CustomerStatus::Inactive)
            ->count();
    }

    public function repeatCustomers(CommercialCustomerReportScope $scope): int
    {
        if (! $this->hasTable('sales_orders')) {
            return 0;
        }

        return (int) SalesOrder::query()
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->whereNotIn('status', $this->revenueExcludedStatuses())
            ->whereDate('order_date', '>=', $scope->fromDate)
            ->whereDate('order_date', '<=', $scope->toDate)
            ->select('customer_id')
            ->groupBy('customer_id')
            ->havingRaw('COUNT(*) >= 2')
            ->get()
            ->count();
    }

    public function customerGrowthPercent(CommercialCustomerReportScope $scope): ?float
    {
        if (! $this->hasTable('customers')) {
            return null;
        }

        $from = Carbon::parse($scope->fromDate);
        $to = Carbon::parse($scope->toDate);
        $days = max(1, $from->diffInDays($to) + 1);

        $previousFrom = $from->copy()->subDays($days)->toDateString();
        $previousTo = $from->copy()->subDay()->toDateString();

        $current = $this->newCustomers($scope);

        $previous = (int) Customer::query()
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->whereDate('created_at', '>=', $previousFrom)
            ->whereDate('created_at', '<=', $previousTo)
            ->count();

        if ($previous <= 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    public function averageCustomerValue(CommercialCustomerReportScope $scope): float
    {
        if (! $this->hasTable('sales_orders')) {
            return 0.0;
        }

        $revenue = (float) SalesOrder::query()
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->whereNotIn('status', $this->revenueExcludedStatuses())
            ->whereDate('order_date', '>=', $scope->fromDate)
            ->whereDate('order_date', '<=', $scope->toDate)
            ->sum('total_amount');

        $customers = (int) SalesOrder::query()
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->whereNotIn('status', $this->revenueExcludedStatuses())
            ->whereDate('order_date', '>=', $scope->fromDate)
            ->whereDate('order_date', '<=', $scope->toDate)
            ->distinct('customer_id')
            ->count('customer_id');

        return $customers > 0 ? $revenue / $customers : 0.0;
    }

    public function topCustomerRevenue(CommercialCustomerReportScope $scope): float
    {
        if (! $this->hasTable('sales_orders')) {
            return 0.0;
        }

        return (float) SalesOrder::query()
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->whereNotIn('status', $this->revenueExcludedStatuses())
            ->whereDate('order_date', '>=', $scope->fromDate)
            ->whereDate('order_date', '<=', $scope->toDate)
            ->select('customer_id', DB::raw('SUM(total_amount) as revenue'))
            ->groupBy('customer_id')
            ->orderByDesc('revenue')
            ->limit(1)
            ->value('revenue') ?? 0.0;
    }

    public function customersWithOpenQuotes(CommercialCustomerReportScope $scope): int
    {
        if (! $this->hasTable('quotations')) {
            return 0;
        }

        return (int) Quotation::query()
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->whereIn('status', $this->openQuotationStatuses())
            ->distinct('customer_id')
            ->count('customer_id');
    }

    public function customersWithOpenOrders(CommercialCustomerReportScope $scope): int
    {
        if (! $this->hasTable('sales_orders')) {
            return 0;
        }

        return (int) SalesOrder::query()
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->whereIn('status', $this->openOrderStatuses())
            ->distinct('customer_id')
            ->count('customer_id');
    }

    /**
     * @return array<string, mixed>
     */
    public function summaryMetrics(CommercialCustomerReportScope $scope): array
    {
        return [
            'total' => $this->totalCustomers($scope),
            'new' => $this->newCustomers($scope),
            'active' => $this->activeStatusCustomers($scope),
            'inactive' => $this->inactiveStatusCustomers($scope),
            'repeat' => $this->repeatCustomers($scope),
            'growth' => $this->customerGrowthPercent($scope),
            'average_value' => $this->averageCustomerValue($scope),
            'open_quotes' => $this->customersWithOpenQuotes($scope),
            'open_orders' => $this->customersWithOpenOrders($scope),
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    public function branchBreakdown(CommercialCustomerReportScope $scope): array
    {
        if (! $this->hasTable('customers')) {
            return [];
        }

        $rows = $this->baseCustomerQuery($scope)
            ->select(
                'customers.branch_id',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN customers.status = 'active' THEN 1 ELSE 0 END) as active_count"),
                DB::raw("SUM(CASE WHEN customers.status = 'inactive' THEN 1 ELSE 0 END) as inactive_count"),
            )
            ->groupBy('customers.branch_id')
            ->orderByDesc('total')
            ->get();

        $names = Branch::query()->whereIn('id', $rows->pluck('branch_id'))->pluck('name', 'id');
        $revenue = $this->branchRevenueMap($scope, $rows->pluck('branch_id')->all());

        return $rows->map(function ($row) use ($names, $revenue) {
            return [
                'branch' => $names[$row->branch_id] ?? '—',
                'customers' => (string) $row->total,
                'active' => (string) $row->active_count,
                'inactive' => (string) $row->inactive_count,
                'revenue' => $this->money((float) ($revenue[$row->branch_id] ?? 0)),
            ];
        })->all();
    }

    /**
     * @param  list<int>  $branchIds
     * @return array<int, float>
     */
    protected function branchRevenueMap(CommercialCustomerReportScope $scope, array $branchIds): array
    {
        if ($branchIds === [] || ! $this->hasTable('sales_orders')) {
            return [];
        }

        return SalesOrder::query()
            ->where('company_id', $scope->companyId)
            ->whereIn('branch_id', $branchIds)
            ->whereNotIn('status', $this->revenueExcludedStatuses())
            ->whereDate('order_date', '>=', $scope->fromDate)
            ->whereDate('order_date', '<=', $scope->toDate)
            ->select('branch_id', DB::raw('SUM(total_amount) as revenue'))
            ->groupBy('branch_id')
            ->pluck('revenue', 'branch_id')
            ->map(fn ($value) => (float) $value)
            ->all();
    }

    /**
     * @return list<array<string, string>>
     */
    public function salespersonBreakdown(CommercialCustomerReportScope $scope): array
    {
        if (! $this->hasTable('sales_orders')) {
            return [];
        }

        $rows = SalesOrder::query()
            ->where('sales_orders.company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('sales_orders.branch_id', $scope->branchId))
            ->whereNotIn('sales_orders.status', $this->revenueExcludedStatuses())
            ->whereDate('sales_orders.order_date', '>=', $scope->fromDate)
            ->whereDate('sales_orders.order_date', '<=', $scope->toDate)
            ->select(
                'sales_orders.created_by',
                DB::raw('COUNT(DISTINCT sales_orders.customer_id) as customers'),
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(sales_orders.total_amount) as revenue'),
            )
            ->groupBy('sales_orders.created_by')
            ->orderByDesc('revenue')
            ->get();

        $names = User::query()->whereIn('id', $rows->pluck('created_by'))->pluck('name', 'id');

        return $rows->map(function ($row) use ($names) {
            $customers = (int) $row->customers;
            $revenue = (float) $row->revenue;

            return [
                'salesperson' => $names[$row->created_by] ?? '—',
                'customers' => (string) $customers,
                'orders' => (string) $row->orders,
                'revenue' => $this->money($revenue),
                'average_value' => $this->money($customers > 0 ? $revenue / $customers : 0),
            ];
        })->all();
    }

    public function paginateCustomerList(
        CommercialCustomerReportScope $scope,
        ?string $statusFilter = null,
        ?bool $newOnly = false,
    ): LengthAwarePaginator {
        if (! $this->hasTable('customers')) {
            return $this->emptyPaginator($scope);
        }

        $query = $this->baseCustomerQuery($scope);

        if ($statusFilter !== null) {
            $query->where('customers.status', $statusFilter);
        }

        if ($newOnly) {
            $query->whereDate('customers.created_at', '>=', $scope->fromDate)
                ->whereDate('customers.created_at', '<=', $scope->toDate);
        }

        $orderStats = $this->orderStatsSubquery($scope);
        $quoteStats = $this->quoteStatsSubquery($scope);

        $paginator = $query
            ->leftJoinSub($orderStats, 'order_stats', 'order_stats.customer_id', '=', 'customers.id')
            ->leftJoinSub($quoteStats, 'quote_stats', 'quote_stats.customer_id', '=', 'customers.id')
            ->select(
                'customers.id',
                'customers.company_name',
                'customers.customer_code',
                'customers.customer_type',
                'customers.status',
                'customers.created_at',
                DB::raw('COALESCE(order_stats.orders, 0) as orders'),
                DB::raw('COALESCE(order_stats.revenue, 0) as revenue'),
                DB::raw('order_stats.last_order as last_order'),
                DB::raw('COALESCE(quote_stats.open_quotes, 0) as open_quotes'),
            )
            ->orderByDesc('customers.created_at')
            ->paginate(self::PER_PAGE, ['*'], 'page', $scope->page);

        return $paginator->through(function ($row) use ($newOnly) {
            $base = [
                'customer' => $row->company_name,
                'code' => $row->customer_code,
                'type' => $this->displayLabel($row->customer_type),
                'status' => $this->displayLabel($row->status),
                'orders' => (string) $row->orders,
                'revenue' => $this->money((float) $row->revenue),
            ];

            if ($newOnly) {
                return array_merge($base, [
                    'open_quotes' => (string) $row->open_quotes,
                    'created' => Carbon::parse($row->created_at)->format('d M Y'),
                ]);
            }

            return array_merge($base, [
                'last_order' => $row->last_order ? Carbon::parse($row->last_order)->format('d M Y') : '—',
                'open_quotes' => (string) $row->open_quotes,
            ]);
        });
    }

    public function paginateRevenue(CommercialCustomerReportScope $scope): LengthAwarePaginator
    {
        return $this->paginateRankedCustomers($scope, 'revenue');
    }

    public function paginateLifetimeValue(CommercialCustomerReportScope $scope): LengthAwarePaginator
    {
        return $this->paginateRankedCustomers($scope, 'lifetime');
    }

    public function topCustomers(CommercialCustomerReportScope $scope): Collection
    {
        if (! $this->hasTable('sales_orders')) {
            return collect();
        }

        $rows = SalesOrder::query()
            ->where('sales_orders.company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('sales_orders.branch_id', $scope->branchId))
            ->whereNotIn('sales_orders.status', $this->revenueExcludedStatuses())
            ->whereDate('sales_orders.order_date', '>=', $scope->fromDate)
            ->whereDate('sales_orders.order_date', '<=', $scope->toDate)
            ->select(
                'sales_orders.customer_id',
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(sales_orders.total_amount) as revenue'),
            )
            ->groupBy('sales_orders.customer_id')
            ->orderByDesc('revenue')
            ->limit($scope->topLimit)
            ->get();

        $names = Customer::query()->whereIn('id', $rows->pluck('customer_id'))->pluck('company_name', 'id');
        $lifetime = $this->customerLifetimeValues($scope, $rows->pluck('customer_id')->all());

        return $rows->map(function ($row) use ($names, $lifetime) {
            return [
                'customer' => $names[$row->customer_id] ?? '—',
                'orders' => (string) $row->orders,
                'revenue' => $this->money((float) $row->revenue),
                'lifetime_value' => $this->money((float) ($lifetime[$row->customer_id] ?? $row->revenue)),
            ];
        });
    }

    public function paginateGrowth(CommercialCustomerReportScope $scope): LengthAwarePaginator
    {
        if (! $this->hasTable('customers')) {
            return $this->emptyPaginator($scope);
        }

        $sub = Customer::query()
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->whereDate('created_at', '>=', $scope->fromDate)
            ->whereDate('created_at', '<=', $scope->toDate)
            ->selectRaw($this->monthPeriodSql('created_at').' as period_key, COUNT(*) as new_customers')
            ->groupBy('period_key');

        $rows = DB::query()
            ->fromSub($sub, 'growth')
            ->orderByDesc('period_key')
            ->paginate(self::PER_PAGE, ['*'], 'page', $scope->page);

        $collection = $rows->getCollection();
        $previous = null;

        $mapped = $collection->map(function ($row) use (&$previous) {
            $count = (int) $row->new_customers;
            $growth = '0%';

            if ($previous !== null && $previous > 0) {
                $growth = round((($count - $previous) / $previous) * 100, 1).'%';
            } elseif ($previous === 0 && $count > 0) {
                $growth = '100%';
            }

            $previous = $count;
            $label = Carbon::createFromFormat('Y-m', (string) $row->period_key)->format('M Y');

            return [
                'period' => $label,
                'new_customers' => (string) $count,
                'growth' => $growth,
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

    public function paginateWithoutRecentOrders(CommercialCustomerReportScope $scope): LengthAwarePaginator
    {
        if (! $this->hasTable('customers') || ! $this->hasTable('sales_orders')) {
            return $this->emptyPaginator($scope);
        }

        $cutoff = Carbon::parse($scope->toDate)->subDays($scope->dormantDays)->toDateString();

        $query = $this->baseCustomerQuery($scope)
            ->whereNotExists(function ($sub) use ($scope, $cutoff) {
                $sub->select(DB::raw(1))
                    ->from('sales_orders')
                    ->whereColumn('sales_orders.customer_id', 'customers.id')
                    ->where('sales_orders.company_id', $scope->companyId)
                    ->when($scope->branchId, fn ($q) => $q->where('sales_orders.branch_id', $scope->branchId))
                    ->whereNotIn('sales_orders.status', $this->revenueExcludedStatuses())
                    ->whereDate('sales_orders.order_date', '>=', $cutoff);
            });

        $lastOrderSub = SalesOrder::query()
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->whereNotIn('status', $this->revenueExcludedStatuses())
            ->select('customer_id', DB::raw('MAX(order_date) as last_order'))
            ->groupBy('customer_id');

        $paginator = $query
            ->leftJoinSub($lastOrderSub, 'last_orders', 'last_orders.customer_id', '=', 'customers.id')
            ->select(
                'customers.company_name',
                'customers.customer_code',
                'customers.status',
                'last_orders.last_order',
            )
            ->orderBy('last_orders.last_order')
            ->paginate(self::PER_PAGE, ['*'], 'page', $scope->page);

        return $paginator->through(function ($row) use ($scope) {
            return [
                'customer' => $row->company_name,
                'code' => $row->customer_code,
                'status' => $this->displayLabel($row->status),
                'last_order' => $row->last_order ? Carbon::parse($row->last_order)->format('d M Y') : '—',
                'days_inactive' => $row->last_order
                    ? (string) Carbon::parse($row->last_order)->diffInDays(Carbon::parse($scope->toDate))
                    : __('Never ordered'),
            ];
        });
    }

    public function paginateByBranch(CommercialCustomerReportScope $scope): LengthAwarePaginator
    {
        return $this->paginateCollection(collect($this->branchBreakdown($scope)), $scope->page);
    }

    public function paginateBySalesperson(CommercialCustomerReportScope $scope): LengthAwarePaginator
    {
        return $this->paginateCollection(collect($this->salespersonBreakdown($scope)), $scope->page);
    }

    public function paginateActivity(CommercialCustomerReportScope $scope): LengthAwarePaginator
    {
        if (! $this->hasTable('customer_activities')) {
            return $this->paginateOrderActivity($scope);
        }

        $query = CustomerActivity::query()
            ->where('customer_activities.company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('customer_activities.branch_id', $scope->branchId))
            ->whereDate('customer_activities.activity_at', '>=', $scope->fromDate)
            ->whereDate('customer_activities.activity_at', '<=', $scope->toDate)
            ->join('customers', 'customers.id', '=', 'customer_activities.customer_id')
            ->when($scope->customerType, fn ($q) => $q->where('customers.customer_type', $scope->customerType))
            ->when($scope->status, fn ($q) => $q->where('customers.status', $scope->status))
            ->when($scope->search !== '', function ($q) use ($scope) {
                $term = '%'.$scope->search.'%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('customers.company_name', 'like', $term)
                        ->orWhere('customer_activities.subject', 'like', $term);
                });
            })
            ->select(
                'customers.company_name',
                'customer_activities.activity_type',
                'customer_activities.subject',
                'customer_activities.activity_at',
            )
            ->orderByDesc('customer_activities.activity_at');

        $paginator = $query->paginate(self::PER_PAGE, ['*'], 'page', $scope->page);

        return $paginator->through(fn ($row) => [
            'customer' => $row->company_name,
            'type' => $this->displayLabel($row->activity_type),
            'subject' => $row->subject,
            'date' => Carbon::parse($row->activity_at)->format('d M Y H:i'),
        ]);
    }

    protected function paginateOrderActivity(CommercialCustomerReportScope $scope): LengthAwarePaginator
    {
        if (! $this->hasTable('sales_orders')) {
            return $this->emptyPaginator($scope);
        }

        $query = SalesOrder::query()
            ->where('sales_orders.company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('sales_orders.branch_id', $scope->branchId))
            ->whereNotIn('sales_orders.status', $this->revenueExcludedStatuses())
            ->whereDate('sales_orders.order_date', '>=', $scope->fromDate)
            ->whereDate('sales_orders.order_date', '<=', $scope->toDate)
            ->join('customers', 'customers.id', '=', 'sales_orders.customer_id')
            ->select(
                'customers.company_name',
                'sales_orders.order_number',
                'sales_orders.total_amount',
                'sales_orders.order_date',
            )
            ->orderByDesc('sales_orders.order_date');

        $paginator = $query->paginate(self::PER_PAGE, ['*'], 'page', $scope->page);

        return $paginator->through(fn ($row) => [
            'customer' => $row->company_name,
            'type' => __('Order'),
            'subject' => $row->order_number,
            'date' => Carbon::parse($row->order_date)->format('d M Y'),
            'value' => $this->money((float) $row->total_amount),
        ]);
    }

    protected function paginateRankedCustomers(CommercialCustomerReportScope $scope, string $rankBy): LengthAwarePaginator
    {
        if (! $this->hasTable('sales_orders')) {
            return $this->emptyPaginator($scope);
        }

        if ($rankBy === 'lifetime') {
            $sub = SalesOrder::query()
                ->where('company_id', $scope->companyId)
                ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
                ->whereNotIn('status', $this->revenueExcludedStatuses())
                ->select(
                    'customer_id',
                    DB::raw('COUNT(*) as orders'),
                    DB::raw('SUM(total_amount) as lifetime_value'),
                    DB::raw('MAX(order_date) as last_order'),
                )
                ->groupBy('customer_id');

            $paginator = DB::query()
                ->fromSub($sub, 'ranked')
                ->orderByDesc('lifetime_value')
                ->paginate(self::PER_PAGE, ['*'], 'page', $scope->page);
        } else {
            $sub = SalesOrder::query()
                ->where('company_id', $scope->companyId)
                ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
                ->whereNotIn('status', $this->revenueExcludedStatuses())
                ->whereDate('order_date', '>=', $scope->fromDate)
                ->whereDate('order_date', '<=', $scope->toDate)
                ->select(
                    'customer_id',
                    DB::raw('COUNT(*) as orders'),
                    DB::raw('SUM(total_amount) as revenue'),
                    DB::raw('MAX(order_date) as last_order'),
                )
                ->groupBy('customer_id');

            $paginator = DB::query()
                ->fromSub($sub, 'ranked')
                ->orderByDesc('revenue')
                ->paginate(self::PER_PAGE, ['*'], 'page', $scope->page);
        }

        $customerIds = $paginator->getCollection()->pluck('customer_id');
        $names = Customer::query()->whereIn('id', $customerIds)->pluck('company_name', 'id');

        $mapped = $paginator->getCollection()->map(function ($row) use ($names, $rankBy) {
            $revenue = (float) ($row->revenue ?? $row->lifetime_value ?? 0);

            return [
                'customer' => $names[$row->customer_id] ?? '—',
                'orders' => (string) $row->orders,
                'revenue' => $this->money($revenue),
                'last_order' => $row->last_order ? Carbon::parse($row->last_order)->format('d M Y') : '—',
                'lifetime_value' => $this->money((float) ($row->lifetime_value ?? $revenue)),
            ];
        });

        return new Paginator(
            $mapped,
            $paginator->total(),
            $paginator->perPage(),
            $paginator->currentPage(),
            ['path' => request()->url(), 'query' => request()->query()],
        );
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    protected function orderStatsSubquery(CommercialCustomerReportScope $scope)
    {
        return SalesOrder::query()
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->whereNotIn('status', $this->revenueExcludedStatuses())
            ->whereDate('order_date', '>=', $scope->fromDate)
            ->whereDate('order_date', '<=', $scope->toDate)
            ->select(
                'customer_id',
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('MAX(order_date) as last_order'),
            )
            ->groupBy('customer_id');
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    protected function quoteStatsSubquery(CommercialCustomerReportScope $scope)
    {
        return Quotation::query()
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->whereIn('status', $this->openQuotationStatuses())
            ->select('customer_id', DB::raw('COUNT(*) as open_quotes'))
            ->groupBy('customer_id');
    }

    /**
     * @return array<int, float>
     */
    protected function customerLifetimeValues(CommercialCustomerReportScope $scope, array $customerIds): array
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

    protected function emptyPaginator(CommercialCustomerReportScope $scope): LengthAwarePaginator
    {
        return new Paginator([], 0, self::PER_PAGE, $scope->page, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);
    }

    protected function monthPeriodSql(string $column): string
    {
        return match (DB::connection()->getDriverName()) {
            'sqlite' => "strftime('%Y-%m', {$column})",
            'pgsql' => "to_char({$column}, 'YYYY-MM')",
            default => "DATE_FORMAT({$column}, '%Y-%m')",
        };
    }

    protected function displayLabel(mixed $value): string
    {
        if ($value instanceof \BackedEnum) {
            $value = $value->value;
        } elseif ($value instanceof \UnitEnum) {
            $value = $value->name;
        }

        return ucfirst(str_replace('_', ' ', (string) ($value ?? '')));
    }
}
