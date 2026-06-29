<?php

namespace App\Services\Accounting;

use App\Enums\CustomerInvoiceStatus;
use App\Enums\FulfilmentStatus;
use App\Models\Production\ProductionFulfilment;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\CustomerInvoice;
use App\Support\Sales\SalesOrderBillingEligibilityService;

class DeliveryInvoiceService
{
    public function __construct(
        protected DeliveryInvoiceEligibilityService $deliveryEligibility,
        protected SalesOrderBillingEligibilityService $billingEligibility,
    ) {}

    /**
     * @return array{label: string, state: string, invoice_ready: bool, can_invoice: bool, active_invoice: ?CustomerInvoice}
     */
    public function billingStatusForJob(int $jobCardId): array
    {
        $jobCard = ProductionJobCard::query()->with('salesOrder')->find($jobCardId);

        if (! $jobCard) {
            return [
                'label' => __('Unknown'),
                'state' => 'na',
                'invoice_ready' => false,
                'can_invoice' => false,
                'active_invoice' => null,
            ];
        }

        $fulfilment = ProductionFulfilment::query()
            ->where('production_job_card_id', $jobCard->id)
            ->first();

        $invoiceReady = (bool) ($fulfilment?->invoice_ready);
        $activeInvoice = CustomerInvoice::query()
            ->where('production_job_card_id', $jobCard->id)
            ->whereNotIn('status', [CustomerInvoiceStatus::Cancelled])
            ->latest('id')
            ->first();

        $order = $jobCard->salesOrder;
        $canInvoice = $order
            && ($this->billingEligibility->isFulfilmentComplete($order)
                || $this->billingEligibility->isProductionComplete($order))
            && ! $activeInvoice;

        $label = match (true) {
            $activeInvoice?->status === CustomerInvoiceStatus::Posted && (float) $activeInvoice->balance_due <= 0 => __('Paid'),
            $activeInvoice?->status === CustomerInvoiceStatus::Posted => __('Invoiced'),
            $activeInvoice !== null => __('Invoice draft'),
            $fulfilment?->status === FulfilmentStatus::Collected => __('Collected — ready to invoice'),
            $fulfilment?->status === FulfilmentStatus::Delivered => __('Delivered — ready to invoice'),
            $invoiceReady => __('Ready for invoice'),
            $order && $this->billingEligibility->isProductionComplete($order) => __('Production complete — ready to invoice'),
            default => __('Awaiting fulfilment'),
        };

        return [
            'label' => $label,
            'state' => $invoiceReady ? 'ready' : 'pending',
            'invoice_ready' => $invoiceReady,
            'can_invoice' => $canInvoice,
            'active_invoice' => $activeInvoice,
        ];
    }
}
