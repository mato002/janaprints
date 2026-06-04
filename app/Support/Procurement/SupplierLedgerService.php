<?php

namespace App\Support\Procurement;

use App\Enums\SupplierBillStatus;
use App\Enums\SupplierBillType;
use App\Enums\SupplierPaymentStatus;
use App\Models\Procurement\SupplierBill;
use App\Models\Procurement\SupplierPayment;
use Illuminate\Support\Collection;

class SupplierLedgerService
{
    /**
     * @param  array{vendor_id: int, from_date?: ?string, to_date?: ?string}  $filters
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
        $vendorId = (int) $filters['vendor_id'];
        $fromDate = $filters['from_date'] ?? null;
        $toDate = $filters['to_date'] ?? null;

        $billQuery = SupplierBill::query()
            ->where('vendor_id', $vendorId)
            ->whereIn('status', [
                SupplierBillStatus::Posted->value,
                SupplierBillStatus::Paid->value,
            ])
            ->whereNot('bill_type', SupplierBillType::CreditNote->value);

        $creditQuery = SupplierBill::query()
            ->where('vendor_id', $vendorId)
            ->whereIn('status', [
                SupplierBillStatus::Posted->value,
                SupplierBillStatus::Paid->value,
            ])
            ->where('bill_type', SupplierBillType::CreditNote->value);

        $paymentQuery = SupplierPayment::query()
            ->where('vendor_id', $vendorId)
            ->where('status', SupplierPaymentStatus::Posted);

        $opening = 0.0;

        if ($fromDate) {
            $opening += (float) (clone $billQuery)->whereDate('bill_date', '<', $fromDate)->sum('total_amount');
            $opening -= (float) (clone $creditQuery)->whereDate('bill_date', '<', $fromDate)->sum('total_amount');
            $opening -= (float) (clone $paymentQuery)->whereDate('payment_date', '<', $fromDate)->sum('amount');

            $billQuery->whereDate('bill_date', '>=', $fromDate);
            $creditQuery->whereDate('bill_date', '>=', $fromDate);
            $paymentQuery->whereDate('payment_date', '>=', $fromDate);
        }

        if ($toDate) {
            $billQuery->whereDate('bill_date', '<=', $toDate);
            $creditQuery->whereDate('bill_date', '<=', $toDate);
            $paymentQuery->whereDate('payment_date', '<=', $toDate);
        }

        $entries = collect();
        $totalCharges = 0.0;
        $totalCredits = 0.0;

        foreach ($billQuery->orderBy('bill_date')->orderBy('id')->get() as $bill) {
            $amount = (float) $bill->total_amount;
            $totalCharges += $amount;
            $entries->push((object) [
                'date' => $bill->bill_date->toDateString(),
                'type' => 'bill',
                'reference' => $bill->bill_number,
                'description' => $bill->bill_type->label(),
                'debit' => $amount,
                'credit' => 0.0,
                'source_id' => $bill->id,
            ]);
        }

        foreach ($creditQuery->orderBy('bill_date')->orderBy('id')->get() as $bill) {
            $amount = (float) $bill->total_amount;
            $totalCredits += $amount;
            $entries->push((object) [
                'date' => $bill->bill_date->toDateString(),
                'type' => 'credit_note',
                'reference' => $bill->bill_number,
                'description' => __('Credit note'),
                'debit' => 0.0,
                'credit' => $amount,
                'source_id' => $bill->id,
            ]);
        }

        foreach ($paymentQuery->orderBy('payment_date')->orderBy('id')->get() as $payment) {
            $amount = (float) $payment->amount;
            $totalCredits += $amount;
            $entries->push((object) [
                'date' => $payment->payment_date->toDateString(),
                'type' => 'payment',
                'reference' => $payment->payment_number,
                'description' => $payment->payment_method->label(),
                'debit' => 0.0,
                'credit' => $amount,
                'source_id' => $payment->id,
            ]);
        }

        $sorted = $entries->sortBy([['date', 'asc'], ['type', 'asc']])->values();
        $balance = $opening;

        $sorted = $sorted->map(function ($row) use (&$balance) {
            $balance += $row->debit - $row->credit;
            $row->running_balance = round($balance, 2);

            return $row;
        });

        return [
            'entries' => $sorted,
            'opening_balance' => round($opening, 2),
            'closing_balance' => round($balance, 2),
            'total_charges' => round($totalCharges, 2),
            'total_credits' => round($totalCredits, 2),
        ];
    }
}
