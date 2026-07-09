<?php

namespace App\Support\Reports;

use App\Enums\CustomerInvoiceStatus;
use App\Enums\CustomerPaymentStatus;
use App\Enums\PosSaleStatus;
use App\Enums\ProductionJobCardStatus;
use App\Enums\ProductionQueueStatus;
use App\Enums\ProductionType;
use App\Enums\SalesOrderStatus;
use App\Models\Pos\PosSale;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionQueue;
use App\Models\Production\ProductionSession;
use App\Models\Production\WorkCenter;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\CustomerPayment;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Services\Production\DepartmentCommandCenterService;
use App\Support\Commercial\Reports\CommercialSalesReportQueries;
use App\Support\Commercial\Reports\CommercialSalesReportScope;
use App\Support\Platform\PlatformCacheService;
use App\Support\Production\DepartmentQueueRegistry;
use App\Support\Sales\SalesOrderFinancialStatusService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class OperationalRegisterQueries
{
    public function __construct(
        protected IntelligenceAggregateQueries $aggregates,
        protected ProductionReportQueries $productionReports,
        protected CommercialSalesReportQueries $commercialSales,
        protected DepartmentCommandCenterService $commandCenters,
        protected DepartmentQueueRegistry $departments,
        protected PlatformCacheService $cache,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function executiveKpis(OperationalRegisterScope $scope, ?User $user = null): array
    {
        $key = 'kpis:'.$scope->cacheKey();

        return $this->cache->remember('operational_registers', $key, function () use ($scope) {
            $intel = $scope->intelligenceScope();
            $todayScope = new OperationalRegisterScope(
                companyId: $scope->companyId,
                branchId: $scope->branchId,
                fromDate: today()->toDateString(),
                toDate: today()->toDateString(),
            );
            $commercialScope = new CommercialSalesReportScope(
                companyId: $scope->companyId,
                branchId: $scope->branchId,
                fromDate: today()->toDateString(),
                toDate: today()->toDateString(),
            );

            $waiting = ProductionQueue::query()
                ->where('company_id', $scope->companyId)
                ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
                ->whereIn('status', [ProductionQueueStatus::Waiting, ProductionQueueStatus::Queued])
                ->count();

            $running = ProductionQueue::query()
                ->where('company_id', $scope->companyId)
                ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
                ->where('status', ProductionQueueStatus::InProgress)
                ->count();

            $overdue = ProductionJobCard::query()
                ->where('company_id', $scope->companyId)
                ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
                ->whereNotNull('required_date')
                ->whereDate('required_date', '<', today())
                ->whereNotIn('status', [
                    ProductionJobCardStatus::Completed,
                    ProductionJobCardStatus::ReadyForDispatch,
                    ProductionJobCardStatus::Cancelled,
                ])
                ->count();

            $outsourced = ProductionJobCard::query()
                ->where('company_id', $scope->companyId)
                ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
                ->where('status', ProductionJobCardStatus::Outsourced)
                ->count();

            $outstanding = $this->aggregates->sumReceivables($intel) ?? 0;

            return [
                'sales_today' => $this->commercialSales->totalSales($commercialScope),
                'production_value_today' => $this->productionValueForScope($todayScope),
                'jobs_completed_today' => $this->aggregates->countCompletedJobsInPeriod($todayScope->intelligenceScope()),
                'jobs_running' => $running,
                'jobs_waiting' => $waiting,
                'jobs_overdue' => $overdue,
                'revenue_today' => $this->commercialSales->totalSales($commercialScope),
                'outstanding_payments' => $outstanding,
                'outsourced_jobs' => $outsourced,
                'machine_utilisation' => $this->averageMachineUtilisation($intel),
                'department_utilisation' => $this->averageDepartmentUtilisation($intel),
                'operator_productivity' => $this->averageOperatorProductivity($intel),
            ];
        }, config('platform.cache.dashboard', 60));
    }

    /**
     * @return array{summary: list<array<string, mixed>>, table: array<string, mixed>}
     */
    public function dailySalesRegister(OperationalRegisterScope $scope, ?User $user = null): array
    {
        $rows = collect()
            ->merge($this->salesOrderRegisterRows($scope, $user))
            ->merge($this->posRegisterRows($scope, $user))
            ->merge($this->paymentRegisterRows($scope, $user))
            ->merge($this->invoiceRegisterRows($scope, $user));

        $rows = $this->sortRegisterRows($rows);

        $totalAmount = $rows->sum(fn (array $row) => (float) ($row['amount_raw'] ?? 0));

        return [
            'summary' => [
                ['label' => __('Transactions'), 'value' => (string) $rows->count(), 'icon' => 'document-text'],
                ['label' => __('Total amount'), 'value' => number_format($totalAmount, 2), 'icon' => 'currency-dollar'],
            ],
            'table' => $this->tablePayload(
                __('Daily Sales Register'),
                [
                    __('Date'), __('Customer'), __('Document'), __('Product'), __('Department'),
                    __('Payment method'), __('Amount'), __('Payment status'), __('Delivery status'),
                    __('Salesperson'), __('Production status'), __('Provider'), __('Provider cost'), __('Margin'), __('Remarks'),
                ],
                $rows->map(fn (array $row) => $this->stripInternalKeys($row))->all(),
                [__('Total'), '', '', '', '', '', number_format($totalAmount, 2), '', '', '', '', '', '', '', ''],
            ),
        ];
    }

    /**
     * @return array{summary: list<array<string, mixed>>, table: array<string, mixed>}
     */
    public function departmentRegister(OperationalRegisterScope $scope, string $department, ?User $user = null): array
    {
        $columns = $this->commandCenters->registerColumnsFor($department);
        $presented = $this->commandCenters->registerRows($this->scopeToRequest($scope), $department, $user);
        $keys = array_column($columns, 'key');
        $amountIndex = array_search('line_amount', $keys, true);
        if ($amountIndex === false) {
            $amountIndex = array_search('amount', $keys, true);
        }
        $totalAmount = $amountIndex === false
            ? 0
            : collect($presented)->sum(fn (array $row) => $this->parseAmount($row['values'][$amountIndex] ?? '0'));

        return [
            'summary' => [
                ['label' => __('Jobs'), 'value' => (string) count($presented), 'icon' => 'collection'],
                ['label' => __('Total amount'), 'value' => number_format($totalAmount, 2), 'icon' => 'currency-dollar'],
            ],
            'table' => $this->tablePayload(
                config("production_operational_registers.registers.{$department}.label", ucfirst($department)),
                array_column($columns, 'label'),
                $presented,
                $this->totalsRow($keys, $presented, ['line_amount', 'amount', 'unit_price', 'selling_price', 'vendor_cost', 'margin']),
            ),
        ];
    }

    /**
     * @return array{summary: list<array<string, mixed>>, table: array<string, mixed>}
     */
    public function productionSummaryRegister(OperationalRegisterScope $scope): array
    {
        $intel = $scope->intelligenceScope();
        $completed = $this->aggregates->countCompletedJobsInPeriod($intel);
        $delayed = $this->aggregates->countDelayedJobs($intel);
        $cancelled = $this->productionReports->countJobsCancelled($intel);
        $avgTurnaround = $this->productionReports->averageTurnaroundDays($intel);

        $rows = $this->productionReports->throughputByDay($intel);
        $structured = array_map(fn (array $row) => [
            'values' => array_map('strval', $row),
            'links' => [],
        ], $rows);

        return [
            'summary' => [
                ['label' => __('Completed'), 'value' => (string) $completed, 'icon' => 'check-circle'],
                ['label' => __('Delayed'), 'value' => (string) $delayed, 'icon' => 'clock'],
                ['label' => __('Cancelled'), 'value' => (string) $cancelled, 'icon' => 'x-circle'],
                ['label' => __('Avg turnaround'), 'value' => $avgTurnaround !== null ? $avgTurnaround.' '.__('days') : '—', 'icon' => 'calendar'],
            ],
            'table' => $this->tablePayload(
                __('Production Summary Register'),
                [__('Date'), __('Completed'), __('Delayed'), __('Cancelled')],
                $structured,
            ),
        ];
    }

    /**
     * @return array{summary: list<array<string, mixed>>, table: array<string, mixed>}
     */
    public function machineUtilisationRegister(OperationalRegisterScope $scope): array
    {
        $rows = $this->hasSessionsTable()
            ? $this->machineUtilisationFromSessions($scope)
            : $this->machineUtilisationFromReports($scope);

        return [
            'summary' => [
                ['label' => __('Machines tracked'), 'value' => (string) count($rows), 'icon' => 'server'],
            ],
            'table' => $this->tablePayload(
                __('Machine Utilisation Register'),
                [__('Machine'), __('Running jobs'), __('Completed jobs'), __('Active time (h)'), __('Idle time (h)'), __('Avg completion (h)'), __('Utilisation')],
                $rows,
            ),
        ];
    }

    /**
     * @return array{summary: list<array<string, mixed>>, table: array<string, mixed>}
     */
    public function operatorProductivityRegister(OperationalRegisterScope $scope): array
    {
        $rows = $this->operatorProductivityRows($scope);

        return [
            'summary' => [
                ['label' => __('Operators'), 'value' => (string) count($rows), 'icon' => 'users'],
            ],
            'table' => $this->tablePayload(
                __('Operator Productivity Register'),
                [__('Operator'), __('Completed'), __('Running'), __('Avg completion (h)'), __('Avg delay (d)'), __('Workload'), __('Department')],
                $rows,
            ),
        ];
    }

    /**
     * @return array{summary: list<array<string, mixed>>, table: array<string, mixed>}
     */
    public function departmentPerformanceRegister(OperationalRegisterScope $scope): array
    {
        $rows = $this->departmentPerformanceRows($scope);

        return [
            'summary' => [
                ['label' => __('Departments'), 'value' => (string) count($rows), 'icon' => 'office-building'],
            ],
            'table' => $this->tablePayload(
                __('Department Performance Register'),
                [
                    __('Department'), __('Received'), __('Completed'), __('Pending'), __('Overdue'),
                    __('Revenue'), __('Production value'), __('Outsource cost'), __('Completion %'), __('Avg lead time (d)'),
                ],
                $rows,
            ),
        ];
    }

    protected function scopeToRequest(OperationalRegisterScope $scope): Request
    {
        return Request::create('/', 'GET', $scope->toFilterArray());
    }

    /**
     * @param  list<string>  $headers
     * @param  list<array<string, mixed>>  $rows
     * @param  list<string>  $totals
     * @return array<string, mixed>
     */
    protected function tablePayload(string $title, array $headers, array $rows, array $totals = []): array
    {
        return [
            'title' => $title,
            'columns' => $headers,
            'rows' => $rows,
            'totals' => $totals,
        ];
    }

    /**
     * @param  list<string>  $amountKeys
     * @param  list<array<string, mixed>>  $rows
     * @return list<string>
     */
    protected function totalsRow(array $keys, array $rows, array $amountKeys): array
    {
        $totals = array_fill(0, count($keys), '');
        $totals[0] = __('Total');

        foreach ($amountKeys as $amountKey) {
            $index = array_search($amountKey, $keys, true);
            if ($index === false) {
                continue;
            }

            $sum = collect($rows)->sum(function (array $row) use ($index, $amountKey) {
                if (isset($row['values'][$index])) {
                    return $this->parseAmount($row['values'][$index]);
                }

                return $this->parseAmount($row[$amountKey] ?? '0');
            });
            $totals[$index] = number_format($sum, 2);
        }

        return $totals;
    }

    protected function parseAmount(mixed $value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        return (float) preg_replace('/[^\d.-]/', '', (string) $value);
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function salesOrderRegisterRows(OperationalRegisterScope $scope, ?User $user): array
    {
        if (! Schema::hasTable('sales_orders')) {
            return [];
        }

        $query = SalesOrder::query()
            ->with(['customer:id,company_name', 'creator:id,name', 'items:id,sales_order_id,item_name'])
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->whereDate('order_date', '>=', $scope->fromDate)
            ->whereDate('order_date', '<=', $scope->toDate)
            ->when($scope->customerId, fn ($q) => $q->where('customer_id', $scope->customerId));

        if ($scope->search !== '') {
            $like = '%'.addcslashes($scope->search, '%_\\').'%';
            $query->where(function ($q) use ($like) {
                $q->where('order_number', 'like', $like)
                    ->orWhereHas('customer', fn ($c) => $c->where('company_name', 'like', $like));
            });
        }

        return $query
            ->orderByDesc('order_date')
            ->orderByDesc('id')
            ->limit(500)
            ->get()
            ->map(function (SalesOrder $order) use ($user) {
            $financial = app(SalesOrderFinancialStatusService::class)->snapshot($order);
            $job = ProductionJobCard::query()->where('sales_order_id', $order->id)->first();
            $product = $order->items->first()?->item_name ?? '—';

            return [
                'sort_date' => $order->order_date?->format('Y-m-d') ?? '',
                'sort_at' => $order->created_at?->getTimestamp() ?? 0,
                'sort_id' => $order->id,
                'values' => [
                    $order->order_date?->format('Y-m-d') ?? '—',
                    $order->customer?->company_name ?? '—',
                    $order->order_number,
                    $product,
                    $job?->production_type?->value ? str_replace('_', ' ', ucfirst($job->production_type->value)) : '—',
                    '—',
                    number_format((float) $order->total_amount, 2),
                    $financial['financial_status_label'] ?? '—',
                    $order->status?->value ? str_replace('_', ' ', ucfirst($order->status->value)) : '—',
                    $order->creator?->name ?? '—',
                    $job?->status?->value ? str_replace('_', ' ', ucfirst($job->status->value)) : '—',
                    '—',
                    '—',
                    '—',
                    $order->notes ?? '—',
                ],
                'links' => array_filter([
                    1 => ($user?->can('view', $order->customer) && $order->customer)
                        ? route('admin.crm.customers.show', $order->customer) : null,
                    2 => ($user?->can('view', $order) && Route::has('admin.sales-orders.show'))
                        ? route('admin.sales-orders.show', $order) : null,
                ]),
                'amount_raw' => (float) $order->total_amount,
            ];
        })->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function posRegisterRows(OperationalRegisterScope $scope, ?User $user): array
    {
        if (! Schema::hasTable('pos_sales')) {
            return [];
        }

        return PosSale::query()
            ->with(['customer:id,company_name', 'cashier:id,name', 'items:id,pos_sale_id,description'])
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->where('status', PosSaleStatus::Paid)
            ->whereDate('sale_date', '>=', $scope->fromDate)
            ->whereDate('sale_date', '<=', $scope->toDate)
            ->orderByDesc('sale_date')
            ->orderByDesc('id')
            ->limit(500)
            ->get()
            ->map(function (PosSale $sale) use ($user) {
                return [
                    'sort_date' => $sale->sale_date?->format('Y-m-d') ?? '',
                    'sort_at' => $sale->created_at?->getTimestamp() ?? 0,
                    'sort_id' => $sale->id,
                    'values' => [
                        $sale->sale_date?->format('Y-m-d') ?? '—',
                        $sale->customer?->company_name ?? __('Walk-in'),
                        $sale->sale_number,
                        $sale->items->first()?->description ?? __('POS sale'),
                        __('Retail'),
                        __('POS'),
                        number_format((float) $sale->total_amount, 2),
                        __('Paid'),
                        __('Delivered'),
                        $sale->cashier?->name ?? '—',
                        '—',
                        '—',
                        '—',
                        '—',
                        $sale->notes ?? '—',
                    ],
                    'links' => array_filter([
                        2 => ($user?->can('view', $sale) && Route::has('admin.commercial.pos.show'))
                            ? route('admin.commercial.pos.show', $sale) : null,
                    ]),
                    'amount_raw' => (float) $sale->total_amount,
                ];
            })->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function paymentRegisterRows(OperationalRegisterScope $scope, ?User $user): array
    {
        if (! Schema::hasTable('customer_payments')) {
            return [];
        }

        return CustomerPayment::query()
            ->with(['customer:id,company_name', 'creator:id,name'])
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->where('status', CustomerPaymentStatus::Posted)
            ->whereDate('payment_date', '>=', $scope->fromDate)
            ->whereDate('payment_date', '<=', $scope->toDate)
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->limit(500)
            ->get()
            ->map(function (CustomerPayment $payment) use ($user) {
                return [
                    'sort_date' => $payment->payment_date?->format('Y-m-d') ?? '',
                    'sort_at' => $payment->created_at?->getTimestamp() ?? 0,
                    'sort_id' => $payment->id,
                    'values' => [
                        $payment->payment_date?->format('Y-m-d') ?? '—',
                        $payment->customer?->company_name ?? '—',
                        $payment->payment_number,
                        __('Customer payment'),
                        '—',
                        $payment->payment_method?->value ?? '—',
                        number_format((float) $payment->amount, 2),
                        __('Posted'),
                        '—',
                        $payment->creator?->name ?? '—',
                        '—',
                        '—',
                        '—',
                        '—',
                        $payment->notes ?? '—',
                    ],
                    'links' => array_filter([
                        1 => ($user?->can('view', $payment->customer) && $payment->customer)
                            ? route('admin.crm.customers.show', $payment->customer) : null,
                        2 => ($user?->can('view', $payment) && Route::has('admin.payments.show'))
                            ? route('admin.payments.show', $payment) : null,
                    ]),
                    'amount_raw' => (float) $payment->amount,
                ];
            })->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function invoiceRegisterRows(OperationalRegisterScope $scope, ?User $user): array
    {
        if (! Schema::hasTable('customer_invoices')) {
            return [];
        }

        return CustomerInvoice::query()
            ->with(['customer:id,company_name', 'salesOrder:id,order_number'])
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->whereNotIn('status', [CustomerInvoiceStatus::Cancelled, CustomerInvoiceStatus::Draft])
            ->whereDate('invoice_date', '>=', $scope->fromDate)
            ->whereDate('invoice_date', '<=', $scope->toDate)
            ->orderByDesc('invoice_date')
            ->orderByDesc('id')
            ->limit(500)
            ->get()
            ->map(function (CustomerInvoice $invoice) use ($user) {
                return [
                    'sort_date' => $invoice->invoice_date?->format('Y-m-d') ?? '',
                    'sort_at' => $invoice->created_at?->getTimestamp() ?? 0,
                    'sort_id' => $invoice->id,
                    'values' => [
                        $invoice->invoice_date?->format('Y-m-d') ?? '—',
                        $invoice->customer?->company_name ?? '—',
                        $invoice->invoice_number,
                        __('Invoice'),
                        '—',
                        '—',
                        number_format((float) $invoice->total_amount, 2),
                        $invoice->status?->label() ?? '—',
                        '—',
                        '—',
                        '—',
                        '—',
                        '—',
                        '—',
                        $invoice->notes ?? '—',
                    ],
                    'links' => array_filter([
                        1 => ($user?->can('view', $invoice->customer) && $invoice->customer)
                            ? route('admin.crm.customers.show', $invoice->customer) : null,
                        2 => ($user?->can('view', $invoice) && Route::has('admin.invoices.show'))
                            ? route('admin.invoices.show', $invoice) : null,
                    ]),
                    'amount_raw' => (float) $invoice->total_amount,
                ];
            })->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function machineUtilisationFromSessions(OperationalRegisterScope $scope): array
    {
        $sessions = ProductionSession::query()
            ->with(['jobCard.assignedMachine:id,asset_name'])
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->whereDate('started_at', '>=', $scope->fromDate)
            ->whereDate('started_at', '<=', $scope->toDate)
            ->get();

        $periodHours = max(1, Carbon::parse($scope->fromDate)->diffInHours(Carbon::parse($scope->toDate)->endOfDay()) + 1);

        return $sessions->groupBy(fn (ProductionSession $session) => $session->jobCard?->assignedMachine?->asset_name ?? __('Unassigned'))
            ->map(function (Collection $group, string $machine) use ($periodHours) {
                $activeMinutes = $group->sum(function (ProductionSession $session) {
                    if (! $session->started_at) {
                        return 0;
                    }
                    $end = $session->ended_at ?? now();

                    return $session->started_at->diffInMinutes($end);
                });
                $activeHours = round($activeMinutes / 60, 1);
                $idleHours = max(0, round($periodHours - $activeHours, 1));
                $completed = $group->filter(fn (ProductionSession $s) => $s->ended_at !== null)->count();
                $running = $group->filter(fn (ProductionSession $s) => $s->ended_at === null)->count();
                $avgCompletion = $completed > 0
                    ? round($group->filter(fn (ProductionSession $s) => $s->ended_at)->avg(fn (ProductionSession $s) => $s->started_at->diffInHours($s->ended_at)), 1)
                    : 0;
                $utilisation = round(min(100, ($activeHours / $periodHours) * 100), 1);

                return [
                    'values' => [
                        $machine,
                        (string) $running,
                        (string) $completed,
                        (string) $activeHours,
                        (string) $idleHours,
                        (string) $avgCompletion,
                        $utilisation.'%',
                    ],
                    'links' => [],
                ];
            })->values()->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function machineUtilisationFromReports(OperationalRegisterScope $scope): array
    {
        return collect($this->productionReports->machineUtilization($scope->intelligenceScope()))
            ->map(fn (array $row) => [
                'values' => [
                    (string) ($row[0] ?? '—'),
                    '—',
                    (string) ($row[2] ?? 0),
                    '—',
                    '—',
                    '—',
                    (string) ($row[3] ?? '0%'),
                ],
                'links' => [],
            ])->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function operatorProductivityRows(OperationalRegisterScope $scope): array
    {
        $query = ProductionQueue::query()
            ->with(['assignedOperator:id,name', 'jobCard:id,status,required_date,actual_end_date,production_type'])
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->whereNotNull('assigned_operator_id')
            ->whereDate('created_at', '>=', $scope->fromDate)
            ->whereDate('created_at', '<=', $scope->toDate);

        return $query->get()
            ->groupBy('assigned_operator_id')
            ->map(function (Collection $queues, int $operatorId) {
                $operator = $queues->first()?->assignedOperator;
                $completed = $queues->where('status', ProductionQueueStatus::Completed)->count();
                $running = $queues->where('status', ProductionQueueStatus::InProgress)->count();
                $delays = $queues->filter(fn (ProductionQueue $q) => $q->jobCard?->isDelayed())->count();
                $department = $queues->first()?->jobCard?->production_type?->value
                    ? str_replace('_', ' ', ucfirst($queues->first()->jobCard->production_type->value))
                    : '—';

                return [
                    'values' => [
                        $operator?->name ?? __('Unknown'),
                        (string) $completed,
                        (string) $running,
                        '—',
                        (string) $delays,
                        (string) $queues->count(),
                        $department,
                    ],
                    'links' => [],
                ];
            })->values()->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function departmentPerformanceRows(OperationalRegisterScope $scope): array
    {
        $departments = $this->departments->availableDepartments();
        $rows = [];

        foreach ($departments as $slug => $department) {
            $jobQuery = ProductionJobCard::query()
                ->where('production_job_cards.company_id', $scope->companyId)
                ->when($scope->branchId, fn ($q) => $q->where('production_job_cards.branch_id', $scope->branchId))
                ->whereDate('production_job_cards.created_at', '>=', $scope->fromDate)
                ->whereDate('production_job_cards.created_at', '<=', $scope->toDate);

            if ($department['job_statuses'] !== []) {
                $jobQuery->whereIn('production_job_cards.status', $department['job_statuses']);
            } elseif ($department['production_types'] !== []) {
                $jobQuery->whereIn('production_job_cards.production_type', $department['production_types']);
            }

            $received = (clone $jobQuery)->count();
            $completed = (clone $jobQuery)->where('production_job_cards.status', ProductionJobCardStatus::Completed)->count();
            $pending = (clone $jobQuery)->whereNotIn('production_job_cards.status', [
                ProductionJobCardStatus::Completed,
                ProductionJobCardStatus::Cancelled,
            ])->count();
            $overdue = (clone $jobQuery)
                ->whereNotNull('production_job_cards.required_date')
                ->whereDate('production_job_cards.required_date', '<', today())
                ->whereNotIn('production_job_cards.status', [ProductionJobCardStatus::Completed, ProductionJobCardStatus::Cancelled])
                ->count();

            $revenue = (float) (clone $jobQuery)
                ->join('sales_orders', 'sales_orders.id', '=', 'production_job_cards.sales_order_id')
                ->sum('sales_orders.total_amount');
            $outsourceCost = (float) (clone $jobQuery)
                ->sum(DB::raw('COALESCE(production_job_cards.outsource_actual_cost, production_job_cards.outsource_quoted_cost, 0)'));
            $completionPct = $received > 0 ? round(($completed / $received) * 100, 1) : 0;

            $rows[] = [
                'values' => [
                    $department['label'],
                    (string) $received,
                    (string) $completed,
                    (string) $pending,
                    (string) $overdue,
                    number_format($revenue, 2),
                    number_format($revenue, 2),
                    number_format($outsourceCost, 2),
                    $completionPct.'%',
                    '—',
                ],
                'links' => [
                    0 => Route::has('admin.production.queue.department')
                        ? route('admin.production.queue.department', $slug) : null,
                ],
            ];
        }

        return $rows;
    }

    protected function productionValueForScope(OperationalRegisterScope $scope): float
    {
        if (! Schema::hasTable('production_job_cards') || ! Schema::hasTable('sales_orders')) {
            return 0.0;
        }

        return (float) ProductionJobCard::query()
            ->where('production_job_cards.company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('production_job_cards.branch_id', $scope->branchId))
            ->whereDate('production_job_cards.updated_at', '>=', $scope->fromDate)
            ->whereDate('production_job_cards.updated_at', '<=', $scope->toDate)
            ->join('sales_orders', 'sales_orders.id', '=', 'production_job_cards.sales_order_id')
            ->sum('sales_orders.total_amount');
    }

    protected function averageMachineUtilisation(IntelligenceScope $scope): ?int
    {
        $rows = $this->productionReports->machineUtilization($scope);
        if ($rows === []) {
            return null;
        }

        $values = collect($rows)->map(fn (array $row) => (int) preg_replace('/\D/', '', (string) ($row[3] ?? '0')));

        return (int) round($values->avg(), 0);
    }

    protected function averageDepartmentUtilisation(IntelligenceScope $scope): ?int
    {
        $rows = $this->productionReports->departmentThroughput($scope);
        if ($rows === []) {
            return null;
        }

        return min(100, (int) round(collect($rows)->avg(fn (array $row) => (int) ($row[1] ?? 0)), 0));
    }

    protected function averageOperatorProductivity(IntelligenceScope $scope): ?int
    {
        $registerScope = new OperationalRegisterScope(
            companyId: $scope->companyId,
            branchId: $scope->branchId,
            fromDate: $scope->fromDate,
            toDate: $scope->toDate,
        );
        $rows = $this->operatorProductivityRows($registerScope);
        if ($rows === []) {
            return null;
        }

        return (int) round(collect($rows)->avg(fn (array $row) => (int) ($row['values'][1] ?? 0)), 0);
    }

    protected function hasSessionsTable(): bool
    {
        return Schema::hasTable('production_sessions');
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    protected function stripInternalKeys(array $row): array
    {
        unset($row['sort_date'], $row['sort_at'], $row['sort_id'], $row['amount_raw']);

        return $row;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, array<string, mixed>>  $rows
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    protected function sortRegisterRows(\Illuminate\Support\Collection $rows): \Illuminate\Support\Collection
    {
        return $rows
            ->sort(function (array $a, array $b): int {
                $dateCompare = strcmp($b['sort_date'] ?? '', $a['sort_date'] ?? '');
                if ($dateCompare !== 0) {
                    return $dateCompare;
                }

                $timeCompare = ($b['sort_at'] ?? 0) <=> ($a['sort_at'] ?? 0);
                if ($timeCompare !== 0) {
                    return $timeCompare;
                }

                return ($b['sort_id'] ?? 0) <=> ($a['sort_id'] ?? 0);
            })
            ->values();
    }

    /**
     * @return array{summary: list<array<string, mixed>>, table: array<string, mixed>}
     */
    public function customerSummaryRegister(OperationalRegisterScope $scope): array
    {
        $rows = ProductionJobCard::query()
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->whereDate('created_at', '>=', $scope->fromDate)
            ->whereDate('created_at', '<=', $scope->toDate)
            ->with('customer:id,company_name,customer_code')
            ->get()
            ->groupBy('customer_id')
            ->map(function ($jobs, $customerId) use ($scope) {
                $customer = $jobs->first()?->customer;
                $completed = $jobs->whereIn('status', [
                    ProductionJobCardStatus::Completed,
                    ProductionJobCardStatus::ReadyForDispatch,
                ])->count();

                return [
                    'values' => [
                        $customer?->company_name ?? __('Unknown'),
                        $customer?->customer_code ?? '—',
                        (string) $jobs->count(),
                        (string) $completed,
                        (string) $jobs->whereIn('status', [
                            ProductionJobCardStatus::Queued,
                            ProductionJobCardStatus::InProduction,
                            ProductionJobCardStatus::QualityCheck,
                        ])->count(),
                        (string) $jobs->filter(fn ($job) => $job->isDelayed())->count(),
                    ],
                    'sort_date' => $scope->toDate,
                    'sort_at' => 0,
                    'sort_id' => (int) $customerId,
                ];
            })
            ->values();

        $sorted = $this->sortRegisterRows($rows);

        return [
            'summary' => [
                ['label' => __('Customers'), 'value' => (string) $sorted->count(), 'icon' => 'users'],
                ['label' => __('Jobs'), 'value' => (string) $sorted->sum(fn ($row) => (int) ($row['values'][2] ?? 0)), 'icon' => 'collection'],
            ],
            'table' => $this->tablePayload(
                __('Customer Production Summary'),
                [__('Customer'), __('Code'), __('Jobs'), __('Completed'), __('Active'), __('Late')],
                $sorted->all(),
            ),
        ];
    }
}
