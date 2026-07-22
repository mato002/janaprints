<?php

namespace App\Support\Export;

use App\Enums\TaxDirection;
use App\Http\Controllers\Admin\Concerns\ExportsTabularIndex;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Models\Accounting\AccountingPeriod;
use App\Models\Accounting\FiscalYear;
use App\Models\Accounting\Journal;
use App\Models\Accounting\PostingRule;
use App\Models\Accounting\PostingTemplate;
use App\Models\Procurement\SupplierBill;
use App\Models\Procurement\SupplierPayment;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\CustomerPayment;
use App\Enums\CustomerInvoiceType;
use App\Models\Tax\TaxAuditLog;
use App\Models\Tax\TaxCode;
use App\Models\Tax\TaxPeriod;
use App\Models\Tax\TaxReturn;
use App\Support\Accounting\GeneralLedgerService;
use App\Support\Accounting\Posting\PostingRuleWorkspacePresenter;
use App\Support\Accounting\Reports\BalanceSheetReportService;
use App\Support\Accounting\Reports\GeneralLedgerReportService;
use App\Support\Accounting\Reports\ProfitAndLossReportService;
use App\Support\Accounting\TrialBalanceService;
use App\Support\Procurement\SupplierAgingService;
use App\Support\Procurement\SupplierLedgerService;
use App\Support\Procurement\SupplierStatementService;
use App\Support\Sales\CustomerAgingService;
use App\Support\Sales\CustomerLedgerService;
use App\Support\Sales\CustomerStatementService;
use App\Support\Tax\TaxReportService;
use App\Support\Tax\TaxTransactionRecorder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AccountingListingExporter
{
    use ExportsTabularIndex;
    use ScopesToTenant;

    /** @var list<string> */
    protected array $listings = [
        'customer-invoices',
        'customer-credit-notes',
        'customer-payments',
        'supplier-bills',
        'supplier-payments',
        'journals',
        'ledger-entries',
        'fiscal-years',
        'accounting-periods',
        'posting-rules',
        'posting-templates',
        'ar-aging',
        'ar-ledger',
        'ar-statement',
        'ap-aging',
        'ap-ledger',
        'ap-statement',
        'trial-balance',
        'balance-sheet',
        'profit-and-loss',
        'general-ledger-report',
        'tax-codes',
        'tax-ledger',
        'tax-returns',
        'tax-audit',
        'vat-summary',
        'output-vat',
        'input-vat',
        'tax-liability',
    ];

    public function __construct(
        protected GeneralLedgerService $generalLedger,
        protected TrialBalanceService $trialBalance,
        protected BalanceSheetReportService $balanceSheet,
        protected ProfitAndLossReportService $profitAndLoss,
        protected GeneralLedgerReportService $generalLedgerReport,
        protected PostingRuleWorkspacePresenter $postingRules,
        protected CustomerAgingService $customerAging,
        protected CustomerLedgerService $customerLedger,
        protected CustomerStatementService $customerStatement,
        protected SupplierAgingService $supplierAging,
        protected SupplierLedgerService $supplierLedger,
        protected SupplierStatementService $supplierStatement,
        protected TaxReportService $taxReports,
        protected TaxTransactionRecorder $taxRecorder,
    ) {}

    public function download(
        string $listing,
        string $format,
        TabularExportWriter $writer,
        Request $request,
    ): StreamedResponse {
        abort_unless(in_array($listing, $this->listings, true), 404);

        [$headers, $rows, $basename, $title] = match ($listing) {
            'customer-invoices' => $this->customerInvoices(),
            'customer-credit-notes' => $this->customerCreditNotes(),
            'customer-payments' => $this->customerPayments(),
            'supplier-bills' => $this->supplierBills(),
            'supplier-payments' => $this->supplierPayments(),
            'journals' => $this->journals($request),
            'ledger-entries' => $this->ledgerEntries($request),
            'fiscal-years' => $this->fiscalYears(),
            'accounting-periods' => $this->accountingPeriods($request),
            'posting-rules' => $this->postingRulesExport($request),
            'posting-templates' => $this->postingTemplates(),
            'ar-aging' => $this->arAging($request),
            'ar-ledger' => $this->arLedger($request),
            'ar-statement' => $this->arStatement($request),
            'ap-aging' => $this->apAging($request),
            'ap-ledger' => $this->apLedger($request),
            'ap-statement' => $this->apStatement($request),
            'trial-balance' => $this->trialBalance($request),
            'balance-sheet' => $this->balanceSheetExport($request),
            'profit-and-loss' => $this->profitAndLossExport($request),
            'general-ledger-report' => $this->generalLedgerReportExport($request),
            'tax-codes' => $this->taxCodes(),
            'tax-ledger' => $this->taxLedger($request),
            'tax-returns' => $this->taxReturns(),
            'tax-audit' => $this->taxAudit(),
            'vat-summary' => $this->vatSummary($request),
            'output-vat' => $this->directionVat($request, 'output'),
            'input-vat' => $this->directionVat($request, 'input'),
            'tax-liability' => $this->taxLiability($request),
            default => abort(404),
        };

        return $this->downloadTabularExport($writer, $format, $basename, $headers, $rows, $title);
    }

    /**
     * @return array{0: list<string>, 1: list<list<string|float|int|null>>, 2: string, 3: string}
     */
    protected function customerInvoices(): array
    {
        Gate::authorize('viewAny', CustomerInvoice::class);

        $invoices = $this->scopeToTenant(
            CustomerInvoice::query()
                ->with(['customer', 'salesOrder'])
                ->whereNot('invoice_type', CustomerInvoiceType::CreditNote)
        )->latest('invoice_date')->latest('id')->limit(5000)->get();

        $headers = [__('Number'), __('Customer'), __('Date'), __('Type'), __('Total'), __('Status')];
        $rows = $invoices->map(fn (CustomerInvoice $invoice) => [
            $invoice->invoice_number,
            $invoice->customer?->company_name ?? '',
            $invoice->invoice_date->format('Y-m-d'),
            $invoice->invoice_type->label(),
            number_format((float) $invoice->total_amount, 2, '.', ''),
            $invoice->status->label(),
        ])->all();

        return [$headers, $rows, 'customer-invoices', __('Customer invoices')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string|float|int|null>>, 2: string, 3: string}
     */
    protected function customerCreditNotes(): array
    {
        Gate::authorize('viewAny', CustomerInvoice::class);

        $creditNotes = $this->scopeToTenant(
            CustomerInvoice::query()
                ->with(['customer', 'creditedInvoice'])
                ->where('invoice_type', CustomerInvoiceType::CreditNote)
        )->latest('invoice_date')->latest('id')->limit(5000)->get();

        $headers = [__('Number'), __('Customer'), __('Credits invoice'), __('Date'), __('Total'), __('Status')];
        $rows = $creditNotes->map(fn (CustomerInvoice $creditNote) => [
            $creditNote->invoice_number,
            $creditNote->customer?->company_name ?? '',
            $creditNote->creditedInvoice?->invoice_number ?? '',
            $creditNote->invoice_date->format('Y-m-d'),
            number_format((float) $creditNote->total_amount, 2, '.', ''),
            $creditNote->status->label(),
        ])->all();

        return [$headers, $rows, 'customer-credit-notes', __('Customer credit notes')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string|float|int|null>>, 2: string, 3: string}
     */
    protected function customerPayments(): array
    {
        Gate::authorize('viewAny', CustomerPayment::class);

        $payments = $this->scopeToTenant(
            CustomerPayment::query()->with('customer')
        )->latest('payment_date')->latest('id')->limit(5000)->get();

        $headers = [__('Number'), __('Customer'), __('Date'), __('Method'), __('Amount'), __('Status')];
        $rows = $payments->map(fn (CustomerPayment $payment) => [
            $payment->payment_number,
            $payment->customer?->company_name ?? '',
            $payment->payment_date->format('Y-m-d'),
            $payment->payment_method->label(),
            number_format((float) $payment->amount, 2, '.', ''),
            $payment->status->label(),
        ])->all();

        return [$headers, $rows, 'customer-payments', __('Customer payments')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string|float|int|null>>, 2: string, 3: string}
     */
    protected function supplierBills(): array
    {
        Gate::authorize('viewAny', SupplierBill::class);

        $bills = $this->scopeToTenant(
            SupplierBill::query()->with(['vendor', 'purchaseOrder'])
        )->latest('bill_date')->latest('id')->limit(5000)->get();

        $headers = [__('Number'), __('Supplier'), __('Date'), __('Total'), __('Balance'), __('Status')];
        $rows = $bills->map(fn (SupplierBill $bill) => [
            $bill->bill_number,
            $bill->vendor?->vendor_name ?? '',
            $bill->bill_date->format('Y-m-d'),
            number_format((float) $bill->total_amount, 2, '.', ''),
            number_format((float) $bill->balance_due, 2, '.', ''),
            $bill->status->label(),
        ])->all();

        return [$headers, $rows, 'supplier-bills', __('Supplier bills')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string|float|int|null>>, 2: string, 3: string}
     */
    protected function supplierPayments(): array
    {
        Gate::authorize('viewAny', SupplierPayment::class);

        $payments = $this->scopeToTenant(
            SupplierPayment::query()->with('vendor')
        )->latest('payment_date')->latest('id')->limit(5000)->get();

        $headers = [__('Number'), __('Supplier'), __('Date'), __('Amount'), __('Status')];
        $rows = $payments->map(fn (SupplierPayment $payment) => [
            $payment->payment_number,
            $payment->vendor?->vendor_name ?? '',
            $payment->payment_date->format('Y-m-d'),
            number_format((float) $payment->amount, 2, '.', ''),
            $payment->status->label(),
        ])->all();

        return [$headers, $rows, 'supplier-payments', __('Supplier payments')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string|float|int|null>>, 2: string, 3: string}
     */
    protected function journals(Request $request): array
    {
        Gate::authorize('viewAny', Journal::class);

        $journals = Journal::query()
            ->forTenant()
            ->with(['accountingPeriod'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest('journal_date')
            ->latest('id')
            ->limit(5000)
            ->get();

        $headers = [__('Number'), __('Date'), __('Period'), __('Debit'), __('Credit'), __('Status')];
        $rows = $journals->map(fn (Journal $journal) => [
            $journal->journal_number,
            $journal->journal_date->format('Y-m-d'),
            $journal->accountingPeriod?->code ?? '',
            number_format((float) $journal->total_debit, 2, '.', ''),
            number_format((float) $journal->total_credit, 2, '.', ''),
            $journal->status->label(),
        ])->all();

        return [$headers, $rows, 'journals', __('Journal entries')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string|float|int|null>>, 2: string, 3: string}
     */
    protected function ledgerEntries(Request $request): array
    {
        Gate::authorize('viewAny', Journal::class);

        $filters = array_filter([
            'period_id' => $request->integer('period_id') ?: null,
            'account_id' => $request->integer('account_id') ?: null,
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date'),
        ], fn ($v) => filled($v));

        $entries = $this->generalLedger->entries($filters);

        $headers = [__('Date'), __('Journal'), __('Account'), __('Debit'), __('Credit')];
        $rows = $entries->map(fn ($entry) => [
            $entry->journal_date,
            $entry->journal_number,
            $entry->account_code.' — '.$entry->account_name,
            $entry->debit > 0 ? number_format((float) $entry->debit, 2, '.', '') : '',
            $entry->credit > 0 ? number_format((float) $entry->credit, 2, '.', '') : '',
        ])->all();

        return [$headers, $rows, 'general-ledger-inquiry', __('General Ledger')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string|float|int|null>>, 2: string, 3: string}
     */
    protected function fiscalYears(): array
    {
        Gate::authorize('viewAny', FiscalYear::class);

        $fiscalYears = FiscalYear::query()
            ->forTenant()
            ->withCount('periods')
            ->orderByDesc('start_date')
            ->get();

        $headers = [__('Fiscal year'), __('Code'), __('Start'), __('End'), __('Status'), __('Periods')];
        $rows = $fiscalYears->map(fn (FiscalYear $fy) => [
            $fy->name,
            $fy->code,
            $fy->start_date->format('Y-m-d'),
            $fy->end_date->format('Y-m-d'),
            $fy->status->label(),
            (string) $fy->periods_count,
        ])->all();

        return [$headers, $rows, 'fiscal-years', __('Fiscal years')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string|float|int|null>>, 2: string, 3: string}
     */
    protected function accountingPeriods(Request $request): array
    {
        Gate::authorize('viewAny', FiscalYear::class);

        $fiscalYearId = $request->integer('fiscal_year_id');
        abort_unless($fiscalYearId, 404);

        $fiscalYear = FiscalYear::query()->forTenant()->findOrFail($fiscalYearId);

        $headers = [__('#'), __('Period'), __('Code'), __('Start'), __('End'), __('Status')];
        $rows = $fiscalYear->periods->map(fn (AccountingPeriod $period) => [
            (string) $period->period_number,
            $period->name,
            $period->code,
            $period->start_date->format('Y-m-d'),
            $period->end_date->format('Y-m-d'),
            $period->status->label(),
        ])->all();

        return [$headers, $rows, 'accounting-periods', __('Accounting periods')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string|float|int|null>>, 2: string, 3: string}
     */
    protected function postingRulesExport(Request $request): array
    {
        Gate::authorize('viewAny', PostingRule::class);

        $workspace = $this->postingRules->buildIndex($request);
        $validations = $workspace['validations'];

        $headers = [__('Event code'), __('Rule'), __('Module'), __('Template'), __('Auto post'), __('Status'), __('Validation')];
        $rows = collect($workspace['rules'])->map(function (PostingRule $rule) use ($validations) {
            $validation = $validations[$rule->id];

            return [
                $rule->event_code,
                $rule->name,
                $rule->module->label(),
                $rule->template?->code ?? '',
                $rule->auto_post ? __('Yes') : __('No'),
                $rule->is_active ? __('Active') : __('Inactive'),
                $validation->label(),
            ];
        })->all();

        return [$headers, $rows, 'posting-rules', __('Posting rules')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string|float|int|null>>, 2: string, 3: string}
     */
    protected function postingTemplates(): array
    {
        Gate::authorize('viewAny', PostingTemplate::class);

        $templates = PostingTemplate::query()
            ->forTenant()
            ->withCount('lines')
            ->orderBy('module')
            ->orderBy('code')
            ->limit(5000)
            ->get();

        $headers = [__('Code'), __('Name'), __('Module'), __('Lines'), __('Status')];
        $rows = $templates->map(fn (PostingTemplate $template) => [
            $template->code,
            $template->name,
            $template->module->label(),
            (string) $template->lines_count,
            $template->is_active ? __('Active') : __('Inactive'),
        ])->all();

        return [$headers, $rows, 'posting-templates', __('Posting templates')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string|float|int|null>>, 2: string, 3: string}
     */
    protected function arAging(Request $request): array
    {
        Gate::authorize('viewReceivablesAging', \App\Models\Crm\Customer::class);

        $report = $this->customerAging->build([
            'customer_id' => $request->integer('customer_id') ?: null,
            'as_of_date' => $request->input('as_of_date', now()->toDateString()),
        ]);

        $headers = [__('Customer'), __('Current'), '1–30', '31–60', '61–90', '90+', __('Total')];
        $rows = collect($report['rows'])->map(fn (array $row) => [
            $row['customer_name'],
            number_format($row['current'], 2, '.', ''),
            number_format($row['days_1_30'], 2, '.', ''),
            number_format($row['days_31_60'], 2, '.', ''),
            number_format($row['days_61_90'], 2, '.', ''),
            number_format($row['days_90_plus'], 2, '.', ''),
            number_format($row['total'], 2, '.', ''),
        ])->all();

        return [$headers, $rows, 'ar-aging', __('Accounts receivable aging')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string|float|int|null>>, 2: string, 3: string}
     */
    protected function arLedger(Request $request): array
    {
        Gate::authorize('viewReceivablesLedger', \App\Models\Crm\Customer::class);

        $customerId = $request->integer('customer_id');
        if (! $customerId) {
            return [[__('Date'), __('Type'), __('Reference'), __('Description'), __('Debit'), __('Credit'), __('Balance')], [], 'ar-ledger', __('Customer ledger')];
        }

        $report = $this->customerLedger->build([
            'customer_id' => $customerId,
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date'),
        ]);

        $headers = [__('Date'), __('Type'), __('Reference'), __('Description'), __('Debit'), __('Credit')];
        $rows = $report['entries']->map(fn ($entry) => [
            $entry->date,
            $entry->type,
            $entry->reference,
            $entry->description,
            $entry->debit > 0 ? number_format($entry->debit, 2, '.', '') : '',
            $entry->credit > 0 ? number_format($entry->credit, 2, '.', '') : '',
        ])->all();

        return [$headers, $rows, 'ar-ledger', __('Customer ledger')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string|float|int|null>>, 2: string, 3: string}
     */
    protected function arStatement(Request $request): array
    {
        Gate::authorize('viewReceivablesStatement', \App\Models\Crm\Customer::class);

        if (! $request->filled(['customer_id', 'from_date', 'to_date'])) {
            return [[__('Date'), __('Reference'), __('Description'), __('Debit'), __('Credit'), __('Balance')], [], 'ar-statement', __('Customer statement')];
        }

        $report = $this->customerStatement->build([
            'customer_id' => $request->integer('customer_id'),
            'from_date' => $request->string('from_date')->toString(),
            'to_date' => $request->string('to_date')->toString(),
        ]);

        $headers = [__('Date'), __('Type'), __('Reference'), __('Description'), __('Debit'), __('Credit')];
        $rows = $report['entries']->map(fn ($entry) => [
            $entry->date,
            $entry->type,
            $entry->reference,
            $entry->description,
            $entry->debit > 0 ? number_format($entry->debit, 2, '.', '') : '',
            $entry->credit > 0 ? number_format($entry->credit, 2, '.', '') : '',
        ])->all();

        return [$headers, $rows, 'ar-statement', __('Customer statement')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string|float|int|null>>, 2: string, 3: string}
     */
    protected function apAging(Request $request): array
    {
        Gate::authorize('viewPayablesAging', \App\Models\Procurement\Vendor::class);

        $report = $this->supplierAging->build([
            'vendor_id' => $request->integer('vendor_id') ?: null,
            'as_of_date' => $request->input('as_of_date', now()->toDateString()),
        ]);

        $headers = [__('Supplier'), __('Current'), '1–30', '31–60', '61–90', '90+', __('Total')];
        $rows = collect($report['rows'])->map(fn (array $row) => [
            $row['vendor_name'],
            number_format($row['current'], 2, '.', ''),
            number_format($row['days_1_30'], 2, '.', ''),
            number_format($row['days_31_60'], 2, '.', ''),
            number_format($row['days_61_90'], 2, '.', ''),
            number_format($row['days_90_plus'], 2, '.', ''),
            number_format($row['total'], 2, '.', ''),
        ])->all();

        return [$headers, $rows, 'ap-aging', __('Accounts payable aging')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string|float|int|null>>, 2: string, 3: string}
     */
    protected function apLedger(Request $request): array
    {
        Gate::authorize('viewPayablesLedger', \App\Models\Procurement\Vendor::class);

        $vendorId = $request->integer('vendor_id');
        if (! $vendorId) {
            return [[__('Date'), __('Type'), __('Reference'), __('Description'), __('Debit'), __('Credit')], [], 'ap-ledger', __('Supplier ledger')];
        }

        $report = $this->supplierLedger->build([
            'vendor_id' => $vendorId,
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date'),
        ]);

        $headers = [__('Date'), __('Type'), __('Reference'), __('Description'), __('Debit'), __('Credit')];
        $rows = $report['entries']->map(fn ($entry) => [
            $entry->date,
            $entry->type,
            $entry->reference,
            $entry->description,
            $entry->debit > 0 ? number_format($entry->debit, 2, '.', '') : '',
            $entry->credit > 0 ? number_format($entry->credit, 2, '.', '') : '',
        ])->all();

        return [$headers, $rows, 'ap-ledger', __('Supplier ledger')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string|float|int|null>>, 2: string, 3: string}
     */
    protected function apStatement(Request $request): array
    {
        Gate::authorize('viewPayablesStatement', \App\Models\Procurement\Vendor::class);

        if (! $request->filled(['vendor_id', 'from_date', 'to_date'])) {
            return [[__('Date'), __('Reference'), __('Description'), __('Debit'), __('Credit'), __('Balance')], [], 'ap-statement', __('Supplier statement')];
        }

        $report = $this->supplierStatement->build([
            'vendor_id' => $request->integer('vendor_id'),
            'from_date' => $request->string('from_date')->toString(),
            'to_date' => $request->string('to_date')->toString(),
        ]);

        $headers = [__('Date'), __('Type'), __('Reference'), __('Description'), __('Debit'), __('Credit')];
        $rows = $report['entries']->map(fn ($entry) => [
            $entry->date,
            $entry->type,
            $entry->reference,
            $entry->description,
            $entry->debit > 0 ? number_format($entry->debit, 2, '.', '') : '',
            $entry->credit > 0 ? number_format($entry->credit, 2, '.', '') : '',
        ])->all();

        return [$headers, $rows, 'ap-statement', __('Supplier statement')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string|float|int|null>>, 2: string, 3: string}
     */
    protected function trialBalance(Request $request): array
    {
        Gate::authorize('viewReports', Journal::class);

        $filters = array_filter([
            'period_id' => $request->integer('period_id') ?: null,
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date'),
        ], fn ($v) => filled($v));

        $report = $request->boolean('include_zero', true)
            ? $this->trialBalance->buildFull($filters)
            : $this->trialBalance->build($filters);

        $headers = [__('Account'), __('Name'), __('Debit'), __('Credit'), __('Balance')];
        $rows = $report['rows']->map(fn (array $row) => [
            $row['account_code'],
            $row['account_name'],
            number_format($row['period_debit'], 2, '.', ''),
            number_format($row['period_credit'], 2, '.', ''),
            number_format($row['balance'], 2, '.', ''),
        ])->all();

        return [$headers, $rows, 'trial-balance', __('Trial Balance')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string|float|int|null>>, 2: string, 3: string}
     */
    protected function balanceSheetExport(Request $request): array
    {
        Gate::authorize('viewReports', Journal::class);

        $report = $this->balanceSheet->build([
            'as_of_date' => $request->input('as_of_date', now()->toDateString()),
            'period_id' => $request->integer('period_id') ?: null,
        ]);

        $headers = [__('Section'), __('Account'), __('Name'), __('Balance')];
        $rows = [];

        foreach ($report['sections'] as $section) {
            foreach ($section['accounts'] as $account) {
                $rows[] = [
                    $section['label'],
                    $account['account_code'],
                    $account['account_name'],
                    number_format($account['balance'], 2, '.', ''),
                ];
            }
        }

        return [$headers, $rows, 'balance-sheet', __('Balance Sheet')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string|float|int|null>>, 2: string, 3: string}
     */
    protected function profitAndLossExport(Request $request): array
    {
        Gate::authorize('viewReports', Journal::class);

        $period = AccountingPeriod::query()->forTenant()->where('is_current', true)->first();
        $filters = array_filter([
            'from_date' => $request->input('from_date', $period?->start_date?->toDateString() ?? now()->startOfMonth()->toDateString()),
            'to_date' => $request->input('to_date', $period?->end_date?->toDateString() ?? now()->toDateString()),
            'period_id' => $request->integer('period_id') ?: $period?->id,
        ], fn ($v) => filled($v));

        $report = $this->profitAndLoss->build($filters);

        $headers = [__('Section'), __('Account'), __('Name'), __('Amount')];
        $rows = [];

        foreach ($report['sections'] as $section) {
            foreach ($section['accounts'] as $account) {
                $rows[] = [
                    $section['label'],
                    $account['account_code'],
                    $account['account_name'],
                    number_format($account['amount'], 2, '.', ''),
                ];
            }
        }

        return [$headers, $rows, 'profit-and-loss', __('Profit & Loss')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string|float|int|null>>, 2: string, 3: string}
     */
    protected function generalLedgerReportExport(Request $request): array
    {
        Gate::authorize('viewReports', Journal::class);

        $filters = array_filter([
            'period_id' => $request->integer('period_id') ?: null,
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date'),
            'account_id' => $request->integer('account_id') ?: null,
        ], fn ($v) => filled($v));

        if (! empty($filters['account_id'])) {
            $report = $this->generalLedgerReport->build($filters);
            $headers = [__('Date'), __('Journal'), __('Reference'), __('Description'), __('Debit'), __('Credit'), __('Balance')];
            $rows = collect($report['lines'])->map(fn (array $line) => [
                $line['journal_date'],
                $line['journal_number'],
                $line['reference'] ?? '',
                $line['description'] ?? '',
                $line['debit'] > 0 ? number_format($line['debit'], 2, '.', '') : '',
                $line['credit'] > 0 ? number_format($line['credit'], 2, '.', '') : '',
                number_format($line['running_balance'], 2, '.', ''),
            ])->all();

            return [$headers, $rows, 'gl-report', __('General Ledger Report')];
        }

        $summary = $this->generalLedgerReport->buildSummary($filters);
        $headers = [__('Account'), __('Name'), __('Type'), __('Debit'), __('Credit'), __('Balance')];
        $rows = collect($summary['rows'] ?? [])->map(fn (array $row) => [
            $row['account_code'],
            $row['account_name'],
            $row['account_type'] ?? '',
            number_format($row['period_debit'], 2, '.', ''),
            number_format($row['period_credit'], 2, '.', ''),
            number_format($row['signed_balance'], 2, '.', ''),
        ])->all();

        return [$headers, $rows, 'gl-report-summary', __('General Ledger Summary')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string|float|int|null>>, 2: string, 3: string}
     */
    protected function taxCodes(): array
    {
        Gate::authorize('viewAny', TaxCode::class);

        $codes = TaxCode::query()
            ->forTenant()
            ->with(['category', 'rates'])
            ->orderBy('sort_order')
            ->orderBy('code')
            ->get();

        $headers = [__('Code'), __('Name'), __('Category'), __('Current rate'), __('Active')];
        $rows = $codes->map(fn (TaxCode $code) => [
            $code->code,
            $code->name,
            trim(($code->category?->name ?? '').($code->category?->type ? ' ('.$code->category->type->label().')' : '')),
            $code->rates->first() ? number_format((float) $code->rates->first()->rate_percent, 2).'%' : '',
            $code->is_active ? __('Active') : __('Inactive'),
        ])->all();

        return [$headers, $rows, 'tax-codes', __('Tax codes')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string|float|int|null>>, 2: string, 3: string}
     */
    protected function taxLedger(Request $request): array
    {
        Gate::authorize('viewLedger', TaxCode::class);

        ['companyId' => $companyId] = ['companyId' => tenant()->companyId()];
        abort_unless($companyId, 403);

        $from = $request->input('from_date');
        $to = $request->input('to_date');
        $periodId = $request->integer('tax_period_id') ?: null;

        if ($periodId) {
            $period = TaxPeriod::query()->forTenant()->find($periodId);
            if ($period) {
                $from = $period->start_date->toDateString();
                $to = $period->end_date->toDateString();
            }
        }

        $direction = $request->input('direction')
            ? TaxDirection::from($request->input('direction'))
            : null;

        $rows = $this->taxRecorder->ledgerQuery($companyId, $from, $to, $direction);

        $headers = [__('Date'), __('Document'), __('Code'), __('Direction'), __('Tax')];
        $exportRows = $rows->map(fn ($row) => [
            $row->document_date->format('Y-m-d'),
            $row->document_number,
            $row->taxCode?->code ?? '',
            $row->direction->label(),
            number_format((float) $row->tax_amount, 2, '.', ''),
        ])->all();

        return [$headers, $exportRows, 'tax-ledger', __('Tax Ledger')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string|float|int|null>>, 2: string, 3: string}
     */
    protected function taxReturns(): array
    {
        Gate::authorize('manageReturns', TaxCode::class);

        $returns = TaxReturn::query()
            ->forTenant()
            ->with('taxPeriod')
            ->orderByDesc('updated_at')
            ->limit(5000)
            ->get();

        $headers = [__('Period'), __('Status'), __('Output tax'), __('Input tax'), __('Net liability'), __('Updated')];
        $rows = $returns->map(fn (TaxReturn $return) => [
            $return->taxPeriod?->code ?? '',
            $return->status->label(),
            number_format((float) $return->output_tax, 2, '.', ''),
            number_format((float) $return->input_tax, 2, '.', ''),
            number_format((float) $return->net_liability, 2, '.', ''),
            $return->updated_at?->format('Y-m-d H:i') ?? '',
        ])->all();

        return [$headers, $rows, 'tax-returns', __('Tax returns')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string|float|int|null>>, 2: string, 3: string}
     */
    protected function taxAudit(): array
    {
        Gate::authorize('viewAudit', TaxCode::class);

        $logs = TaxAuditLog::query()
            ->forTenant()
            ->with('user')
            ->orderByDesc('created_at')
            ->limit(5000)
            ->get();

        $headers = [__('Date'), __('Action'), __('Entity'), __('User')];
        $rows = $logs->map(fn (TaxAuditLog $log) => [
            $log->created_at->format('Y-m-d H:i'),
            $log->action,
            class_basename($log->auditable_type ?? ''),
            $log->user?->name ?? '',
        ])->all();

        return [$headers, $rows, 'tax-audit', __('Tax audit trail')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string|float|int|null>>, 2: string, 3: string}
     */
    protected function vatSummary(Request $request): array
    {
        Gate::authorize('viewReports', TaxCode::class);

        $filters = $this->taxFilters($request);
        $report = $this->taxReports->vatSummary($filters);

        $headers = [__('Tax code'), __('Direction'), __('Tax amount')];
        $rows = collect($report['by_code'] ?? [])->map(fn (array $row) => [
            $row['tax_code'] ?? '',
            $row['direction'] ?? '',
            number_format((float) ($row['tax_amount'] ?? 0), 2, '.', ''),
        ])->all();

        $rows[] = ['', __('Output VAT'), number_format($report['output_vat'], 2, '.', '')];
        $rows[] = ['', __('Input VAT'), number_format($report['input_vat'], 2, '.', '')];
        $rows[] = ['', __('Net liability'), number_format($report['net_liability'], 2, '.', '')];

        return [$headers, $rows, 'vat-summary', __('VAT Summary')];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string|float|int|null>>, 2: string, 3: string}
     */
    protected function directionVat(Request $request, string $direction): array
    {
        Gate::authorize('viewReports', TaxCode::class);

        $filters = $this->taxFilters($request);
        $report = $direction === 'output'
            ? $this->taxReports->outputVat($filters)
            : $this->taxReports->inputVat($filters);

        $headers = [__('Date'), __('Document'), __('Code'), __('Taxable'), __('Tax')];
        $rows = collect($report['rows'] ?? [])->map(fn (array $row) => [
            $row['document_date'] ?? '',
            $row['document_number'] ?? '',
            $row['tax_code'] ?? '',
            number_format((float) ($row['taxable_amount'] ?? 0), 2, '.', ''),
            number_format((float) ($row['tax_amount'] ?? 0), 2, '.', ''),
        ])->all();

        $basename = $direction === 'output' ? 'output-vat' : 'input-vat';
        $title = $direction === 'output' ? __('Output VAT') : __('Input VAT');

        return [$headers, $rows, $basename, $title];
    }

    /**
     * @return array{0: list<string>, 1: list<list<string|float|int|null>>, 2: string, 3: string}
     */
    protected function taxLiability(Request $request): array
    {
        Gate::authorize('viewReports', TaxCode::class);

        $filters = $this->taxFilters($request);
        $report = $this->taxReports->taxLiability($filters);

        $headers = [__('Metric'), __('Amount')];
        $rows = [
            [__('Output VAT'), number_format($report['output_vat'] ?? 0, 2, '.', '')],
            [__('Input VAT'), number_format($report['input_vat'] ?? 0, 2, '.', '')],
            [__('Withholding tax'), number_format($report['withholding_tax'] ?? 0, 2, '.', '')],
            [__('Net liability'), number_format($report['net_liability'] ?? 0, 2, '.', '')],
        ];

        return [$headers, $rows, 'tax-liability', __('Tax liability')];
    }

    /**
     * @return array{company_id: int, from_date?: string, to_date?: string, tax_period_id?: int}
     */
    protected function taxFilters(Request $request): array
    {
        $companyId = tenant()->companyId();
        abort_unless($companyId, 403);

        $from = $request->input('from_date');
        $to = $request->input('to_date');
        $periodId = $request->integer('tax_period_id') ?: null;

        if ($periodId) {
            $period = TaxPeriod::query()->forTenant()->find($periodId);
            if ($period) {
                $from = $period->start_date->toDateString();
                $to = $period->end_date->toDateString();
            }
        }

        return array_filter([
            'company_id' => $companyId,
            'from_date' => $from,
            'to_date' => $to,
            'tax_period_id' => $periodId,
        ], fn ($v) => filled($v));
    }
}
