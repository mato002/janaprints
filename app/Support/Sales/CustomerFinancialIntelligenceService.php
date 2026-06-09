<?php

namespace App\Support\Sales;

use App\Enums\CustomerInvoiceStatus;
use App\Enums\CustomerInvoiceType;
use App\Enums\CustomerPaymentStatus;
use App\Models\Crm\Customer;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\CustomerPayment;
use Illuminate\Support\Facades\DB;

class CustomerFinancialIntelligenceService
{
    public function __construct(
        protected CustomerLedgerService $ledger,
        protected CustomerCreditWalletService $wallet,
        protected CustomerAgingService $aging,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function profile(Customer $customer): array
    {
        $customerId = $customer->id;

        $totalInvoiced = (float) CustomerInvoice::query()
            ->where('customer_id', $customerId)
            ->where('status', CustomerInvoiceStatus::Posted)
            ->whereNot('invoice_type', CustomerInvoiceType::CreditNote)
            ->sum('total_amount');

        $totalCreditNotes = (float) CustomerInvoice::query()
            ->where('customer_id', $customerId)
            ->where('status', CustomerInvoiceStatus::Posted)
            ->where('invoice_type', CustomerInvoiceType::CreditNote)
            ->sum('total_amount');

        $totalPaid = (float) CustomerPayment::query()
            ->where('customer_id', $customerId)
            ->where('status', CustomerPaymentStatus::Posted)
            ->sum('amount');

        $outstanding = $this->ledger->closingBalance($customerId);
        $creditWallet = $this->wallet->summary($customerId);

        $openInvoices = CustomerInvoice::query()
            ->where('customer_id', $customerId)
            ->where('status', CustomerInvoiceStatus::Posted)
            ->where('balance_due', '>', 0)
            ->whereNot('invoice_type', CustomerInvoiceType::CreditNote)
            ->orderBy('due_date')
            ->get();

        $today = now()->toDateString();
        $overdueAmount = round((float) $openInvoices
            ->filter(fn (CustomerInvoice $invoice) => ($invoice->due_date ?? $invoice->invoice_date)->toDateString() < $today)
            ->sum('balance_due'), 2);

        $agingReport = $this->aging->build([
            'company_id' => $customer->company_id,
            'customer_id' => $customerId,
        ]);

        $agingRow = $agingReport['rows']->first();
        $agingBuckets = [
            'current' => $agingRow['current'] ?? 0,
            '1_30' => $agingRow['days_1_30'] ?? 0,
            '31_60' => $agingRow['days_31_60'] ?? 0,
            '61_90' => $agingRow['days_61_90'] ?? 0,
            '90_plus' => $agingRow['days_90_plus'] ?? 0,
        ];

        $oldestOutstanding = $openInvoices->first();

        return [
            'total_invoiced' => round($totalInvoiced, 2),
            'total_paid' => round($totalPaid, 2),
            'total_credit_notes' => round($totalCreditNotes, 2),
            'outstanding' => round($outstanding, 2),
            'credit_balance' => $creditWallet['remaining_credit'],
            'overdue_amount' => $overdueAmount,
            'collection_risk' => $this->resolveCollectionRisk($agingBuckets, $overdueAmount, $outstanding),
            'average_payment_days' => $this->averagePaymentDays($customerId),
            'oldest_outstanding_invoice' => $oldestOutstanding ? [
                'id' => $oldestOutstanding->id,
                'invoice_number' => $oldestOutstanding->invoice_number,
                'due_date' => $oldestOutstanding->due_date?->toDateString(),
                'balance_due' => round((float) $oldestOutstanding->balance_due, 2),
            ] : null,
            'aging' => [
                'as_of_date' => $agingReport['as_of_date'],
                'buckets' => $agingBuckets,
                'total' => round((float) ($agingRow['total'] ?? 0), 2),
            ],
            'collection' => [
                'invoice_count' => CustomerInvoice::query()
                    ->where('customer_id', $customerId)
                    ->whereNot('invoice_type', CustomerInvoiceType::CreditNote)
                    ->count(),
                'payment_count' => CustomerPayment::query()
                    ->where('customer_id', $customerId)
                    ->count(),
                'credit_note_count' => CustomerInvoice::query()
                    ->where('customer_id', $customerId)
                    ->where('invoice_type', CustomerInvoiceType::CreditNote)
                    ->count(),
                'deposit_count' => CustomerPayment::query()
                    ->where('customer_id', $customerId)
                    ->where('is_deposit', true)
                    ->where('status', CustomerPaymentStatus::Posted)
                    ->count(),
                'receipt_count' => CustomerPayment::query()
                    ->where('customer_id', $customerId)
                    ->where('status', CustomerPaymentStatus::Posted)
                    ->whereNotNull('receipt_number')
                    ->count(),
            ],
            'credit_wallet' => $creditWallet,
        ];
    }

    /**
     * @param  array<string, float>  $agingBuckets
     */
    protected function resolveCollectionRisk(array $agingBuckets, float $overdueAmount, float $outstanding): string
    {
        if ($agingBuckets['90_plus'] > 0 || ($outstanding > 0 && $overdueAmount / $outstanding >= 0.5)) {
            return 'high';
        }

        if ($agingBuckets['61_90'] > 0 || $agingBuckets['31_60'] > 0 || $overdueAmount > 0) {
            return 'medium';
        }

        return 'low';
    }

    protected function averagePaymentDays(int $customerId): ?int
    {
        $rows = DB::table('customer_payment_allocations')
            ->join('customer_payments', 'customer_payments.id', '=', 'customer_payment_allocations.customer_payment_id')
            ->join('customer_invoices', 'customer_invoices.id', '=', 'customer_payment_allocations.customer_invoice_id')
            ->where('customer_payments.customer_id', $customerId)
            ->where('customer_payments.status', CustomerPaymentStatus::Posted->value)
            ->select([
                'customer_invoices.invoice_date',
                'customer_payments.payment_date',
            ])
            ->get();

        if ($rows->isEmpty()) {
            return null;
        }

        $totalDays = $rows->sum(function ($row) {
            $invoiceDate = \Illuminate\Support\Carbon::parse($row->invoice_date);
            $paymentDate = \Illuminate\Support\Carbon::parse($row->payment_date);

            return max(0, (int) $invoiceDate->diffInDays($paymentDate));
        });

        return (int) round($totalDays / $rows->count());
    }
}
