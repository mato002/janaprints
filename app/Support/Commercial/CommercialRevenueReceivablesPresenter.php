<?php

namespace App\Support\Commercial;

use App\Enums\CustomerInvoiceStatus;
use App\Enums\CustomerInvoiceType;
use App\Enums\CustomerPaymentStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Crm\Customer;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\CustomerPayment;
use App\Models\Sales\SalesOrder;
use App\Support\Reports\IntelligenceAggregateQueries;
use App\Support\Reports\IntelligenceScope;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class CommercialRevenueReceivablesPresenter
{
    private const TOP_DEBTORS_LIMIT = 10;

    public function __construct(
        protected IntelligenceAggregateQueries $intelligence,
    ) {}

    /**
     * @return array<string, mixed>|null
     */
    public function build(): ?array
    {
        $user = auth()->user();

        if (! $user?->can('invoices.view') && ! $user?->can('sales_orders.view') && ! $user?->can('payments.view')) {
            return null;
        }

        $scope = $this->scope();

        return [
            'revenue_strip' => $this->revenueStrip($scope),
            'invoice_health' => $this->invoiceHealth($scope),
            'receivable_aging' => $this->receivableAging($scope),
            'top_debtors' => $this->topDebtors($scope),
            'payment_visibility' => $this->paymentVisibility(),
            'deposit_tracking' => $this->depositTracking($scope),
            'sales_vs_collections' => $this->salesVsCollections($scope),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function depositTracking(IntelligenceScope $scope): array
    {
        if (! auth()->user()?->can('sales_orders.view')) {
            return ['rows' => []];
        }

        $orders = SalesOrder::query()
            ->forTenant()
            ->where('required_deposit_amount', '>', 0)
            ->with('customer:id,company_name')
            ->orderByDesc('order_date')
            ->limit(50)
            ->get(['id', 'order_number', 'customer_id', 'required_deposit_amount', 'deposit_invoiced_amount', 'deposit_paid_amount']);

        return [
            'rows' => $orders->map(fn (SalesOrder $order) => [
                $order->order_number,
                $order->customer?->company_name ?? '—',
                number_format((float) $order->required_deposit_amount, 2),
                number_format((float) $order->deposit_invoiced_amount, 2),
                number_format((float) $order->deposit_paid_amount, 2),
                number_format(max(0, (float) $order->required_deposit_amount - (float) $order->deposit_paid_amount), 2),
            ])->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function salesVsCollections(IntelligenceScope $scope): array
    {
        $invoiced = (float) CustomerInvoice::query()
            ->forTenant()
            ->where('status', CustomerInvoiceStatus::Posted)
            ->whereNot('invoice_type', CustomerInvoiceType::CreditNote)
            ->whereDate('invoice_date', '>=', $scope->fromDate)
            ->whereDate('invoice_date', '<=', $scope->toDate)
            ->sum('total_amount');

        $collected = (float) CustomerPayment::query()
            ->forTenant()
            ->where('status', CustomerPaymentStatus::Posted)
            ->whereDate('payment_date', '>=', $scope->fromDate)
            ->whereDate('payment_date', '<=', $scope->toDate)
            ->sum('amount');

        return [
            'invoiced' => $this->money($invoiced),
            'collected' => $this->money($collected),
            'collection_rate' => $invoiced > 0 ? round(($collected / $invoiced) * 100, 1).'%' : '—',
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function revenueStrip(IntelligenceScope $scope): array
    {
        $today = now()->toDateString();

        $periods = [
            ['key' => 'today', 'label' => __('Revenue Today'), 'from' => $today, 'to' => $today, 'icon' => 'currency-dollar'],
            ['key' => 'week', 'label' => __('Revenue This Week'), 'from' => now()->startOfWeek()->toDateString(), 'to' => $today, 'icon' => 'chart-bar'],
            ['key' => 'month', 'label' => __('Revenue This Month'), 'from' => now()->startOfMonth()->toDateString(), 'to' => $today, 'icon' => 'chart-pie'],
            ['key' => 'ytd', 'label' => __('Revenue YTD'), 'from' => now()->startOfYear()->toDateString(), 'to' => $today, 'icon' => 'trending-up'],
        ];

        return array_map(fn (array $period) => [
            'key' => $period['key'],
            'label' => $period['label'],
            'value' => $this->money($this->sumRevenueBetween($scope, $period['from'], $period['to'])),
            'icon' => $period['icon'],
            'href' => Route::has('admin.commercial.reports.sales.index') ? route('admin.commercial.reports.sales.index') : null,
        ], $periods);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function invoiceHealth(IntelligenceScope $scope): array
    {
        if (! auth()->user()?->can('invoices.view')) {
            return [];
        }

        $base = CustomerInvoice::query()->forTenant()
            ->whereIn('status', [CustomerInvoiceStatus::Approved, CustomerInvoiceStatus::Posted]);

        $issued = (clone $base)->count();
        $paid = (clone $base)->where('balance_due', '<=', 0)->where('amount_paid', '>', 0)->count();
        $outstanding = (clone $base)->where('balance_due', '>', 0)->count();
        $overdue = (clone $base)
            ->where('balance_due', '>', 0)
            ->whereDate('due_date', '<', now()->toDateString())
            ->count();

        $outstandingAmount = (float) (clone $base)->where('balance_due', '>', 0)->sum('balance_due');
        $overdueAmount = (float) (clone $base)
            ->where('balance_due', '>', 0)
            ->whereDate('due_date', '<', now()->toDateString())
            ->sum('balance_due');

        return [
            ['key' => 'issued', 'label' => __('Invoices Issued'), 'count' => $issued, 'amount' => null, 'icon' => 'document-text'],
            ['key' => 'paid', 'label' => __('Invoices Paid'), 'count' => $paid, 'amount' => null, 'icon' => 'check-circle'],
            ['key' => 'outstanding', 'label' => __('Invoices Outstanding'), 'count' => $outstanding, 'amount' => $this->money($outstandingAmount), 'icon' => 'clock'],
            ['key' => 'overdue', 'label' => __('Overdue Invoices'), 'count' => $overdue, 'amount' => $this->money($overdueAmount), 'icon' => 'exclamation'],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function receivableAging(IntelligenceScope $scope): array
    {
        if (! auth()->user()?->can('invoices.view')) {
            return [];
        }

        $today = now()->startOfDay();
        $buckets = [
            '0_30' => ['label' => __('0–30'), 'min' => 0, 'max' => 30, 'amount' => 0.0],
            '31_60' => ['label' => __('31–60'), 'min' => 31, 'max' => 60, 'amount' => 0.0],
            '61_90' => ['label' => __('61–90'), 'min' => 61, 'max' => 90, 'amount' => 0.0],
            '90_plus' => ['label' => __('90+'), 'min' => 91, 'max' => null, 'amount' => 0.0],
        ];

        $rows = CustomerInvoice::query()->forTenant()
            ->whereIn('status', [CustomerInvoiceStatus::Approved, CustomerInvoiceStatus::Posted])
            ->where('balance_due', '>', 0)
            ->get(['balance_due', 'due_date', 'invoice_date']);

        foreach ($rows as $invoice) {
            $anchor = $invoice->due_date ?? $invoice->invoice_date;
            $days = $anchor ? max(0, (int) Carbon::parse($anchor)->startOfDay()->diffInDays($today)) : 0;

            $key = match (true) {
                $days <= 30 => '0_30',
                $days <= 60 => '31_60',
                $days <= 90 => '61_90',
                default => '90_plus',
            };

            $buckets[$key]['amount'] += (float) $invoice->balance_due;
        }

        return collect($buckets)->map(fn (array $bucket, string $key) => [
            'key' => $key,
            'label' => $bucket['label'],
            'amount' => $this->money($bucket['amount']),
            'amount_raw' => $bucket['amount'],
        ])->values()->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function topDebtors(IntelligenceScope $scope): array
    {
        if (! auth()->user()?->can('invoices.view')) {
            return [];
        }

        $today = now()->startOfDay();

        $rows = CustomerInvoice::query()->forTenant()
            ->whereIn('status', [CustomerInvoiceStatus::Approved, CustomerInvoiceStatus::Posted])
            ->where('balance_due', '>', 0)
            ->select(
                'customer_id',
                DB::raw('SUM(balance_due) as outstanding'),
                DB::raw('MIN(COALESCE(due_date, invoice_date)) as oldest_anchor'),
            )
            ->groupBy('customer_id')
            ->orderByDesc('outstanding')
            ->limit(self::TOP_DEBTORS_LIMIT)
            ->get();

        if ($rows->isEmpty()) {
            return [];
        }

        $customerIds = $rows->pluck('customer_id')->filter()->all();
        $customers = Customer::query()->whereIn('id', $customerIds)->get(['id', 'public_id', 'company_name'])->keyBy('id');

        $lastPayments = CustomerPayment::query()->forTenant()
            ->where('status', CustomerPaymentStatus::Posted)
            ->whereIn('customer_id', $customerIds)
            ->select('customer_id', DB::raw('MAX(payment_date) as last_payment'))
            ->groupBy('customer_id')
            ->pluck('last_payment', 'customer_id');

        return $rows->map(function ($row) use ($customers, $lastPayments, $today) {
            $oldest = $row->oldest_anchor ? Carbon::parse($row->oldest_anchor)->startOfDay() : null;
            $daysOutstanding = $oldest ? (int) $oldest->diffInDays($today) : 0;
            $lastPayment = $lastPayments[$row->customer_id] ?? null;
            $customer = $customers[$row->customer_id] ?? null;

            return [
                'customer' => $customer?->company_name ?? '—',
                'customer_url' => $customer && Route::has('admin.crm.customers.show')
                    ? route('admin.crm.customers.show', $customer)
                    : null,
                'outstanding' => $this->money((float) $row->outstanding),
                'last_payment' => $lastPayment ? Carbon::parse($lastPayment)->format('d M Y') : '—',
                'days_outstanding' => $daysOutstanding,
                'days_label' => $daysOutstanding === 1
                    ? __('1 day')
                    : __(':count days', ['count' => $daysOutstanding]),
            ];
        })->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function paymentVisibility(): array
    {
        if (! auth()->user()?->can('sales_orders.view')) {
            return ['summary' => [], 'orders' => []];
        }

        $orders = SalesOrder::query()->forTenant()
            ->whereNotIn('status', [SalesOrderStatus::Draft, SalesOrderStatus::Cancelled])
            ->with(['customer:id,company_name', 'invoices'])
            ->orderByDesc('order_date')
            ->limit(10)
            ->get();

        $summary = [
            'paid' => 0,
            'partially_paid' => 0,
            'unpaid' => 0,
            'uninvoiced' => 0,
        ];

        $presentedOrders = [];

        foreach ($orders as $order) {
            $payment = SalesOrderPaymentVisibility::resolve($order);
            $summary[$payment['status']] = ($summary[$payment['status']] ?? 0) + 1;

            if (in_array($payment['status'], ['paid', 'partially_paid', 'unpaid'], true)) {
                $presentedOrders[] = [
                    'reference' => $order->order_number,
                    'customer' => $order->customer?->company_name ?? '—',
                    'status' => $payment['status'],
                    'label' => $payment['label'],
                    'variant' => $payment['variant'],
                    'amount_paid' => number_format($payment['amount_paid'], 2),
                    'amount_outstanding' => number_format($payment['amount_outstanding'], 2),
                    'url' => Route::has('admin.sales-orders.show') ? route('admin.sales-orders.show', $order) : null,
                ];
            }
        }

        $allOrders = SalesOrder::query()->forTenant()
            ->whereNotIn('status', [SalesOrderStatus::Draft, SalesOrderStatus::Cancelled])
            ->with('invoices')
            ->get();

        $fullSummary = [
            'paid' => 0,
            'partially_paid' => 0,
            'unpaid' => 0,
            'uninvoiced' => 0,
        ];

        foreach ($allOrders as $order) {
            $status = SalesOrderPaymentVisibility::resolve($order)['status'];
            $fullSummary[$status] = ($fullSummary[$status] ?? 0) + 1;
        }

        return [
            'summary' => [
                ['key' => 'paid', 'label' => __('Paid'), 'count' => $fullSummary['paid'], 'variant' => 'success'],
                ['key' => 'partially_paid', 'label' => __('Partially Paid'), 'count' => $fullSummary['partially_paid'], 'variant' => 'warning'],
                ['key' => 'unpaid', 'label' => __('Unpaid'), 'count' => $fullSummary['unpaid'], 'variant' => 'danger'],
            ],
            'orders' => array_slice(
                collect($presentedOrders)->sortByDesc(fn (array $row) => $row['status'] === 'unpaid' ? 3 : ($row['status'] === 'partially_paid' ? 2 : 1))->values()->all(),
                0,
                10,
            ),
        ];
    }

    protected function sumRevenueBetween(IntelligenceScope $scope, string $from, string $to): float
    {
        if ($this->intelligence->hasTable('customer_invoices')) {
            return (float) CustomerInvoice::query()->forTenant()
                ->where('status', CustomerInvoiceStatus::Posted)
                ->whereDate('invoice_date', '>=', $from)
                ->whereDate('invoice_date', '<=', $to)
                ->sum('total_amount');
        }

        $periodScope = new IntelligenceScope(
            companyId: $scope->companyId,
            branchId: $scope->branchId,
            fromDate: $from,
            toDate: $to,
        );

        return $this->intelligence->sumSalesOrderValue($periodScope, true);
    }

    protected function scope(): IntelligenceScope
    {
        return new IntelligenceScope(
            companyId: (int) (tenant()->companyId() ?? auth()->user()?->company_id),
            branchId: tenant()->branchId(),
            fromDate: now()->startOfMonth()->toDateString(),
            toDate: now()->toDateString(),
        );
    }

    protected function money(float $amount): string
    {
        return 'KES '.number_format($amount, 0);
    }
}
