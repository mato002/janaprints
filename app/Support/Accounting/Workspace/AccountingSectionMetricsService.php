<?php

namespace App\Support\Accounting\Workspace;

use App\Enums\AccountingPeriodStatus;
use App\Enums\GlAccountStatus;
use App\Enums\CustomerInvoiceStatus;
use App\Enums\CustomerPaymentStatus;
use App\Enums\JournalStatus;
use App\Enums\SupplierBillStatus;
use App\Enums\SupplierPaymentStatus;
use App\Enums\TaxReturnStatus;
use App\Models\Accounting\AccountingPeriod;
use App\Models\Accounting\GlAccount;
use App\Models\Accounting\Journal;
use App\Models\Accounting\PostingRule;
use App\Models\Accounting\PostingTemplate;
use App\Models\Procurement\SupplierBill;
use App\Models\Procurement\SupplierPayment;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\CustomerPayment;
use App\Models\Tax\TaxReturn;
use App\Models\Tax\TaxTransaction;
use App\Support\Accounting\Dashboard\AccountingLedgerMetricsService;
use App\Support\Procurement\SupplierAgingService;
use App\Support\Sales\CustomerAgingService;
use App\Support\Tax\TaxReportService;

class AccountingSectionMetricsService
{
    public function __construct(
        protected AccountingLedgerMetricsService $ledgerMetrics,
        protected CustomerAgingService $customerAging,
        protected SupplierAgingService $supplierAging,
        protected TaxReportService $taxReports,
    ) {}

    /**
     * @param  array{company_id: int, branch_id?: int|null, period_id?: int|null}  $filters
     * @return array<string, mixed>
     */
    public function forSection(string $section, array $filters): array
    {
        return match ($section) {
            'general-ledger' => $this->generalLedger($filters),
            'receivables' => $this->receivables($filters),
            'payables' => $this->payables($filters),
            'tax' => $this->tax($filters),
            'setup' => $this->setup($filters),
            default => ['kpis' => [], 'widgets' => []],
        };
    }

    /**
     * @param  array{company_id: int, branch_id?: int|null, period_id?: int|null}  $filters
     * @return array<string, mixed>
     */
    protected function generalLedger(array $filters): array
    {
        $ledger = $this->ledgerMetrics->build($filters);
        $period = $ledger['period'];

        return [
            'kpis' => [
                ['label' => __('Revenue MTD'), 'value' => number_format($ledger['cards']['revenue_mtd'] ?? 0, 2), 'icon' => 'chart-bar'],
                ['label' => __('Expenses MTD'), 'value' => number_format($ledger['cards']['expenses_mtd'] ?? 0, 2), 'icon' => 'document-text'],
                ['label' => __('Net Profit MTD'), 'value' => number_format($ledger['cards']['net_profit_mtd'] ?? 0, 2), 'icon' => 'scale'],
            ],
            'widgets' => [
                'period_status' => $this->periodStatus($filters['company_id'], $period['period_id'] ?? null),
                'recent_journals' => $this->recentJournals($filters),
            ],
        ];
    }

    /**
     * @param  array{company_id: int, branch_id?: int|null}  $filters
     * @return array<string, mixed>
     */
    protected function receivables(array $filters): array
    {
        $asOf = now()->toDateString();
        $aging = $this->customerAging->build([
            'company_id' => $filters['company_id'],
            'as_of_date' => $asOf,
        ]);

        $totalAr = round(collect($aging['rows'] ?? [])->sum('total'), 2);
        $overdue = round(collect($aging['rows'] ?? [])->sum(fn ($row) => ($row['days_1_30'] ?? 0) + ($row['days_31_60'] ?? 0) + ($row['days_61_90'] ?? 0) + ($row['days_90_plus'] ?? 0)), 2);
        $overdueCustomers = collect($aging['rows'] ?? [])
            ->filter(fn ($row) => (($row['days_1_30'] ?? 0) + ($row['days_31_60'] ?? 0) + ($row['days_61_90'] ?? 0) + ($row['days_90_plus'] ?? 0)) > 0)
            ->count();

        $postedInvoices = CustomerInvoice::query()
            ->where('company_id', $filters['company_id'])
            ->where('status', CustomerInvoiceStatus::Posted)
            ->when(! empty($filters['branch_id']), fn ($q) => $q->where('branch_id', $filters['branch_id']))
            ->count();

        $postedPayments = CustomerPayment::query()
            ->where('company_id', $filters['company_id'])
            ->where('status', CustomerPaymentStatus::Posted)
            ->when(! empty($filters['branch_id']), fn ($q) => $q->where('branch_id', $filters['branch_id']))
            ->count();

        $collectionRate = ($postedInvoices + $postedPayments) > 0
            ? round(($postedPayments / max($postedInvoices, 1)) * 100, 1)
            : 0;

        return [
            'kpis' => [
                ['label' => __('AR Balance'), 'value' => number_format($totalAr, 2), 'icon' => 'users'],
                ['label' => __('Overdue Customers'), 'value' => (string) $overdueCustomers, 'icon' => 'exclamation'],
                ['label' => __('Collection Rate'), 'value' => $collectionRate.'%', 'icon' => 'chart-pie'],
            ],
            'widgets' => [
                'overdue_amount' => number_format($overdue, 2),
                'recent_invoices' => $this->recentInvoices($filters),
                'recent_payments' => $this->recentCustomerPayments($filters),
            ],
        ];
    }

    /**
     * @param  array{company_id: int, branch_id?: int|null}  $filters
     * @return array<string, mixed>
     */
    protected function payables(array $filters): array
    {
        $asOf = now()->toDateString();
        $aging = $this->supplierAging->build([
            'company_id' => $filters['company_id'],
            'as_of_date' => $asOf,
        ]);

        $totalAp = round(collect($aging['rows'] ?? [])->sum('total'), 2);
        $overdue = round(collect($aging['rows'] ?? [])->sum(fn ($row) => ($row['days_1_30'] ?? 0) + ($row['days_31_60'] ?? 0) + ($row['days_61_90'] ?? 0) + ($row['days_90_plus'] ?? 0)), 2);

        $upcoming = SupplierPayment::query()
            ->where('company_id', $filters['company_id'])
            ->where('status', SupplierPaymentStatus::Draft)
            ->when(! empty($filters['branch_id']), fn ($q) => $q->where('branch_id', $filters['branch_id']))
            ->orderBy('payment_date')
            ->limit(5)
            ->get(['id', 'payment_number', 'payment_date', 'amount', 'vendor_id'])
            ->map(fn (SupplierPayment $payment) => [
                'label' => $payment->payment_number,
                'date' => $payment->payment_date?->toDateString(),
                'amount' => round((float) $payment->amount, 2),
                'route' => route('admin.payables.payments.show', $payment),
            ])
            ->all();

        return [
            'kpis' => [
                ['label' => __('AP Balance'), 'value' => number_format($totalAp, 2), 'icon' => 'truck'],
                ['label' => __('Overdue Bills'), 'value' => number_format($overdue, 2), 'icon' => 'exclamation'],
                ['label' => __('Upcoming Payments'), 'value' => (string) count($upcoming), 'icon' => 'credit-card'],
            ],
            'widgets' => [
                'upcoming_payments' => $upcoming,
                'recent_transactions' => $this->recentPayablesTransactions($filters),
            ],
        ];
    }

    /**
     * @param  array{company_id: int, branch_id?: int|null}  $filters
     * @return array<string, mixed>
     */
    protected function tax(array $filters): array
    {
        $period = AccountingPeriod::query()
            ->where('company_id', $filters['company_id'])
            ->where('is_current', true)
            ->first();

        $from = $period?->start_date?->toDateString() ?? now()->startOfMonth()->toDateString();
        $to = $period?->end_date?->toDateString() ?? now()->toDateString();

        $vat = $this->taxReports->vatSummary([
            'company_id' => $filters['company_id'],
            'from_date' => $from,
            'to_date' => $to,
        ]);

        $draftReturns = TaxReturn::query()
            ->where('company_id', $filters['company_id'])
            ->where('status', TaxReturnStatus::Draft)
            ->count();

        $filedReturns = TaxReturn::query()
            ->where('company_id', $filters['company_id'])
            ->where('status', TaxReturnStatus::Filed)
            ->count();

        return [
            'kpis' => [
                ['label' => __('Current VAT Liability'), 'value' => number_format($vat['net_liability'] ?? 0, 2), 'icon' => 'receipt-tax'],
                ['label' => __('Draft Returns'), 'value' => (string) $draftReturns, 'icon' => 'document-text'],
                ['label' => __('Filed Returns'), 'value' => (string) $filedReturns, 'icon' => 'check-circle'],
            ],
            'widgets' => [
                'recent_activity' => TaxTransaction::query()
                    ->where('company_id', $filters['company_id'])
                    ->orderByDesc('document_date')
                    ->limit(8)
                    ->get(['document_date', 'document_number', 'tax_amount', 'direction'])
                    ->map(fn ($row) => [
                        'date' => $row->document_date->toDateString(),
                        'label' => $row->document_number,
                        'amount' => round((float) $row->tax_amount, 2),
                        'direction' => $row->direction->label(),
                    ])
                    ->all(),
                'upcoming_filings' => TaxReturn::query()
                    ->where('company_id', $filters['company_id'])
                    ->where('status', TaxReturnStatus::Draft)
                    ->orderByDesc('updated_at')
                    ->limit(5)
                    ->get(['id', 'return_number', 'status', 'net_liability'])
                    ->map(fn (TaxReturn $return) => [
                        'label' => $return->return_number,
                        'status' => $return->status->label(),
                        'amount' => round((float) $return->net_liability, 2),
                        'route' => route('admin.tax.returns.show', $return),
                    ])
                    ->all(),
            ],
        ];
    }

    /**
     * @param  array{company_id: int, branch_id?: int|null}  $filters
     * @return array<string, mixed>
     */
    protected function setup(array $filters): array
    {
        $companyId = $filters['company_id'];
        $activeAccounts = GlAccount::query()
            ->where('company_id', $companyId)
            ->where('status', GlAccountStatus::Active)
            ->count();
        $totalAccounts = GlAccount::query()->where('company_id', $companyId)->count();
        $period = AccountingPeriod::query()->where('company_id', $companyId)->where('is_current', true)->first();
        $rules = PostingRule::query()->where('company_id', $companyId)->where('is_active', true)->count();
        $templates = PostingTemplate::query()->where('company_id', $companyId)->count();

        return [
            'kpis' => [
                ['label' => __('Active Accounts'), 'value' => "{$activeAccounts} / {$totalAccounts}", 'icon' => 'book-open'],
                ['label' => __('Current Fiscal Period'), 'value' => $period?->code ?? '—', 'icon' => 'calendar'],
                ['label' => __('Posting Rules'), 'value' => (string) $rules, 'icon' => 'cog'],
            ],
            'widgets' => [
                'chart_status' => [
                    'active' => $activeAccounts,
                    'total' => $totalAccounts,
                    'templates' => $templates,
                ],
                'period' => $period ? [
                    'code' => $period->code,
                    'name' => $period->name,
                    'status' => $period->status->label(),
                    'route' => route('admin.accounting.periods.index'),
                ] : null,
                'posting_rule_count' => $rules,
            ],
        ];
    }

    /**
     * @param  array{company_id: int, branch_id?: int|null}  $filters
     * @return list<array<string, mixed>>
     */
    protected function recentJournals(array $filters): array
    {
        return Journal::query()
            ->where('company_id', $filters['company_id'])
            ->when(! empty($filters['branch_id']), fn ($q) => $q->where(function ($inner) use ($filters) {
                $inner->whereNull('branch_id')->orWhere('branch_id', $filters['branch_id']);
            }))
            ->orderByDesc('journal_date')
            ->limit(8)
            ->get(['id', 'journal_number', 'journal_date', 'status', 'total_debit'])
            ->map(fn (Journal $journal) => [
                'id' => $journal->id,
                'journal_number' => $journal->journal_number,
                'journal_date' => $journal->journal_date->toDateString(),
                'status_label' => $journal->status->label(),
                'amount' => round((float) $journal->total_debit, 2),
            ])
            ->all();
    }

    /**
     * @param  array{company_id: int, branch_id?: int|null}  $filters
     * @return list<array<string, mixed>>
     */
    protected function recentInvoices(array $filters): array
    {
        return CustomerInvoice::query()
            ->where('company_id', $filters['company_id'])
            ->when(! empty($filters['branch_id']), fn ($q) => $q->where('branch_id', $filters['branch_id']))
            ->with('customer:id,company_name')
            ->orderByDesc('invoice_date')
            ->limit(8)
            ->get(['id', 'invoice_number', 'invoice_date', 'total_amount', 'status', 'customer_id'])
            ->map(fn (CustomerInvoice $invoice) => [
                'label' => $invoice->invoice_number,
                'customer' => $invoice->customer?->company_name,
                'date' => $invoice->invoice_date->toDateString(),
                'amount' => round((float) $invoice->total_amount, 2),
                'status' => $invoice->status->label(),
                'route' => route('admin.invoices.show', $invoice),
            ])
            ->all();
    }

    /**
     * @param  array{company_id: int, branch_id?: int|null}  $filters
     * @return list<array<string, mixed>>
     */
    protected function recentCustomerPayments(array $filters): array
    {
        return CustomerPayment::query()
            ->where('company_id', $filters['company_id'])
            ->when(! empty($filters['branch_id']), fn ($q) => $q->where('branch_id', $filters['branch_id']))
            ->with('customer:id,company_name')
            ->orderByDesc('payment_date')
            ->limit(8)
            ->get(['id', 'payment_number', 'payment_date', 'amount', 'status', 'customer_id'])
            ->map(fn (CustomerPayment $payment) => [
                'label' => $payment->payment_number,
                'customer' => $payment->customer?->company_name,
                'date' => $payment->payment_date->toDateString(),
                'amount' => round((float) $payment->amount, 2),
                'status' => $payment->status->label(),
                'route' => route('admin.payments.show', $payment),
            ])
            ->all();
    }

    /**
     * @param  array{company_id: int, branch_id?: int|null}  $filters
     * @return list<array<string, mixed>>
     */
    protected function recentPayablesTransactions(array $filters): array
    {
        $bills = SupplierBill::query()
            ->where('company_id', $filters['company_id'])
            ->when(! empty($filters['branch_id']), fn ($q) => $q->where('branch_id', $filters['branch_id']))
            ->orderByDesc('bill_date')
            ->limit(5)
            ->get(['id', 'bill_number', 'bill_date', 'total_amount', 'status'])
            ->map(fn (SupplierBill $bill) => [
                'type' => __('Bill'),
                'label' => $bill->bill_number,
                'date' => $bill->bill_date->toDateString(),
                'amount' => round((float) $bill->total_amount, 2),
                'status' => $bill->status->label(),
                'route' => route('admin.payables.bills.show', $bill),
            ]);

        $payments = SupplierPayment::query()
            ->where('company_id', $filters['company_id'])
            ->when(! empty($filters['branch_id']), fn ($q) => $q->where('branch_id', $filters['branch_id']))
            ->orderByDesc('payment_date')
            ->limit(5)
            ->get(['id', 'payment_number', 'payment_date', 'amount', 'status'])
            ->map(fn (SupplierPayment $payment) => [
                'type' => __('Payment'),
                'label' => $payment->payment_number,
                'date' => $payment->payment_date?->toDateString(),
                'amount' => round((float) $payment->amount, 2),
                'status' => $payment->status->label(),
                'route' => route('admin.payables.payments.show', $payment),
            ]);

        return $bills->merge($payments)->sortByDesc('date')->take(8)->values()->all();
    }

    /**
     * @return array{code: string, name: string, status: string, status_label: string, can_post: bool}
     */
    protected function periodStatus(int $companyId, ?int $periodId): array
    {
        $period = $periodId
            ? AccountingPeriod::query()->where('company_id', $companyId)->find($periodId)
            : AccountingPeriod::query()->where('company_id', $companyId)->where('is_current', true)->first();

        if (! $period) {
            return [
                'code' => '—',
                'name' => __('No period'),
                'status' => AccountingPeriodStatus::Open->value,
                'status_label' => AccountingPeriodStatus::Open->label(),
                'can_post' => true,
            ];
        }

        return [
            'code' => $period->code,
            'name' => $period->name,
            'status' => $period->status->value,
            'status_label' => $period->status->label(),
            'can_post' => $period->status->canPost(),
        ];
    }
}
