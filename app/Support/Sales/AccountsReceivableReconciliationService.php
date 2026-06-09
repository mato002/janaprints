<?php

namespace App\Support\Sales;

use App\Enums\ArReconciliationCheckStatus;
use App\Enums\ArReconciliationExceptionType;
use App\Enums\CustomerDepositApplicationStatus;
use App\Enums\CustomerInvoiceStatus;
use App\Enums\CustomerInvoiceType;
use App\Enums\CustomerPaymentStatus;
use App\Enums\JournalStatus;
use App\Enums\NormalBalance;
use App\Enums\PostingEventCode;
use App\Models\Accounting\AccountingPeriod;
use App\Models\Crm\Customer;
use App\Models\Sales\CustomerDepositApplication;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\CustomerPayment;
use App\Models\Sales\CustomerPaymentAllocation;
use App\Support\Accounting\LedgerSignedBalance;
use App\Support\Accounting\Reports\PostedJournalQuery;
use Illuminate\Validation\ValidationException;

class AccountsReceivableReconciliationService
{
    public function __construct(
        protected CustomerLedgerService $ledger,
        protected ReceivablesBranchScope $branchScope,
    ) {}

    /**
     * @param  array{
     *     company_id: int,
     *     branch_id?: int|null,
     *     as_of_date?: string,
     *     period_id?: int|null,
     *     tolerance?: float
     * }  $filters
     * @return array<string, mixed>
     */
    public function build(array $filters): array
    {
        $companyId = (int) $filters['company_id'];
        $asOfDate = $filters['as_of_date'] ?? now()->toDateString();
        $tolerance = (float) ($filters['tolerance'] ?? 0.02);

        $periodId = $filters['period_id'] ?? null;
        $branchId = $this->branchScope->resolve($filters['branch_id'] ?? null);

        $operationalAr = $this->operationalOpenAr($companyId, $asOfDate, $branchId);
        $glAr = $this->glTradeReceivablesBalance($companyId, $asOfDate, $branchId);
        $aggregateLedger = $this->aggregateCustomerLedgerClosing($companyId, $asOfDate, $branchId);
        $depositCreditRemaining = $this->depositCreditRemaining($companyId, $branchId);
        $paymentTotal = $this->postedPaymentTotal($companyId, $asOfDate, $periodId, $branchId);
        $glReceiptDebits = $this->glCustomerPaymentReceiptDebits($companyId, $asOfDate, $periodId, $branchId);

        $checks = [
            $this->checkRow(
                'subledger_vs_gl',
                __('Customer subledger vs GL account 1300'),
                $operationalAr,
                $glAr,
                $tolerance,
            ),
            $this->checkRow(
                'invoice_vs_ledger',
                __('Invoice balances vs customer ledger'),
                $operationalAr,
                round($aggregateLedger + $depositCreditRemaining, 2),
                $tolerance,
            ),
            $this->checkRow(
                'payments_vs_cash',
                __('Customer payments vs cash receipts'),
                $paymentTotal,
                $glReceiptDebits,
                $tolerance,
            ),
        ];

        $exceptions = $this->detectExceptions($companyId, $branchId);

        return [
            'company_id' => $companyId,
            'as_of_date' => $asOfDate,
            'period_id' => $filters['period_id'] ?? null,
            'branch_id' => $branchId,
            'tolerance' => $tolerance,
            'checks' => $checks,
            'exceptions' => $exceptions,
            'is_resolved' => $this->isResolved($checks, $exceptions),
            'summary' => [
                'operational_ar' => $operationalAr,
                'gl_ar' => $glAr,
                'aggregate_ledger' => $aggregateLedger,
                'deposit_credit_remaining' => $depositCreditRemaining,
                'posted_payments' => $paymentTotal,
                'gl_receipt_debits' => $glReceiptDebits,
            ],
        ];
    }

    public function buildForPeriod(AccountingPeriod $period, ?int $branchId = null): array
    {
        return $this->build([
            'company_id' => $period->company_id,
            'branch_id' => $branchId,
            'as_of_date' => $period->end_date->toDateString(),
            'period_id' => $period->id,
        ]);
    }

    /**
     * @param  array<string, mixed>|null  $report
     */
    public function assertReconciledForPeriod(AccountingPeriod $period, ?array $report = null): void
    {
        $report ??= $this->buildForPeriod($period);

        if ($report['is_resolved']) {
            return;
        }

        $varianceChecks = collect($report['checks'])
            ->filter(fn (array $row) => $row['status'] === ArReconciliationCheckStatus::Variance->value)
            ->pluck('label')
            ->all();

        $blocking = collect($report['exceptions'])
            ->filter(fn (array $row) => $row['severity'] === 'critical')
            ->count();

        throw ValidationException::withMessages([
            'ar_reconciliation' => __('Accounts receivable reconciliation is unresolved. Variance checks: :checks. Blocking exceptions: :count.', [
                'checks' => $varianceChecks === [] ? __('none') : implode(', ', $varianceChecks),
                'count' => $blocking,
            ]),
        ]);
    }

    /**
     * @param  list<array<string, mixed>>  $checks
     * @param  list<array<string, mixed>>  $exceptions
     */
    public function isResolved(array $checks, array $exceptions): bool
    {
        foreach ($checks as $check) {
            if ($check['status'] !== ArReconciliationCheckStatus::Matched->value) {
                return false;
            }
        }

        foreach ($exceptions as $exception) {
            if ($exception['severity'] === 'critical') {
                return false;
            }
        }

        return true;
    }

    protected function operationalOpenAr(int $companyId, ?string $asOfDate = null, ?int $branchId = null): float
    {
        $query = CustomerInvoice::query()
            ->where('company_id', $companyId)
            ->where('status', CustomerInvoiceStatus::Posted)
            ->whereNot('invoice_type', CustomerInvoiceType::CreditNote);

        if ($asOfDate) {
            $query->whereDate('invoice_date', '<=', $asOfDate);
        }

        if ($branchId !== null) {
            $query->where('branch_id', $branchId);
        }

        return round((float) $query->sum('balance_due'), 2);
    }

    protected function glTradeReceivablesBalance(int $companyId, string $asOfDate, ?int $branchId): float
    {
        $filters = array_filter([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'as_of_date' => $asOfDate,
        ]);

        $rows = PostedJournalQuery::aggregateByAccount($filters)->get();
        $row = $rows->firstWhere('account_code', '1300');

        if (! $row) {
            return 0.0;
        }

        return round(LedgerSignedBalance::balanceSheetAmount(
            (float) $row->total_debit,
            (float) $row->total_credit,
            NormalBalance::from($row->normal_balance),
        ), 2);
    }

    protected function aggregateCustomerLedgerClosing(int $companyId, string $asOfDate, ?int $branchId = null): float
    {
        $total = 0.0;

        $customerQuery = Customer::query()->where('company_id', $companyId);

        if ($branchId !== null) {
            $customerQuery->where('branch_id', $branchId);
        }

        $customerQuery->pluck('id')
            ->each(function (int $customerId) use (&$total, $asOfDate, $branchId) {
                $total += $this->ledger->closingBalance($customerId, $asOfDate, $branchId);
            });

        return round($total, 2);
    }

    protected function depositCreditRemaining(int $companyId, ?int $branchId = null): float
    {
        $query = CustomerPayment::query()
            ->where('company_id', $companyId)
            ->where('is_deposit', true)
            ->where('status', CustomerPaymentStatus::Posted);

        if ($branchId !== null) {
            $query->where('branch_id', $branchId);
        }

        return round((float) $query->sum('credit_remaining'), 2);
    }

    protected function postedPaymentTotal(int $companyId, string $asOfDate, ?int $periodId, ?int $branchId = null): float
    {
        $query = CustomerPayment::query()
            ->where('company_id', $companyId)
            ->where('status', CustomerPaymentStatus::Posted)
            ->whereDate('payment_date', '<=', $asOfDate);

        if ($branchId !== null) {
            $query->where('branch_id', $branchId);
        }

        if ($periodId) {
            $period = AccountingPeriod::query()->find($periodId);
            if ($period) {
                $query->whereDate('payment_date', '>=', $period->start_date)
                    ->whereDate('payment_date', '<=', $period->end_date);
            }
        }

        return round((float) $query->sum('amount'), 2);
    }

    protected function glCustomerPaymentReceiptDebits(int $companyId, string $asOfDate, ?int $periodId, ?int $branchId = null): float
    {
        $receiptCodes = collect(config('posting_account_keys', []))
            ->only(['cash', 'bank', 'mpesa_clearing', 'card_clearing'])
            ->pluck('default_code')
            ->unique()
            ->values()
            ->all();

        $filters = array_filter([
            'company_id' => $companyId,
            'as_of_date' => $asOfDate,
            'period_id' => $periodId,
            'branch_id' => $branchId,
        ]);

        if ($periodId) {
            $period = AccountingPeriod::query()->find($periodId);
            if ($period) {
                $filters['from_date'] = $period->start_date->toDateString();
                $filters['to_date'] = $period->end_date->toDateString();
                unset($filters['as_of_date']);
            }
        }

        $amount = (float) PostedJournalQuery::applyFilters(PostedJournalQuery::base($companyId), $filters)
            ->whereIn('gl_accounts.code', $receiptCodes)
            ->where('journals.source_type', 'customer_payment')
            ->where('journals.posting_event', PostingEventCode::PaymentReceived->value)
            ->where('journals.status', JournalStatus::Posted->value)
            ->sum('journal_lines.debit');

        return round($amount, 2);
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function detectExceptions(int $companyId, ?int $branchId = null): array
    {
        $exceptions = [];

        $invoiceQuery = CustomerInvoice::query()->where('company_id', $companyId);
        $paymentQuery = CustomerPayment::query()->where('company_id', $companyId);
        $applicationQuery = CustomerDepositApplication::query()->where('company_id', $companyId);

        if ($branchId !== null) {
            $invoiceQuery->where('branch_id', $branchId);
            $paymentQuery->where('branch_id', $branchId);
            $applicationQuery->where('branch_id', $branchId);
        }

        (clone $invoiceQuery)
            ->where('status', CustomerInvoiceStatus::Posted)
            ->whereNull('posted_journal_id')
            ->each(function (CustomerInvoice $invoice) use (&$exceptions) {
                $exceptions[] = $this->exceptionRow(
                    ArReconciliationExceptionType::MissingJournal,
                    __('Posted invoice :number has no GL journal.', ['number' => $invoice->invoice_number]),
                    $invoice->invoice_number,
                    (float) $invoice->total_amount,
                    $invoice->customer_id,
                );
            });

        (clone $paymentQuery)
            ->where('status', CustomerPaymentStatus::Posted)
            ->whereNull('posted_journal_id')
            ->each(function (CustomerPayment $payment) use (&$exceptions) {
                $exceptions[] = $this->exceptionRow(
                    ArReconciliationExceptionType::MissingJournal,
                    __('Posted payment :number has no GL journal.', ['number' => $payment->payment_number]),
                    $payment->payment_number,
                    (float) $payment->amount,
                    $payment->customer_id,
                );
            });

        (clone $applicationQuery)
            ->where('status', CustomerDepositApplicationStatus::Posted)
            ->whereNull('posted_journal_id')
            ->each(function (CustomerDepositApplication $application) use (&$exceptions) {
                $exceptions[] = $this->exceptionRow(
                    ArReconciliationExceptionType::MissingJournal,
                    __('Posted deposit application :number has no GL journal.', ['number' => $application->application_number]),
                    $application->application_number,
                    (float) $application->amount,
                    $application->customer_id,
                );
            });

        (clone $invoiceQuery)
            ->where('status', CustomerInvoiceStatus::Posted)
            ->whereColumn('amount_paid', '>', 'total_amount')
            ->each(function (CustomerInvoice $invoice) use (&$exceptions) {
                $exceptions[] = $this->exceptionRow(
                    ArReconciliationExceptionType::OverAllocation,
                    __('Invoice :number is over-allocated (paid :paid exceeds total :total).', [
                        'number' => $invoice->invoice_number,
                        'paid' => number_format((float) $invoice->amount_paid, 2),
                        'total' => number_format((float) $invoice->total_amount, 2),
                    ]),
                    $invoice->invoice_number,
                    (float) $invoice->amount_paid - (float) $invoice->total_amount,
                    $invoice->customer_id,
                );
            });

        (clone $invoiceQuery)
            ->where('status', CustomerInvoiceStatus::Posted)
            ->where('balance_due', '<', 0)
            ->each(function (CustomerInvoice $invoice) use (&$exceptions) {
                $exceptions[] = $this->exceptionRow(
                    ArReconciliationExceptionType::NegativeBalance,
                    __('Invoice :number has a negative balance due.', ['number' => $invoice->invoice_number]),
                    $invoice->invoice_number,
                    (float) $invoice->balance_due,
                    $invoice->customer_id,
                );
            });

        $invoiceIds = (clone $invoiceQuery)->pluck('id');

        CustomerPaymentAllocation::query()
            ->whereHas('payment', function ($q) use ($companyId, $branchId) {
                $q->where('company_id', $companyId);
                if ($branchId !== null) {
                    $q->where('branch_id', $branchId);
                }
            })
            ->whereNotIn('customer_invoice_id', $invoiceIds)
            ->with('payment')
            ->each(function (CustomerPaymentAllocation $allocation) use (&$exceptions) {
                $exceptions[] = $this->exceptionRow(
                    ArReconciliationExceptionType::OrphanPayment,
                    __('Payment allocation on :number references a missing invoice.', [
                        'number' => $allocation->payment?->payment_number ?? '#'.$allocation->customer_payment_id,
                    ]),
                    $allocation->payment?->payment_number,
                    (float) $allocation->amount,
                    $allocation->payment?->customer_id,
                );
            });

        (clone $paymentQuery)
            ->where('is_deposit', true)
            ->where('status', CustomerPaymentStatus::Posted)
            ->where('credit_remaining', '>', 0)
            ->each(function (CustomerPayment $payment) use (&$exceptions) {
                $exceptions[] = $this->exceptionRow(
                    ArReconciliationExceptionType::UnallocatedDeposit,
                    __('Deposit :number has :amount unapplied credit.', [
                        'number' => $payment->payment_number,
                        'amount' => number_format((float) $payment->credit_remaining, 2),
                    ]),
                    $payment->payment_number,
                    (float) $payment->credit_remaining,
                    $payment->customer_id,
                    critical: false,
                );
            });

        return $exceptions;
    }

    protected function checkRow(string $key, string $label, float $expected, float $actual, float $tolerance): array
    {
        $difference = round($actual - $expected, 2);
        $status = abs($difference) <= $tolerance
            ? ArReconciliationCheckStatus::Matched
            : ArReconciliationCheckStatus::Variance;

        return [
            'key' => $key,
            'label' => $label,
            'expected' => round($expected, 2),
            'actual' => round($actual, 2),
            'difference' => $difference,
            'status' => $status->value,
            'status_label' => $status->label(),
        ];
    }

    protected function exceptionRow(
        ArReconciliationExceptionType $type,
        string $message,
        ?string $reference,
        float $amount,
        ?int $customerId,
        bool $critical = true,
    ): array {
        return [
            'type' => $type->value,
            'type_label' => $type->label(),
            'message' => $message,
            'reference' => $reference,
            'amount' => round($amount, 2),
            'customer_id' => $customerId,
            'severity' => ($critical && $type->isBlocking()) ? 'critical' : 'warning',
        ];
    }
}
