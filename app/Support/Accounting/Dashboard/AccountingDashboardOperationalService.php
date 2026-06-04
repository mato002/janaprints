<?php

namespace App\Support\Accounting\Dashboard;

use App\Enums\CustomerInvoiceStatus;
use App\Enums\JournalStatus;
use App\Models\Accounting\Journal;
use App\Models\Accounting\AccountingPeriod;
use App\Models\Procurement\SupplierBill;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\CustomerPayment;
use App\Support\Procurement\SupplierAgingService;
use App\Support\Sales\CustomerAgingService;
use Illuminate\Support\Collection;

class AccountingDashboardOperationalService
{
    public function __construct(
        protected CustomerAgingService $customerAging,
        protected SupplierAgingService $supplierAging,
    ) {}

    /**
     * @param  array{company_id: int, branch_id?: int|null, as_of_date?: string}  $filters
     * @return array<string, mixed>
     */
    public function build(array $filters): array
    {
        $asOf = $filters['as_of_date'] ?? now()->toDateString();

        return [
            'widgets' => [
                'recent_journals' => $this->recentJournals($filters),
                'recent_invoices' => $this->recentInvoices($filters),
                'recent_payments' => $this->recentPayments($filters),
                'overdue_receivables' => $this->overdueReceivables($filters, $asOf),
                'overdue_payables' => $this->overduePayables($filters, $asOf),
                'period_closing_alerts' => $this->periodClosingAlerts($filters),
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
            ->when(
                ! empty($filters['branch_id']),
                fn ($q) => $q->where(function ($inner) use ($filters) {
                    $inner->whereNull('branch_id')
                        ->orWhere('branch_id', $filters['branch_id']);
                }),
            )
            ->with(['creator:id,name'])
            ->orderByDesc('journal_date')
            ->orderByDesc('id')
            ->limit(10)
            ->get(['id', 'journal_number', 'journal_date', 'status', 'description', 'total_debit', 'created_by'])
            ->map(fn (Journal $journal) => [
                'id' => $journal->id,
                'journal_number' => $journal->journal_number,
                'journal_date' => $journal->journal_date->toDateString(),
                'status' => $journal->status->value,
                'status_label' => $journal->status->label(),
                'description' => $journal->description,
                'amount' => round((float) $journal->total_debit, 2),
                'creator' => $journal->creator?->name,
            ])
            ->all();
    }

    /**
     * @param  array{company_id: int, branch_id?: int|null}  $filters
     * @return list<array<string, mixed>>
     */
    protected function recentInvoices(array $filters): array
    {
        return $this->invoiceQuery($filters)
            ->with('customer:id,company_name')
            ->orderByDesc('invoice_date')
            ->limit(8)
            ->get(['id', 'invoice_number', 'invoice_date', 'total_amount', 'customer_id', 'status'])
            ->map(fn (CustomerInvoice $invoice) => [
                'label' => $invoice->invoice_number,
                'customer' => $invoice->customer?->company_name,
                'date' => $invoice->invoice_date->toDateString(),
                'amount' => round((float) $invoice->total_amount, 2),
                'route' => route('admin.invoices.show', $invoice),
            ])
            ->all();
    }

    /**
     * @param  array{company_id: int, branch_id?: int|null}  $filters
     * @return list<array<string, mixed>>
     */
    protected function recentPayments(array $filters): array
    {
        return CustomerPayment::query()
            ->where('company_id', $filters['company_id'])
            ->when(! empty($filters['branch_id']), fn ($q) => $q->where('branch_id', $filters['branch_id']))
            ->with('customer:id,company_name')
            ->orderByDesc('payment_date')
            ->limit(8)
            ->get(['id', 'payment_number', 'payment_date', 'amount', 'customer_id'])
            ->map(fn (CustomerPayment $payment) => [
                'label' => $payment->payment_number,
                'customer' => $payment->customer?->company_name,
                'date' => $payment->payment_date->toDateString(),
                'amount' => round((float) $payment->amount, 2),
                'route' => route('admin.payments.show', $payment),
            ])
            ->all();
    }

    /**
     * @param  array{company_id: int, branch_id?: int|null}  $filters
     * @return list<array<string, mixed>>
     */
    protected function overdueReceivables(array $filters, string $asOf): array
    {
        $aging = $this->customerAging->build([
            'company_id' => $filters['company_id'],
            'as_of_date' => $asOf,
        ]);

        return collect($aging['rows'] ?? [])
            ->map(function (array $row) {
                $overdue = ($row['days_1_30'] ?? 0) + ($row['days_31_60'] ?? 0) + ($row['days_61_90'] ?? 0) + ($row['days_90_plus'] ?? 0);

                return ['name' => $row['customer_name'], 'amount' => round($overdue, 2)];
            })
            ->filter(fn (array $row) => $row['amount'] > 0)
            ->sortByDesc('amount')
            ->take(8)
            ->values()
            ->all();
    }

    /**
     * @param  array{company_id: int, branch_id?: int|null}  $filters
     * @return list<array<string, mixed>>
     */
    protected function overduePayables(array $filters, string $asOf): array
    {
        $aging = $this->supplierAging->build([
            'company_id' => $filters['company_id'],
            'as_of_date' => $asOf,
        ]);

        return collect($aging['rows'] ?? [])
            ->map(function (array $row) {
                $overdue = ($row['days_1_30'] ?? 0) + ($row['days_31_60'] ?? 0) + ($row['days_61_90'] ?? 0) + ($row['days_90_plus'] ?? 0);

                return ['name' => $row['vendor_name'], 'amount' => round($overdue, 2)];
            })
            ->filter(fn (array $row) => $row['amount'] > 0)
            ->sortByDesc('amount')
            ->take(8)
            ->values()
            ->all();
    }

    /**
     * @param  array{company_id: int, branch_id?: int|null}  $filters
     * @return list<array<string, mixed>>
     */
    protected function periodClosingAlerts(array $filters): array
    {
        $alerts = [];

        AccountingPeriod::query()
            ->where('company_id', $filters['company_id'])
            ->where('is_current', false)
            ->where('status', \App\Enums\AccountingPeriodStatus::Open)
            ->orderByDesc('end_date')
            ->limit(3)
            ->get(['id', 'code', 'name', 'end_date'])
            ->each(function (AccountingPeriod $period) use (&$alerts) {
                $alerts[] = [
                    'label' => $period->code,
                    'description' => __('Prior period still open: :name', ['name' => $period->name]),
                    'date' => $period->end_date->toDateString(),
                    'route' => route('admin.accounting.periods.index'),
                ];
            });

        $draftJournals = Journal::query()
            ->where('company_id', $filters['company_id'])
            ->where('status', JournalStatus::Draft)
            ->count();

        if ($draftJournals > 0) {
            $alerts[] = [
                'label' => __('Draft journals'),
                'description' => __(':count journals awaiting post', ['count' => $draftJournals]),
                'date' => now()->toDateString(),
                'route' => route('admin.accounting.journals.index'),
            ];
        }

        $approvedInvoices = $this->invoiceQuery($filters)
            ->where('status', CustomerInvoiceStatus::Approved)
            ->count();

        if ($approvedInvoices > 0) {
            $alerts[] = [
                'label' => __('Approved invoices'),
                'description' => __(':count invoices ready to post', ['count' => $approvedInvoices]),
                'date' => now()->toDateString(),
                'route' => route('admin.invoices.index'),
            ];
        }

        return $alerts;
    }

    /**
     * @param  array{company_id: int, branch_id?: int|null}  $filters
     */
    protected function invoiceQuery(array $filters)
    {
        return CustomerInvoice::query()
            ->where('company_id', $filters['company_id'])
            ->when(
                ! empty($filters['branch_id']),
                fn ($q) => $q->where('branch_id', $filters['branch_id']),
            );
    }
}
