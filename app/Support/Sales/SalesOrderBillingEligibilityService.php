<?php

namespace App\Support\Sales;

use App\Enums\CustomerInvoiceType;
use App\Enums\Dispatch\DeliveryNoteStatus;
use App\Enums\FulfilmentStatus;
use App\Enums\ProductionOutputStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Production\ProductionFulfilment;
use App\Models\Production\ProductionOutput;
use App\Models\Sales\SalesOrder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class SalesOrderBillingEligibilityService
{
    /**
     * @return array{eligible: bool, blockers: list<string>, fulfilment_ready: bool, production_complete: bool}
     */
    public function assess(SalesOrder $order, ?CustomerInvoiceType $invoiceType = null): array
    {
        $blockers = [];

        if (in_array($order->status, [SalesOrderStatus::Draft, SalesOrderStatus::Cancelled], true)) {
            $blockers[] = __('Sales order must be confirmed before invoicing.');
        }

        $fulfilmentReady = $this->isFulfilmentComplete($order);
        $productionComplete = $this->isProductionComplete($order);
        $type = $invoiceType ?? CustomerInvoiceType::Standard;
        $requiresFulfilment = in_array($type, [CustomerInvoiceType::Standard, CustomerInvoiceType::Partial], true);

        if ($requiresFulfilment && ! $fulfilmentReady && ! $productionComplete) {
            $blockers[] = __('Final invoice requires production completion (finished goods posted) or customer collection/delivery.');
        }

        return [
            'eligible' => $blockers === [],
            'blockers' => $blockers,
            'fulfilment_ready' => $fulfilmentReady,
            'production_complete' => $productionComplete,
        ];
    }

    public function assertCanInvoice(SalesOrder $order, CustomerInvoiceType $type): void
    {
        $result = $this->assess($order, $type);

        if (! $result['eligible']) {
            throw ValidationException::withMessages([
                'sales_order' => implode(' ', $result['blockers']),
            ]);
        }
    }

    public function isFulfilmentComplete(SalesOrder $order): bool
    {
        $order->loadMissing('jobCard');

        if (! $order->jobCard) {
            return ! in_array($order->status, [SalesOrderStatus::Draft, SalesOrderStatus::Cancelled], true);
        }

        if (! Schema::hasTable('production_fulfilments')) {
            return $order->status === SalesOrderStatus::Delivered;
        }

        $fulfilment = ProductionFulfilment::query()
            ->where('production_job_card_id', $order->jobCard->id)
            ->first();

        if ($fulfilment?->invoice_ready) {
            return true;
        }

        if ($fulfilment && in_array($fulfilment->status, [FulfilmentStatus::Collected, FulfilmentStatus::Delivered], true)) {
            return true;
        }

        if (Schema::hasTable('delivery_notes')) {
            $delivered = $order->jobCard->deliveryNotes()
                ->where('status', DeliveryNoteStatus::Delivered)
                ->where('invoice_ready', true)
                ->exists();

            if ($delivered) {
                return true;
            }
        }

        return $order->status === SalesOrderStatus::Delivered;
    }

    public function isProductionComplete(SalesOrder $order): bool
    {
        $order->loadMissing('jobCard');

        if (! $order->jobCard || ! Schema::hasTable('production_outputs')) {
            return false;
        }

        return ProductionOutput::query()
            ->where('production_job_card_id', $order->jobCard->id)
            ->where('completion_status', ProductionOutputStatus::Posted)
            ->exists();
    }
}
