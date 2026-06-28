<?php

namespace App\Support\Sales;

use App\Enums\CustomerInvoiceStatus;
use App\Enums\CustomerInvoiceType;
use App\Enums\SalesOrderBillingType;
use App\Enums\SalesOrderFinancialStatus;
use App\Enums\SalesOrderStatus;
use App\Support\Commercial\SalesOrderPaymentVisibility;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\SalesOrder;

class SalesOrderFinancialStatusService
{
    public function __construct(
        protected SalesOrderBillingEligibilityService $billingEligibility,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function snapshot(SalesOrder $order): array
    {
        $order->loadMissing(['invoices']);

        $financialStatus = $this->resolveStatus($order);
        $payment = SalesOrderPaymentVisibility::resolve($order);
        $billing = $this->billingEligibility->assess($order);
        $deposit = $this->depositSummary($order);

        return [
            'financial_status' => $financialStatus,
            'financial_status_label' => $financialStatus->label(),
            'financial_status_variant' => $financialStatus->badgeVariant(),
            'payment' => $payment,
            'billing_eligibility' => $billing,
            'deposit' => $deposit,
            'can_close' => $this->canClose($order),
            'closure_blockers' => $this->closureBlockers($order),
        ];
    }

    public function canClose(SalesOrder $order): bool
    {
        return $this->closureBlockers($order) === [];
    }

    /**
     * @return list<string>
     */
    public function closureBlockers(SalesOrder $order): array
    {
        if ($order->status === SalesOrderStatus::Closed) {
            return [__('Sales order is already closed.')];
        }

        $blockers = [];

        if ($order->status !== SalesOrderStatus::Delivered) {
            $blockers[] = __('Order must be delivered before it can be closed.');
        }

        if ($order->remainingInvoiceTotal() > 0.01) {
            $blockers[] = __('Order must be fully invoiced before closing.');
        }

        $payment = SalesOrderPaymentVisibility::resolve($order);

        if (($payment['status'] ?? '') !== 'paid') {
            if ((float) ($payment['amount_invoiced'] ?? 0) <= 0) {
                $blockers[] = __('Post customer invoices before closing the order.');
            } else {
                $blockers[] = __('Outstanding balance must be settled before closing the order.');
            }
        }

        return $blockers;
    }

    public function assertCanClose(SalesOrder $order): void
    {
        $blockers = $this->closureBlockers($order);

        if ($blockers !== []) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'workflow' => implode(' ', $blockers),
            ]);
        }
    }

    public function resolveStatus(SalesOrder $order): SalesOrderFinancialStatus
    {
        if ($order->status === SalesOrderStatus::Closed) {
            return SalesOrderFinancialStatus::Closed;
        }

        $postedInvoices = $this->postedInvoices($order);
        $invoicedTotal = (float) $order->invoiced_total;
        $orderTotal = (float) $order->total_amount;

        if ($invoicedTotal <= 0) {
            return SalesOrderFinancialStatus::NotInvoiced;
        }

        $amountPaid = (float) $postedInvoices->sum('amount_paid');
        $outstanding = (float) $postedInvoices->sum('balance_due');

        if ($outstanding <= 0 && $amountPaid > 0) {
            return SalesOrderFinancialStatus::Paid;
        }

        if ($amountPaid > 0 && $outstanding > 0) {
            return SalesOrderFinancialStatus::PartiallyPaid;
        }

        if ($invoicedTotal >= $orderTotal - 0.01) {
            return SalesOrderFinancialStatus::FullyInvoiced;
        }

        return SalesOrderFinancialStatus::PartiallyInvoiced;
    }

    /**
     * @return array<string, mixed>
     */
    public function depositSummary(SalesOrder $order): array
    {
        $required = (float) ($order->required_deposit_amount ?? 0);

        if ($required <= 0 && $order->billing_type) {
            $percent = $order->billing_type instanceof SalesOrderBillingType
                ? $order->billing_type->depositPercent()
                : SalesOrderBillingType::tryFrom((string) $order->billing_type)?->depositPercent();

            if ($percent !== null) {
                $required = round((float) $order->total_amount * $percent / 100, 2);
            }
        }

        $invoiced = (float) ($order->deposit_invoiced_amount ?? 0);
        $paid = (float) ($order->deposit_paid_amount ?? 0);

        return [
            'required' => $required,
            'invoiced' => $invoiced,
            'paid' => $paid,
            'outstanding' => round(max(0, $required - $paid), 2),
            'billing_type' => $order->billing_type?->label() ?? SalesOrderBillingType::Net30->label(),
            'payment_terms_days' => (int) ($order->payment_terms_days ?? 30),
        ];
    }

    public function syncDepositAmounts(SalesOrder $order): void
    {
        $depositInvoiced = (float) CustomerInvoice::query()
            ->where('sales_order_id', $order->id)
            ->where('invoice_type', CustomerInvoiceType::Deposit)
            ->whereIn('status', [CustomerInvoiceStatus::Approved, CustomerInvoiceStatus::Posted])
            ->sum('total_amount');

        $depositPaid = (float) CustomerInvoice::query()
            ->where('sales_order_id', $order->id)
            ->where('invoice_type', CustomerInvoiceType::Deposit)
            ->where('status', CustomerInvoiceStatus::Posted)
            ->sum('amount_paid');

        $billingType = $order->billing_type ?? SalesOrderBillingType::Net30;
        $required = (float) ($billingType->depositPercent() !== null
            ? round((float) $order->total_amount * $billingType->depositPercent() / 100, 2)
            : 0);

        $order->update([
            'required_deposit_amount' => $required,
            'deposit_invoiced_amount' => round($depositInvoiced, 2),
            'deposit_paid_amount' => round($depositPaid, 2),
        ]);
    }

    protected function postedInvoices(SalesOrder $order): \Illuminate\Support\Collection
    {
        if ($order->relationLoaded('invoices')) {
            return $order->invoices
                ->where('status', CustomerInvoiceStatus::Posted)
                ->filter(fn (CustomerInvoice $invoice) => $invoice->invoice_type !== CustomerInvoiceType::CreditNote)
                ->values();
        }

        return $order->invoices()
            ->where('status', CustomerInvoiceStatus::Posted)
            ->whereNot('invoice_type', CustomerInvoiceType::CreditNote)
            ->get();
    }
}
