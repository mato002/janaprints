<?php

namespace App\Support\Commercial\Reports;

use App\Enums\PosPaymentMethod;
use App\Enums\PosReturnStatus;
use App\Enums\PosSaleStatus;
use App\Enums\PosSessionStatus;
use App\Models\Branch;
use App\Models\Pos\PosSale;
use App\Models\Pos\PosSession;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CommercialPosReportQueries
{
    public const PER_PAGE = 25;

    public function hasTable(string $table): bool
    {
        return Schema::hasTable($table);
    }

    public function baseSaleQuery(CommercialPosReportScope $scope): Builder
    {
        $query = PosSale::query()
            ->where('pos_sales.company_id', $scope->companyId)
            ->whereDate('pos_sales.sale_date', '>=', $scope->fromDate)
            ->whereDate('pos_sales.sale_date', '<=', $scope->toDate);

        if ($scope->branchId !== null) {
            $query->where('pos_sales.branch_id', $scope->branchId);
        }

        if ($scope->cashierId !== null) {
            $query->where('pos_sales.cashier_id', $scope->cashierId);
        }

        if ($scope->status !== null && $scope->status !== '') {
            $query->where('pos_sales.status', $scope->status);
        }

        if ($scope->paymentMethod !== null && $this->hasTable('pos_payments')) {
            $query->whereHas('payments', fn (Builder $inner) => $inner->where('payment_method', $scope->paymentMethod));
        }

        if ($scope->search !== '') {
            $term = '%'.$scope->search.'%';
            $query->where('pos_sales.sale_number', 'like', $term);
        }

        return $query;
    }

    public function todaySalesCount(CommercialPosReportScope $scope): int
    {
        if (! $this->hasTable('pos_sales')) {
            return 0;
        }

        return (int) $this->scopedTodaySalesQuery($scope)
            ->where('status', PosSaleStatus::Paid)
            ->count();
    }

    public function todaySalesValue(CommercialPosReportScope $scope): float
    {
        if (! $this->hasTable('pos_sales')) {
            return 0.0;
        }

        return (float) $this->scopedTodaySalesQuery($scope)
            ->where('status', PosSaleStatus::Paid)
            ->sum('total_amount');
    }

    public function todayReturnsCount(CommercialPosReportScope $scope): int
    {
        if ($this->hasReturnsTable()) {
            return (int) $this->baseReturnQuery($scope)
                ->where('status', PosReturnStatus::Completed->value)
                ->whereDate('completed_at', today())
                ->count();
        }

        if (! $this->hasTable('pos_sales')) {
            return 0;
        }

        return (int) $this->scopedTodaySalesQuery($scope)
            ->where('status', PosSaleStatus::Refunded)
            ->count();
    }

    public function openSessionsCount(CommercialPosReportScope $scope): int
    {
        if (! $this->hasTable('pos_sessions')) {
            return 0;
        }

        return (int) $this->baseSessionQuery($scope)
            ->where('status', PosSessionStatus::Open)
            ->count();
    }

    public function closedSessionsCount(CommercialPosReportScope $scope): int
    {
        if (! $this->hasTable('pos_sessions')) {
            return 0;
        }

        return (int) $this->baseSessionQuery($scope)
            ->where('status', PosSessionStatus::Closed)
            ->whereDate('closed_at', '>=', $scope->fromDate)
            ->whereDate('closed_at', '<=', $scope->toDate)
            ->count();
    }

    public function paymentCollected(CommercialPosReportScope $scope, PosPaymentMethod $method): float
    {
        if (! $this->hasTable('pos_payments') || ! $this->hasTable('pos_sales')) {
            return 0.0;
        }

        $query = DB::table('pos_payments')
            ->join('pos_sales', 'pos_sales.id', '=', 'pos_payments.pos_sale_id')
            ->where('pos_sales.company_id', $scope->companyId)
            ->where('pos_sales.status', PosSaleStatus::Paid->value)
            ->where('pos_payments.payment_method', $method->value)
            ->whereDate('pos_sales.sale_date', '>=', $scope->fromDate)
            ->whereDate('pos_sales.sale_date', '<=', $scope->toDate);

        if ($scope->branchId !== null) {
            $query->where('pos_sales.branch_id', $scope->branchId);
        }

        if ($scope->cashierId !== null) {
            $query->where('pos_sales.cashier_id', $scope->cashierId);
        }

        if ($scope->paymentMethod !== null) {
            $query->where('pos_payments.payment_method', $scope->paymentMethod);
        }

        return (float) $query->sum('pos_payments.amount');
    }

    public function averageSaleValue(CommercialPosReportScope $scope): ?float
    {
        if (! $this->hasTable('pos_sales')) {
            return null;
        }

        $avg = $this->baseSaleQuery($scope)
            ->where('pos_sales.status', PosSaleStatus::Paid)
            ->avg('pos_sales.total_amount');

        return $avg !== null ? round((float) $avg, 2) : null;
    }

    /**
     * @return array{name: string, value: float}|null
     */
    public function topCashier(CommercialPosReportScope $scope): ?array
    {
        if (! $this->hasTable('pos_sales')) {
            return null;
        }

        $row = $this->baseSaleQuery($scope)
            ->where('pos_sales.status', PosSaleStatus::Paid)
            ->select('cashier_id', DB::raw('SUM(total_amount) as revenue'))
            ->groupBy('cashier_id')
            ->orderByDesc('revenue')
            ->first();

        if ($row === null) {
            return null;
        }

        $name = User::query()->whereKey($row->cashier_id)->value('name') ?? __('Unknown');

        return ['name' => (string) $name, 'value' => (float) $row->revenue];
    }

    /**
     * @return array{name: string, value: float}|null
     */
    public function topBranch(CommercialPosReportScope $scope): ?array
    {
        if (! $this->hasTable('pos_sales')) {
            return null;
        }

        $row = $this->baseSaleQuery($scope)
            ->where('pos_sales.status', PosSaleStatus::Paid)
            ->select('branch_id', DB::raw('SUM(total_amount) as revenue'))
            ->groupBy('branch_id')
            ->orderByDesc('revenue')
            ->first();

        if ($row === null) {
            return null;
        }

        $name = Branch::query()->whereKey($row->branch_id)->value('name') ?? __('Unknown');

        return ['name' => (string) $name, 'value' => (float) $row->revenue];
    }

    public function averageBasketSize(CommercialPosReportScope $scope): ?float
    {
        if (! $this->hasTable('pos_sale_items') || ! $this->hasTable('pos_sales')) {
            return null;
        }

        $saleIds = $this->baseSaleQuery($scope)
            ->where('pos_sales.status', PosSaleStatus::Paid)
            ->pluck('pos_sales.id');

        if ($saleIds->isEmpty()) {
            return null;
        }

        $items = (float) DB::table('pos_sale_items')->whereIn('pos_sale_id', $saleIds)->sum('quantity');

        return round($items / $saleIds->count(), 2);
    }

    public function returnRatePercent(CommercialPosReportScope $scope): ?float
    {
        if (! $this->hasTable('pos_sales')) {
            return null;
        }

        $paid = (int) $this->baseSaleQuery($scope)->where('pos_sales.status', PosSaleStatus::Paid)->count();
        $refunded = $this->hasReturnsTable()
            ? (int) $this->baseReturnQuery($scope)->where('status', PosReturnStatus::Completed->value)->count()
            : (int) $this->baseSaleQuery($scope)->where('pos_sales.status', PosSaleStatus::Refunded)->count();
        $denominator = $paid + $refunded;

        if ($denominator === 0) {
            return null;
        }

        return round(($refunded / $denominator) * 100, 1);
    }

    public function refundValue(CommercialPosReportScope $scope): float
    {
        if ($this->hasReturnsTable()) {
            return (float) $this->baseReturnQuery($scope)
                ->where('status', PosReturnStatus::Completed->value)
                ->sum('refund_amount');
        }

        if (! $this->hasTable('pos_sales')) {
            return 0.0;
        }

        return (float) $this->baseSaleQuery($scope)
            ->where('pos_sales.status', PosSaleStatus::Refunded)
            ->sum('total_amount');
    }

    public function salesTrendPercent(CommercialPosReportScope $scope): ?float
    {
        if (! $this->hasTable('pos_sales')) {
            return null;
        }

        $current = (float) $this->baseSaleQuery($scope)
            ->where('pos_sales.status', PosSaleStatus::Paid)
            ->sum('total_amount');

        $days = max(1, (int) (strtotime($scope->toDate) - strtotime($scope->fromDate)) / 86400 + 1);
        $priorFrom = date('Y-m-d', strtotime($scope->fromDate." -{$days} days"));
        $priorTo = date('Y-m-d', strtotime($scope->fromDate.' -1 day'));

        $priorScope = new CommercialPosReportScope(
            companyId: $scope->companyId,
            branchId: $scope->branchId,
            fromDate: $priorFrom,
            toDate: $priorTo,
            cashierId: $scope->cashierId,
            paymentMethod: $scope->paymentMethod,
            status: PosSaleStatus::Paid->value,
            search: $scope->search,
            tab: $scope->tab,
            page: 1,
        );

        $prior = (float) $this->baseSaleQuery($priorScope)->sum('total_amount');

        if ($prior <= 0) {
            return $current > 0 ? 100.0 : null;
        }

        return round((($current - $prior) / $prior) * 100, 1);
    }

    public function formatMoney(float $amount): string
    {
        return number_format($amount, 2);
    }

    public function paginateSalesByCashier(CommercialPosReportScope $scope): LengthAwarePaginator
    {
        $rows = $this->baseSaleQuery($scope)
            ->where('pos_sales.status', PosSaleStatus::Paid)
            ->select(
                'cashier_id',
                DB::raw('COUNT(*) as sales_count'),
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('AVG(total_amount) as avg_sale')
            )
            ->groupBy('cashier_id')
            ->orderByDesc('revenue')
            ->get();

        $names = $this->userNames($rows->pluck('cashier_id'));

        $mapped = $rows->map(fn ($row) => [
            'cashier' => $names[$row->cashier_id] ?? __('Unknown'),
            'sales' => (string) $row->sales_count,
            'revenue' => $this->formatMoney((float) $row->revenue),
            'avg_sale' => $this->formatMoney((float) $row->avg_sale),
        ]);

        return $this->paginateCollection($mapped, $scope->page);
    }

    public function paginateSalesByBranch(CommercialPosReportScope $scope): LengthAwarePaginator
    {
        $rows = $this->baseSaleQuery($scope)
            ->where('pos_sales.status', PosSaleStatus::Paid)
            ->select(
                'branch_id',
                DB::raw('COUNT(*) as sales_count'),
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('AVG(total_amount) as avg_sale')
            )
            ->groupBy('branch_id')
            ->orderByDesc('revenue')
            ->get();

        $names = $this->branchNames($rows->pluck('branch_id'));

        $mapped = $rows->map(fn ($row) => [
            'branch' => $names[$row->branch_id] ?? __('Unknown'),
            'sales' => (string) $row->sales_count,
            'revenue' => $this->formatMoney((float) $row->revenue),
            'avg_sale' => $this->formatMoney((float) $row->avg_sale),
        ]);

        return $this->paginateCollection($mapped, $scope->page);
    }

    public function paginateSalesByDay(CommercialPosReportScope $scope): LengthAwarePaginator
    {
        $dateExpr = $this->isSqlite()
            ? "date(pos_sales.sale_date)"
            : 'DATE(pos_sales.sale_date)';

        $rows = $this->baseSaleQuery($scope)
            ->where('pos_sales.status', PosSaleStatus::Paid)
            ->select(
                DB::raw("{$dateExpr} as sale_day"),
                DB::raw('COUNT(*) as sales_count'),
                DB::raw('SUM(total_amount) as revenue')
            )
            ->groupBy('sale_day')
            ->orderByDesc('sale_day')
            ->get();

        $mapped = $rows->map(fn ($row) => [
            'day' => (string) $row->sale_day,
            'sales' => (string) $row->sales_count,
            'revenue' => $this->formatMoney((float) $row->revenue),
        ]);

        return $this->paginateCollection($mapped, $scope->page);
    }

    public function paginateSalesByHour(CommercialPosReportScope $scope): LengthAwarePaginator
    {
        $hourExpr = $this->isSqlite()
            ? "CAST(strftime('%H', pos_sales.created_at) AS INTEGER)"
            : 'HOUR(pos_sales.created_at)';

        $rows = $this->baseSaleQuery($scope)
            ->where('pos_sales.status', PosSaleStatus::Paid)
            ->select(
                DB::raw("{$hourExpr} as sale_hour"),
                DB::raw('COUNT(*) as sales_count'),
                DB::raw('SUM(total_amount) as revenue')
            )
            ->groupBy('sale_hour')
            ->orderBy('sale_hour')
            ->get();

        $mapped = $rows->map(fn ($row) => [
            'hour' => sprintf('%02d:00', (int) $row->sale_hour),
            'sales' => (string) $row->sales_count,
            'revenue' => $this->formatMoney((float) $row->revenue),
        ]);

        return $this->paginateCollection($mapped, $scope->page);
    }

    public function paginateReturnsAnalysis(CommercialPosReportScope $scope): LengthAwarePaginator
    {
        if ($this->hasReturnsTable()) {
            $dateExpr = $this->isSqlite()
                ? "date(COALESCE(pos_returns.completed_at, pos_returns.created_at))"
                : 'DATE(COALESCE(pos_returns.completed_at, pos_returns.created_at))';

            $rows = $this->baseReturnQuery($scope)
                ->where('status', PosReturnStatus::Completed->value)
                ->select(
                    DB::raw("{$dateExpr} as return_day"),
                    DB::raw('COUNT(*) as return_count'),
                    DB::raw('SUM(refund_amount) as return_value')
                )
                ->groupBy('return_day')
                ->orderByDesc('return_day')
                ->get();
        } else {
            $dateExpr = $this->isSqlite()
                ? 'date(pos_sales.sale_date)'
                : 'DATE(pos_sales.sale_date)';

            $rows = $this->baseSaleQuery($scope)
                ->where('pos_sales.status', PosSaleStatus::Refunded)
                ->select(
                    DB::raw("{$dateExpr} as return_day"),
                    DB::raw('COUNT(*) as return_count'),
                    DB::raw('SUM(total_amount) as return_value')
                )
                ->groupBy('return_day')
                ->orderByDesc('return_day')
                ->get();
        }

        $paidByDay = $this->baseSaleQuery($scope)
            ->where('pos_sales.status', PosSaleStatus::Paid)
            ->select(
                DB::raw($this->isSqlite() ? 'date(pos_sales.sale_date) as sale_day' : 'DATE(pos_sales.sale_date) as sale_day'),
                DB::raw('COUNT(*) as paid_count')
            )
            ->groupBy('sale_day')
            ->pluck('paid_count', 'sale_day');

        $mapped = $rows->map(function ($row) use ($paidByDay) {
            $paidSameDay = (int) ($paidByDay[$row->return_day] ?? 0);
            $rate = ($paidSameDay + (int) $row->return_count) > 0
                ? round(((int) $row->return_count / ($paidSameDay + (int) $row->return_count)) * 100, 1).'%'
                : '—';

            return [
                'day' => (string) $row->return_day,
                'returns' => (string) $row->return_count,
                'value' => $this->formatMoney((float) $row->return_value),
                'rate' => $rate,
            ];
        });

        return $this->paginateCollection($mapped, $scope->page);
    }

    public function paginateRefundAnalysis(CommercialPosReportScope $scope): LengthAwarePaginator
    {
        if ($this->hasReturnsTable()) {
            $rows = $this->baseReturnQuery($scope)
                ->where('status', PosReturnStatus::Completed->value)
                ->select(
                    'refund_method',
                    DB::raw('COUNT(*) as refund_count'),
                    DB::raw('SUM(refund_amount) as refund_value'),
                    DB::raw('AVG(refund_amount) as avg_refund')
                )
                ->groupBy('refund_method')
                ->orderByDesc('refund_value')
                ->get();

            $mapped = $rows->map(fn ($row) => [
                'method' => ucfirst((string) $row->refund_method),
                'refunds' => (string) $row->refund_count,
                'value' => $this->formatMoney((float) $row->refund_value),
                'avg_refund' => $this->formatMoney((float) $row->avg_refund),
            ]);

            return $this->paginateCollection($mapped, $scope->page);
        }

        if (! $this->hasTable('pos_payments')) {
            return $this->paginateCollection(collect(), $scope->page);
        }

        $query = DB::table('pos_payments')
            ->join('pos_sales', 'pos_sales.id', '=', 'pos_payments.pos_sale_id')
            ->where('pos_sales.company_id', $scope->companyId)
            ->where('pos_sales.status', PosSaleStatus::Refunded->value)
            ->whereDate('pos_sales.sale_date', '>=', $scope->fromDate)
            ->whereDate('pos_sales.sale_date', '<=', $scope->toDate);

        if ($scope->branchId !== null) {
            $query->where('pos_sales.branch_id', $scope->branchId);
        }

        if ($scope->cashierId !== null) {
            $query->where('pos_sales.cashier_id', $scope->cashierId);
        }

        if ($scope->paymentMethod !== null) {
            $query->where('pos_payments.payment_method', $scope->paymentMethod);
        }

        $rows = $query
            ->select(
                'pos_payments.payment_method',
                DB::raw('COUNT(DISTINCT pos_sales.id) as refund_count'),
                DB::raw('SUM(pos_payments.amount) as refund_value'),
                DB::raw('AVG(pos_payments.amount) as avg_refund')
            )
            ->groupBy('pos_payments.payment_method')
            ->orderByDesc('refund_value')
            ->get();

        $mapped = $rows->map(fn ($row) => [
            'method' => ucfirst((string) $row->payment_method),
            'refunds' => (string) $row->refund_count,
            'value' => $this->formatMoney((float) $row->refund_value),
            'avg_refund' => $this->formatMoney((float) $row->avg_refund),
        ]);

        return $this->paginateCollection($mapped, $scope->page);
    }

    public function paginateSessionPerformance(CommercialPosReportScope $scope): LengthAwarePaginator
    {
        if (! $this->hasTable('pos_sessions')) {
            return $this->paginateCollection(collect(), $scope->page);
        }

        $paginator = $this->baseSessionQuery($scope)
            ->whereIn('status', [PosSessionStatus::Closed, PosSessionStatus::Open])
            ->orderByDesc('opened_at')
            ->paginate(self::PER_PAGE, [
                'id', 'session_number', 'branch_id', 'cashier_id', 'status', 'opened_at', 'closed_at', 'variance',
            ], 'page', $scope->page);

        $sessions = collect($paginator->items());
        $branchNames = $this->branchNames($sessions->pluck('branch_id'));
        $cashierNames = $this->userNames($sessions->pluck('cashier_id'));
        $sessionIds = $sessions->pluck('id');
        $metricsBySession = $this->sessionSaleMetricsBatch($sessionIds);

        $mapped = $sessions->map(function (PosSession $session) use ($branchNames, $cashierNames, $metricsBySession) {
            $metrics = $metricsBySession[$session->id] ?? ['sales_count' => 0, 'revenue' => 0.0];

            return [
                'session' => $session->session_number,
                'branch' => $branchNames[$session->branch_id] ?? __('Unknown'),
                'cashier' => $cashierNames[$session->cashier_id] ?? __('Unknown'),
                'status' => ucfirst($session->status->value),
                'sales' => (string) $metrics['sales_count'],
                'revenue' => $this->formatMoney($metrics['revenue']),
                'variance' => $session->variance !== null ? $this->formatMoney((float) $session->variance) : '—',
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

    public function paginatePaymentMethodAnalysis(CommercialPosReportScope $scope): LengthAwarePaginator
    {
        if (! $this->hasTable('pos_payments')) {
            return $this->paginateCollection(collect(), $scope->page);
        }

        $query = DB::table('pos_payments')
            ->join('pos_sales', 'pos_sales.id', '=', 'pos_payments.pos_sale_id')
            ->where('pos_sales.company_id', $scope->companyId)
            ->where('pos_sales.status', PosSaleStatus::Paid->value)
            ->whereDate('pos_sales.sale_date', '>=', $scope->fromDate)
            ->whereDate('pos_sales.sale_date', '<=', $scope->toDate);

        if ($scope->branchId !== null) {
            $query->where('pos_sales.branch_id', $scope->branchId);
        }

        if ($scope->cashierId !== null) {
            $query->where('pos_sales.cashier_id', $scope->cashierId);
        }

        if ($scope->paymentMethod !== null) {
            $query->where('pos_payments.payment_method', $scope->paymentMethod);
        }

        $rows = $query
            ->select(
                'pos_payments.payment_method',
                DB::raw('COUNT(DISTINCT pos_sales.id) as sale_count'),
                DB::raw('SUM(pos_payments.amount) as collected'),
                DB::raw('AVG(pos_payments.amount) as avg_payment')
            )
            ->groupBy('pos_payments.payment_method')
            ->orderByDesc('collected')
            ->get();

        $total = (float) $rows->sum('collected');

        $mapped = $rows->map(function ($row) use ($total) {
            $share = $total > 0 ? round(((float) $row->collected / $total) * 100, 1).'%' : '—';

            return [
                'method' => ucfirst((string) $row->payment_method),
                'sales' => (string) $row->sale_count,
                'collected' => $this->formatMoney((float) $row->collected),
                'share' => $share,
                'avg_payment' => $this->formatMoney((float) $row->avg_payment),
            ];
        });

        return $this->paginateCollection($mapped, $scope->page);
    }

    /**
     * @param  Collection<int, int>  $sessionIds
     * @return array<int, array{sales_count: int, revenue: float}>
     */
    protected function sessionSaleMetricsBatch(Collection $sessionIds): array
    {
        if (! $this->hasTable('pos_sales') || $sessionIds->isEmpty()) {
            return [];
        }

        $rows = PosSale::query()
            ->whereIn('pos_session_id', $sessionIds)
            ->where('status', PosSaleStatus::Paid)
            ->select('pos_session_id', DB::raw('COUNT(*) as sales_count'), DB::raw('SUM(total_amount) as revenue'))
            ->groupBy('pos_session_id')
            ->get();

        return $rows->mapWithKeys(fn ($row) => [
            (int) $row->pos_session_id => [
                'sales_count' => (int) $row->sales_count,
                'revenue' => (float) $row->revenue,
            ],
        ])->all();
    }

    protected function hasReturnsTable(): bool
    {
        return $this->hasTable('pos_returns');
    }

    protected function baseReturnQuery(CommercialPosReportScope $scope): \Illuminate\Database\Query\Builder
    {
        $dateExpr = $this->isSqlite()
            ? 'date(COALESCE(completed_at, created_at))'
            : 'DATE(COALESCE(completed_at, created_at))';

        $query = DB::table('pos_returns')
            ->where('company_id', $scope->companyId)
            ->whereRaw("{$dateExpr} BETWEEN ? AND ?", [$scope->fromDate, $scope->toDate]);

        if ($scope->branchId !== null) {
            $query->where('branch_id', $scope->branchId);
        }

        if ($scope->cashierId !== null) {
            $query->where('created_by', $scope->cashierId);
        }

        if ($scope->paymentMethod !== null) {
            $query->where('refund_method', $scope->paymentMethod);
        }

        if ($scope->status !== null && $scope->status !== '') {
            $query->where('status', $scope->status);
        }

        if ($scope->search !== '' && $this->hasTable('pos_sales')) {
            $query->whereExists(function ($sub) use ($scope) {
                $sub->select(DB::raw(1))
                    ->from('pos_sales')
                    ->whereColumn('pos_sales.id', 'pos_returns.pos_sale_id')
                    ->where('pos_sales.sale_number', 'like', '%'.$scope->search.'%');
            });
        }

        return $query;
    }

    protected function scopedTodaySalesQuery(CommercialPosReportScope $scope): Builder
    {
        $query = PosSale::query()
            ->where('company_id', $scope->companyId)
            ->whereDate('sale_date', today());

        if ($scope->branchId !== null) {
            $query->where('branch_id', $scope->branchId);
        }

        if ($scope->cashierId !== null) {
            $query->where('cashier_id', $scope->cashierId);
        }

        return $query;
    }

    protected function baseSessionQuery(CommercialPosReportScope $scope): Builder
    {
        $query = PosSession::query()->where('company_id', $scope->companyId);

        if ($scope->branchId !== null) {
            $query->where('branch_id', $scope->branchId);
        }

        if ($scope->cashierId !== null) {
            $query->where('cashier_id', $scope->cashierId);
        }

        return $query;
    }

    /**
     * @param  Collection<int, int|string|null>  $ids
     * @return array<int|string, string>
     */
    protected function userNames(Collection $ids): array
    {
        $filtered = $ids->filter()->unique()->values();

        if ($filtered->isEmpty()) {
            return [];
        }

        return User::query()->whereIn('id', $filtered)->pluck('name', 'id')->all();
    }

    /**
     * @param  Collection<int, int|string|null>  $ids
     * @return array<int|string, string>
     */
    protected function branchNames(Collection $ids): array
    {
        $filtered = $ids->filter()->unique()->values();

        if ($filtered->isEmpty()) {
            return [];
        }

        return Branch::query()->whereIn('id', $filtered)->pluck('name', 'id')->all();
    }

    /**
     * @param  Collection<int, array<string, string>>  $items
     */
    protected function paginateCollection(Collection $items, int $page): LengthAwarePaginator
    {
        $total = $items->count();
        $slice = $items->slice(($page - 1) * self::PER_PAGE, self::PER_PAGE)->values();

        return new Paginator(
            $slice,
            $total,
            self::PER_PAGE,
            $page,
            ['path' => request()->url(), 'query' => request()->query()],
        );
    }

    protected function isSqlite(): bool
    {
        return DB::connection()->getDriverName() === 'sqlite';
    }
}
