<?php

namespace App\Support\Commercial;

use App\Enums\CustomerInvoiceStatus;
use App\Models\Sales\SalesOrder;
use Illuminate\Support\Collection;

class SalesOrderPaymentVisibility
{
    /**
     * @return array{
     *     status: string,
     *     label: string,
     *     variant: string,
     *     amount_paid: float,
     *     amount_outstanding: float,
     *     amount_invoiced: float,
     * }
     */
    public static function resolve(SalesOrder $order): array
    {
        $invoices = self::billableInvoices($order);

        if ($invoices->isEmpty()) {
            return [
                'status' => 'uninvoiced',
                'label' => __('Uninvoiced'),
                'variant' => 'neutral',
                'amount_paid' => 0.0,
                'amount_outstanding' => (float) $order->total_amount,
                'amount_invoiced' => 0.0,
            ];
        }

        $amountPaid = (float) $invoices->sum('amount_paid');
        $amountOutstanding = (float) $invoices->sum('balance_due');
        $amountInvoiced = (float) $invoices->sum('total_amount');

        if ($amountOutstanding <= 0 && $amountPaid > 0) {
            return [
                'status' => 'paid',
                'label' => __('Paid'),
                'variant' => 'success',
                'amount_paid' => $amountPaid,
                'amount_outstanding' => 0.0,
                'amount_invoiced' => $amountInvoiced,
            ];
        }

        if ($amountPaid > 0 && $amountOutstanding > 0) {
            return [
                'status' => 'partially_paid',
                'label' => __('Partially Paid'),
                'variant' => 'warning',
                'amount_paid' => $amountPaid,
                'amount_outstanding' => $amountOutstanding,
                'amount_invoiced' => $amountInvoiced,
            ];
        }

        return [
            'status' => 'unpaid',
            'label' => __('Unpaid'),
            'variant' => 'danger',
            'amount_paid' => $amountPaid,
            'amount_outstanding' => $amountOutstanding,
            'amount_invoiced' => $amountInvoiced,
        ];
    }

    /**
     * @return Collection<int, \App\Models\Sales\CustomerInvoice>
     */
    protected static function billableInvoices(SalesOrder $order): Collection
    {
        if ($order->relationLoaded('invoices')) {
            return $order->invoices
                ->whereIn('status', [CustomerInvoiceStatus::Approved, CustomerInvoiceStatus::Posted])
                ->values();
        }

        return $order->invoices()
            ->whereIn('status', [CustomerInvoiceStatus::Approved, CustomerInvoiceStatus::Posted])
            ->get();
    }
}
