<?php

namespace App\Support\Sales;

use App\Enums\CustomerInvoiceStatus;
use App\Enums\CustomerInvoiceType;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\SalesOrder;
use Illuminate\Validation\ValidationException;

/**
 * Single authority for customer invoice creation from commercial sources.
 */
class CustomerInvoiceCreationAuthority
{
    public function __construct(
        protected CustomerInvoiceService $invoices,
    ) {}

    /**
     * @param  array<string, mixed>  $options
     */
    public function createFromSalesOrder(SalesOrder $order, int $userId, array $options = []): CustomerInvoiceCreationResult
    {
        $type = $options['invoice_type'] ?? CustomerInvoiceType::Standard;

        if ($type === CustomerInvoiceType::Standard) {
            $existing = $this->findOpenStandardInvoiceForSalesOrder($order);

            if ($existing !== null) {
                return new CustomerInvoiceCreationResult(
                    $existing,
                    wasExisting: true,
                    message: __('An open invoice already exists for this sales order.'),
                );
            }
        }

        $invoice = $this->invoices->createFromSalesOrder($order, $userId, $options);

        return new CustomerInvoiceCreationResult($invoice);
    }

    /**
     * @param  array<string, mixed>  $options
     */
    public function createFromJobCard(ProductionJobCard $jobCard, int $userId, array $options = []): CustomerInvoiceCreationResult
    {
        $existing = $this->findForJobCard($jobCard);

        if ($existing !== null) {
            return new CustomerInvoiceCreationResult(
                $existing,
                wasExisting: true,
                message: __('An invoice already exists for this job card.'),
            );
        }

        $invoice = $this->invoices->createFromJobCard($jobCard, $userId, $options);

        return new CustomerInvoiceCreationResult($invoice);
    }

    /**
     * @param  array<string, mixed>  $options
     */
    public function createFromDeliveryNote(DeliveryNote $note, int $userId, array $options = []): CustomerInvoiceCreationResult
    {
        $existing = $this->findForDeliveryNote($note);

        if ($existing !== null) {
            return new CustomerInvoiceCreationResult(
                $existing,
                wasExisting: true,
                message: __('An invoice already exists for this delivery note.'),
            );
        }

        try {
            $invoice = $this->invoices->createFromDeliveryNote($note, $userId, $options);
        } catch (ValidationException $exception) {
            if ($this->messagesIndicateAlreadyInvoiced($exception)) {
                $fallback = $this->findForSalesOrderFromDeliveryNote($note);

                if ($fallback !== null) {
                    return new CustomerInvoiceCreationResult(
                        $fallback,
                        wasExisting: true,
                        message: __('This order has already been invoiced.'),
                    );
                }
            }

            throw $exception;
        }

        return new CustomerInvoiceCreationResult($invoice);
    }

    public function findForDeliveryNote(DeliveryNote $note): ?CustomerInvoice
    {
        return CustomerInvoice::query()
            ->where('company_id', $note->company_id)
            ->where('delivery_note_id', $note->id)
            ->whereNot('status', CustomerInvoiceStatus::Cancelled)
            ->orderByDesc('id')
            ->first();
    }

    public function findForJobCard(ProductionJobCard $jobCard): ?CustomerInvoice
    {
        return CustomerInvoice::query()
            ->where('company_id', $jobCard->company_id)
            ->where('production_job_card_id', $jobCard->id)
            ->whereNot('status', CustomerInvoiceStatus::Cancelled)
            ->orderByDesc('id')
            ->first();
    }

    public function findOpenStandardInvoiceForSalesOrder(SalesOrder $order): ?CustomerInvoice
    {
        return CustomerInvoice::query()
            ->where('company_id', $order->company_id)
            ->where('sales_order_id', $order->id)
            ->where('invoice_type', CustomerInvoiceType::Standard)
            ->whereIn('status', [
                CustomerInvoiceStatus::Draft,
                CustomerInvoiceStatus::Approved,
                CustomerInvoiceStatus::Posted,
            ])
            ->whereNull('delivery_note_id')
            ->orderByDesc('id')
            ->first();
    }

    protected function findForSalesOrderFromDeliveryNote(DeliveryNote $note): ?CustomerInvoice
    {
        if (! $note->sales_order_id) {
            return null;
        }

        return CustomerInvoice::query()
            ->where('company_id', $note->company_id)
            ->where('sales_order_id', $note->sales_order_id)
            ->whereNot('status', CustomerInvoiceStatus::Cancelled)
            ->orderByDesc('id')
            ->first();
    }

    protected function messagesIndicateAlreadyInvoiced(ValidationException $exception): bool
    {
        $messages = collect($exception->errors())->flatten()->implode(' ');

        return str_contains(strtolower($messages), 'nothing left to bill')
            || str_contains(strtolower($messages), 'already been invoiced')
            || str_contains(strtolower($messages), 'no remaining billable');
    }
}
