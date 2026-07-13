<?php

namespace App\Support\Commercial\Reports;

use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Branch;
use App\Models\Crm\Customer;
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

class CommercialSalesOrderReportQueries
{
    public const PER_PAGE = 25;

    public function hasTable(string $table): bool
    {
        return Schema::hasTable($table);
    }

    public function money(float $amount): string
    {
        return 'KES '.number_format($amount, 0);
    }

    /**
     * @return list<string>
     */
    public function openStatusValues(): array
    {
        return [
            SalesOrderStatus::Confirmed->value,
            SalesOrderStatus::ReadyForProduction->value,
            SalesOrderStatus::InProduction->value,
            SalesOrderStatus::ReadyForDispatch->value,
            SalesOrderStatus::OnHold->value,
        ];
    }

    /**
     * @return list<string>
     */
    public function pendingStatusValues(): array
    {
        return [
            SalesOrderStatus::Draft->value,
            SalesOrderStatus::Confirmed->value,
            SalesOrderStatus::OnHold->value,
        ];
    }

    /**
     * @return list<string>
     */
    public function completedStatusValues(): array
    {
        return [
            SalesOrderStatus::Completed->value,
            SalesOrderStatus::Delivered->value,
            SalesOrderStatus::Closed->value,
        ];
    }

    public function baseOrderQuery(CommercialSalesOrderReportScope $scope): Builder
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

        if ($scope->quotationSource === 'from_quotation') {
            $query->whereNotNull('sales_orders.quotation_id');
        } elseif ($scope->quotationSource === 'direct') {
            $query->whereNull('sales_orders.quotation_id');
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

    public function totalOrders(CommercialSalesOrderReportScope $scope): int
    {
        if (! $this->hasTable('sales_orders')) {
            return 0;
        }

        return (int) $this->baseOrderQuery($scope)->count();
    }

    public function openOrders(CommercialSalesOrderReportScope $scope): int
    {
        if (! $this->hasTable('sales_orders')) {
            return 0;
        }

        return (int) $this->baseOrderQuery($scope)
            ->whereIn('sales_orders.status', $this->openStatusValues())
            ->count();
    }

    public function completedOrders(CommercialSalesOrderReportScope $scope): int
    {
        if (! $this->hasTable('sales_orders')) {
            return 0;
        }

        return (int) $this->baseOrderQuery($scope)
            ->whereIn('sales_orders.status', $this->completedStatusValues())
            ->count();
    }

    public function cancelledOrders(CommercialSalesOrderReportScope $scope): int
    {
        if (! $this->hasTable('sales_orders')) {
            return 0;
        }

        return (int) $this->baseOrderQuery($scope)
            ->where('sales_orders.status', SalesOrderStatus::Cancelled)
            ->count();
    }

    public function totalOrderValue(CommercialSalesOrderReportScope $scope): float
    {
        if (! $this->hasTable('sales_orders')) {
            return 0.0;
        }

        return (float) $this->baseOrderQuery($scope)->sum('sales_orders.total_amount');
    }

    public function averageOrderValue(CommercialSalesOrderReportScope $scope): float
    {
        $orders = $this->totalOrders($scope);

        return $orders > 0 ? $this->totalOrderValue($scope) / $orders : 0.0;
    }

    public function ordersAwaitingProduction(CommercialSalesOrderReportScope $scope): int
    {
        if (! $this->hasTable('sales_orders')) {
            return 0;
        }

        return (int) $this->baseOrderQuery($scope)
            ->where('sales_orders.status', SalesOrderStatus::ReadyForProduction)
            ->count();
    }

    public function quoteToOrderConversionPercent(CommercialSalesOrderReportScope $scope): ?float
    {
        if (! $this->hasTable('quotations')) {
            return null;
        }

        $totalQuotations = (int) Quotation::query()
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->whereDate('quotation_date', '>=', $scope->fromDate)
            ->whereDate('quotation_date', '<=', $scope->toDate)
            ->count();

        if ($totalQuotations === 0) {
            return 0.0;
        }

        $converted = (int) Quotation::query()
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->whereDate('quotation_date', '>=', $scope->fromDate)
            ->whereDate('quotation_date', '<=', $scope->toDate)
            ->where('status', QuotationStatus::Converted)
            ->count();

        return round(($converted / $totalQuotations) * 100, 1);
    }

    public function orderCompletionRatePercent(CommercialSalesOrderReportScope $scope): ?float
    {
        if (! $this->hasTable('sales_orders')) {
            return null;
        }

        $total = (int) $this->baseOrderQuery($scope)
            ->where('sales_orders.status', '!=', SalesOrderStatus::Draft)
            ->count();

        if ($total === 0) {
            return 0.0;
        }

        $completed = $this->completedOrders($scope);

        return round(($completed / $total) * 100, 1);
    }

    /**
     * @return array<string, mixed>
     */
    public function summaryMetrics(CommercialSalesOrderReportScope $scope): array
    {
        return [
            'total_orders' => $this->totalOrders($scope),
            'open_orders' => $this->openOrders($scope),
            'completed_orders' => $this->completedOrders($scope),
            'cancelled_orders' => $this->cancelledOrders($scope),
            'total_value' => $this->totalOrderValue($scope),
            'average_value' => $this->averageOrderValue($scope),
            'awaiting_production' => $this->ordersAwaitingProduction($scope),
            'quote_conversion' => $this->quoteToOrderConversionPercent($scope),
            'completion_rate' => $this->orderCompletionRatePercent($scope),
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    public function statusBreakdown(CommercialSalesOrderReportScope $scope): array
    {
        if (! $this->hasTable('sales_orders')) {
            return [];
        }

        $rows = $this->baseOrderQuery($scope)
            ->select(
                'sales_orders.status',
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(sales_orders.total_amount) as value'),
            )
            ->groupBy('sales_orders.status')
            ->orderByDesc('orders')
            ->get();

        return $rows->map(function ($row) {
            $orders = (int) $row->orders;
            $value = (float) $row->value;
            $status = $row->status instanceof SalesOrderStatus
                ? $row->status->value
                : (string) $row->status;

            return [
                'status' => ucfirst(str_replace('_', ' ', $status)),
                'orders' => (string) $orders,
                'value' => $this->money($value),
                'average' => $this->money($orders > 0 ? $value / $orders : 0),
            ];
        })->all();
    }

    public function paginateOpenOrders(CommercialSalesOrderReportScope $scope): LengthAwarePaginator
    {
        return $this->paginateOrderRows($scope, $this->openStatusValues());
    }

    public function paginatePendingOrders(CommercialSalesOrderReportScope $scope): LengthAwarePaginator
    {
        return $this->paginateOrderRows($scope, $this->pendingStatusValues());
    }

    public function paginateCompletedOrders(CommercialSalesOrderReportScope $scope): LengthAwarePaginator
    {
        return $this->paginateOrderRows($scope, $this->completedStatusValues());
    }

    public function paginateCancelledOrders(CommercialSalesOrderReportScope $scope): LengthAwarePaginator
    {
        return $this->paginateOrderRows($scope, [SalesOrderStatus::Cancelled->value]);
    }

    public function paginateAwaitingProduction(CommercialSalesOrderReportScope $scope): LengthAwarePaginator
    {
        return $this->paginateOrderRows($scope, [SalesOrderStatus::ReadyForProduction->value]);
    }

    public function paginateFromQuotations(CommercialSalesOrderReportScope $scope): LengthAwarePaginator
    {
        if (! $this->hasTable('sales_orders') || ! $this->hasTable('quotations')) {
            return $this->emptyPaginator($scope);
        }

        $paginator = $this->baseOrderQuery($scope)
            ->whereNotNull('sales_orders.quotation_id')
            ->join('quotations', 'quotations.id', '=', 'sales_orders.quotation_id')
            ->select(
                'sales_orders.id',
                'sales_orders.order_number',
                'sales_orders.customer_id',
                'sales_orders.branch_id',
                'sales_orders.created_by',
                'sales_orders.status',
                'sales_orders.order_date',
                'sales_orders.total_amount',
                'quotations.quotation_number',
                'quotations.quotation_date',
            )
            ->orderByDesc('sales_orders.order_date')
            ->paginate(self::PER_PAGE, ['*'], 'page', $scope->page);

        return $this->mapOrderPaginator($paginator, includeQuotation: true);
    }

    public function paginateByCustomer(CommercialSalesOrderReportScope $scope): LengthAwarePaginator
    {
        if (! $this->hasTable('sales_orders')) {
            return $this->emptyPaginator($scope);
        }

        $paginator = $this->baseOrderQuery($scope)
            ->select(
                'sales_orders.customer_id',
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(sales_orders.total_amount) as value'),
                DB::raw('AVG(sales_orders.total_amount) as average_value'),
                DB::raw('SUM(CASE WHEN sales_orders.status IN ("'.implode('","', $this->openStatusValues()).'") THEN 1 ELSE 0 END) as open_orders'),
            )
            ->groupBy('sales_orders.customer_id')
            ->orderByDesc('value')
            ->paginate(self::PER_PAGE, ['*'], 'page', $scope->page);

        $names = Customer::query()
            ->whereIn('id', $paginator->getCollection()->pluck('customer_id'))
            ->pluck('company_name', 'id');

        return $paginator->through(function ($row) use ($names) {
            return [
                'customer' => $names[$row->customer_id] ?? '—',
                'orders' => (string) $row->orders,
                'value' => $this->money((float) $row->value),
                'average' => $this->money((float) $row->average_value),
                'open' => (string) $row->open_orders,
            ];
        });
    }

    public function paginateByBranch(CommercialSalesOrderReportScope $scope): LengthAwarePaginator
    {
        if (! $this->hasTable('sales_orders')) {
            return $this->emptyPaginator($scope);
        }

        $paginator = $this->baseOrderQuery($scope)
            ->select(
                'sales_orders.branch_id',
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(sales_orders.total_amount) as value'),
                DB::raw('AVG(sales_orders.total_amount) as average_value'),
                DB::raw('SUM(CASE WHEN sales_orders.status IN ("'.implode('","', $this->openStatusValues()).'") THEN 1 ELSE 0 END) as open_orders'),
            )
            ->groupBy('sales_orders.branch_id')
            ->orderByDesc('value')
            ->paginate(self::PER_PAGE, ['*'], 'page', $scope->page);

        $names = Branch::query()
            ->whereIn('id', $paginator->getCollection()->pluck('branch_id'))
            ->pluck('name', 'id');

        return $paginator->through(function ($row) use ($names) {
            return [
                'branch' => $names[$row->branch_id] ?? '—',
                'orders' => (string) $row->orders,
                'value' => $this->money((float) $row->value),
                'average' => $this->money((float) $row->average_value),
                'open' => (string) $row->open_orders,
            ];
        });
    }

    public function paginateBySalesperson(CommercialSalesOrderReportScope $scope): LengthAwarePaginator
    {
        if (! $this->hasTable('sales_orders')) {
            return $this->emptyPaginator($scope);
        }

        $paginator = $this->baseOrderQuery($scope)
            ->select(
                'sales_orders.created_by',
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(sales_orders.total_amount) as value'),
                DB::raw('AVG(sales_orders.total_amount) as average_value'),
                DB::raw('SUM(CASE WHEN sales_orders.status IN ("'.implode('","', $this->completedStatusValues()).'") THEN 1 ELSE 0 END) as completed_orders'),
            )
            ->groupBy('sales_orders.created_by')
            ->orderByDesc('value')
            ->paginate(self::PER_PAGE, ['*'], 'page', $scope->page);

        $names = User::query()
            ->whereIn('id', $paginator->getCollection()->pluck('created_by'))
            ->pluck('name', 'id');

        return $paginator->through(function ($row) use ($names) {
            return [
                'salesperson' => $names[$row->created_by] ?? '—',
                'orders' => (string) $row->orders,
                'value' => $this->money((float) $row->value),
                'average' => $this->money((float) $row->average_value),
                'completed' => (string) $row->completed_orders,
            ];
        });
    }

    /**
     * @return list<array<string, string>>
     */
    public function orderAgingBuckets(CommercialSalesOrderReportScope $scope): array
    {
        if (! $this->hasTable('sales_orders')) {
            return [];
        }

        $today = now()->toDateString();

        $rows = $this->baseOrderQuery($scope)
            ->whereIn('sales_orders.status', $this->openStatusValues())
            ->select(
                DB::raw("CASE
                    WHEN DATEDIFF('{$today}', sales_orders.order_date) <= 7 THEN '0-7 days'
                    WHEN DATEDIFF('{$today}', sales_orders.order_date) <= 14 THEN '8-14 days'
                    WHEN DATEDIFF('{$today}', sales_orders.order_date) <= 30 THEN '15-30 days'
                    WHEN DATEDIFF('{$today}', sales_orders.order_date) <= 60 THEN '31-60 days'
                    ELSE '60+ days'
                END as bucket"),
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(sales_orders.total_amount) as value'),
            )
            ->groupBy('bucket')
            ->orderByRaw("FIELD(bucket, '0-7 days', '8-14 days', '15-30 days', '31-60 days', '60+ days')")
            ->get();

        return $rows->map(fn ($row) => [
            'bucket' => (string) $row->bucket,
            'orders' => (string) $row->orders,
            'value' => $this->money((float) $row->value),
        ])->all();
    }

    /**
     * @return list<array<string, string>>
     */
    public function orderValueBuckets(CommercialSalesOrderReportScope $scope): array
    {
        if (! $this->hasTable('sales_orders')) {
            return [];
        }

        $rows = $this->baseOrderQuery($scope)
            ->select(
                DB::raw("CASE
                    WHEN sales_orders.total_amount < 25000 THEN 'Under KES 25K'
                    WHEN sales_orders.total_amount < 50000 THEN 'KES 25K – 50K'
                    WHEN sales_orders.total_amount < 100000 THEN 'KES 50K – 100K'
                    WHEN sales_orders.total_amount < 250000 THEN 'KES 100K – 250K'
                    ELSE 'Over KES 250K'
                END as bucket"),
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(sales_orders.total_amount) as value'),
                DB::raw('AVG(sales_orders.total_amount) as average_value'),
            )
            ->groupBy('bucket')
            ->orderByRaw("FIELD(bucket, 'Under KES 25K', 'KES 25K – 50K', 'KES 50K – 100K', 'KES 100K – 250K', 'Over KES 250K')")
            ->get();

        return $rows->map(fn ($row) => [
            'bucket' => (string) $row->bucket,
            'orders' => (string) $row->orders,
            'value' => $this->money((float) $row->value),
            'average' => $this->money((float) $row->average_value),
        ])->all();
    }

    /**
     * @param  list<string>  $statuses
     */
    protected function paginateOrderRows(CommercialSalesOrderReportScope $scope, array $statuses): LengthAwarePaginator
    {
        if (! $this->hasTable('sales_orders')) {
            return $this->emptyPaginator($scope);
        }

        $paginator = $this->baseOrderQuery($scope)
            ->whereIn('sales_orders.status', $statuses)
            ->select(
                'sales_orders.id',
                'sales_orders.order_number',
                'sales_orders.customer_id',
                'sales_orders.branch_id',
                'sales_orders.created_by',
                'sales_orders.status',
                'sales_orders.order_date',
                'sales_orders.required_date',
                'sales_orders.total_amount',
            )
            ->orderByDesc('sales_orders.order_date')
            ->paginate(self::PER_PAGE, ['*'], 'page', $scope->page);

        return $this->mapOrderPaginator($paginator);
    }

    protected function mapOrderPaginator(LengthAwarePaginator $paginator, bool $includeQuotation = false): LengthAwarePaginator
    {
        $collection = $paginator->getCollection();

        $customerNames = Customer::query()
            ->whereIn('id', $collection->pluck('customer_id'))
            ->pluck('company_name', 'id');

        $branchNames = Branch::query()
            ->whereIn('id', $collection->pluck('branch_id'))
            ->pluck('name', 'id');

        $userNames = User::query()
            ->whereIn('id', $collection->pluck('created_by'))
            ->pluck('name', 'id');

        $today = now()->startOfDay();

        return $paginator->through(function ($row) use ($customerNames, $branchNames, $userNames, $today, $includeQuotation) {
            $status = $row->status instanceof SalesOrderStatus
                ? $row->status->value
                : (string) $row->status;

            $mapped = [
                'order' => $row->order_number,
                'customer' => $customerNames[$row->customer_id] ?? '—',
                'branch' => $branchNames[$row->branch_id] ?? '—',
                'salesperson' => $userNames[$row->created_by] ?? '—',
                'status' => ucfirst(str_replace('_', ' ', $status)),
                'order_date' => $row->order_date ? Carbon::parse($row->order_date)->format('d M Y') : '—',
                'value' => $this->money((float) $row->total_amount),
            ];

            if ($includeQuotation) {
                $mapped['quotation'] = $row->quotation_number ?? '—';
                $mapped['quote_date'] = $row->quotation_date
                    ? Carbon::parse($row->quotation_date)->format('d M Y')
                    : '—';
            } else {
                $mapped['required_date'] = $row->required_date
                    ? Carbon::parse($row->required_date)->format('d M Y')
                    : '—';
                $mapped['age_days'] = $row->order_date
                    ? (string) Carbon::parse($row->order_date)->diffInDays($today)
                    : '—';
            }

            return $mapped;
        });
    }

    protected function emptyPaginator(CommercialSalesOrderReportScope $scope): LengthAwarePaginator
    {
        return new Paginator([], 0, self::PER_PAGE, $scope->page, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);
    }
}
