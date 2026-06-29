<?php

namespace App\Support\Sales;

use App\Enums\CustomerInvoiceStatus;
use App\Enums\CustomerInvoiceType;
use App\Enums\CustomerPaymentStatus;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\CustomerPayment;
use Illuminate\Support\Collection;

class CustomerLedgerService
{
    /**
     * @param  array{customer_id: int, from_date?: ?string, to_date?: ?string}  $filters
     * @return array{
     *     entries: Collection<int, object>,
     *     opening_balance: float,
     *     closing_balance: float,
     *     total_charges: float,
     *     total_credits: float
     * }
     */
    public function build(array $filters): array
    {
        $customerId = (int) $filters['customer_id'];
        $fromDate = $filters['from_date'] ?? null;
        $toDate = $filters['to_date'] ?? null;

        $invoiceQuery = CustomerInvoice::query()
            ->where('customer_id', $customerId)
            ->where('status', CustomerInvoiceStatus::Posted);

        $paymentQuery = CustomerPayment::query()
            ->where('customer_id', $customerId)
            ->where('status', CustomerPaymentStatus::Posted);

        $opening = 0.0;

        if ($fromDate) {
            $opening += (float) (clone $invoiceQuery)
                ->whereNot('invoice_type', CustomerInvoiceType::CreditNote)
                ->whereDate('invoice_date', '<', $fromDate)
                ->sum('total_amount');

            $opening -= (float) (clone $invoiceQuery)
                ->where('invoice_type', CustomerInvoiceType::CreditNote)
                ->whereDate('invoice_date', '<', $fromDate)
                ->sum('total_amount');

            $opening -= (float) (clone $paymentQuery)
                ->whereDate('payment_date', '<', $fromDate)
                ->sum('amount');

            $invoiceQuery->whereDate('invoice_date', '>=', $fromDate);
            $paymentQuery->whereDate('payment_date', '>=', $fromDate);
        }

        if ($toDate) {
            $invoiceQuery->whereDate('invoice_date', '<=', $toDate);
            $paymentQuery->whereDate('payment_date', '<=', $toDate);
        }

        $entries = collect();

        foreach ($invoiceQuery->orderBy('invoice_date')->orderBy('id')->get() as $invoice) {
            $isCreditNote = $invoice->invoice_type === CustomerInvoiceType::CreditNote;

            $entries->push((object) [
                'date' => $invoice->invoice_date->toDateString(),
                'type' => $isCreditNote ? 'credit_note' : 'invoice',
                'reference' => $invoice->invoice_number,
                'description' => $invoice->invoice_type->label(),
                'debit' => $isCreditNote ? 0.0 : (float) $invoice->total_amount,
                'credit' => $isCreditNote ? (float) $invoice->total_amount : 0.0,
                'source_id' => $invoice->id,
            ]);
        }

        foreach ($paymentQuery->orderBy('payment_date')->orderBy('id')->get() as $payment) {
            $entries->push((object) [
                'date' => $payment->payment_date->toDateString(),
                'type' => 'payment',
                'reference' => $payment->payment_number,
                'description' => $payment->payment_method->label().($payment->is_deposit ? ' ('.__('Deposit').')' : ''),
                'debit' => 0.0,
                'credit' => (float) $payment->amount,
                'source_id' => $payment->id,
            ]);
        }

        $entries = $entries->sortBy([
            ['date', 'asc'],
            ['type', 'asc'],
        ])->values();

        $running = $opening;
        $totalCharges = 0.0;
        $totalCredits = 0.0;

        $entries = $entries->map(function ($entry) use (&$running, &$totalCharges, &$totalCredits) {
            $running += $entry->debit - $entry->credit;
            $totalCharges += $entry->debit;
            $totalCredits += $entry->credit;
            $entry->balance = round($running, 2);

            return $entry;
        });

        return [
            'entries' => $entries,
            'opening_balance' => round($opening, 2),
            'closing_balance' => round($running, 2),
            'total_charges' => round($totalCharges, 2),
            'total_credits' => round($totalCredits, 2),
        ];
    }

    public function closingBalance(int $customerId, ?string $asOfDate = null): float
    {
        $filters = ['customer_id' => $customerId];

        if ($asOfDate !== null) {
            $filters['to_date'] = $asOfDate;
        }

        return $this->build($filters)['closing_balance'];
    }
}
