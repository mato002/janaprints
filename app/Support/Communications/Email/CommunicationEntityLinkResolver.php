<?php

namespace App\Support\Communications\Email;

use App\Models\Artwork\ArtworkRequest;
use App\Models\Crm\Customer;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Employee;
use App\Models\Hr\PayrollPayslip;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\CustomerPayment;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use Illuminate\Support\Facades\Route;

class CommunicationEntityLinkResolver
{
    /**
     * @param  array<string, mixed>  $metadata
     * @return array{label: string, url: string|null, type: string}|null
     */
    public function resolve(array $metadata): ?array
    {
        $entityType = (string) ($metadata['entity_type'] ?? '');
        $entityId = (int) ($metadata['entity_id'] ?? 0);
        $documentNumber = (string) ($metadata['document_number'] ?? '');

        if ($entityType === '' && $documentNumber === '') {
            return null;
        }

        $label = $documentNumber !== '' ? $documentNumber : null;
        $url = null;
        $type = $entityType;

        return match ($entityType) {
            'quotation' => $this->link(
                $label ?? $this->numberFor(Quotation::class, $entityId, 'quotation_number', 'QT-'),
                $this->route('admin.quotations.show', $entityId),
                __('Quotation'),
            ),
            'customer_invoice' => $this->link(
                $label ?? $this->numberFor(CustomerInvoice::class, $entityId, 'invoice_number', 'INV-'),
                $this->route('admin.invoices.show', $entityId),
                __('Invoice'),
            ),
            'customer_payment' => $this->link(
                $label ?? $this->numberFor(CustomerPayment::class, $entityId, 'payment_number', 'RCP-'),
                $this->route('admin.payments.show', $entityId),
                __('Receipt'),
            ),
            'sales_order' => $this->link(
                $label ?? $this->numberFor(SalesOrder::class, $entityId, 'order_number', 'SO-'),
                $this->route('admin.sales-orders.show', $entityId),
                __('Sales order'),
            ),
            'delivery_note' => $this->link(
                $label ?? $this->numberFor(DeliveryNote::class, $entityId, 'delivery_note_number', 'DN-'),
                $this->route('admin.dispatch.delivery-notes.show', $entityId),
                __('Delivery note'),
            ),
            'artwork_request' => $this->link(
                $label ?? $this->numberFor(ArtworkRequest::class, $entityId, 'request_number', 'ART-'),
                $this->route('admin.artwork.show', $entityId),
                __('Artwork'),
            ),
            'production_job_card' => $this->link(
                $label ?? $this->numberFor(ProductionJobCard::class, $entityId, 'job_card_number', 'JOB-'),
                $this->route('admin.production.job-cards.show', $entityId),
                __('Job'),
            ),
            'customer' => $this->link(
                $label ?? $this->numberFor(Customer::class, $entityId, 'customer_code', 'CUST-'),
                $this->route('admin.crm.customers.show', $entityId),
                __('Customer'),
            ),
            'employee' => $this->link(
                $label ?? __('Employee #:id', ['id' => $entityId]),
                $this->route('admin.employees.edit', $entityId),
                __('Employee'),
            ),
            'payroll_payslip' => $this->link(
                $label ?? $this->numberFor(PayrollPayslip::class, $entityId, 'reference', 'PS-'),
                $this->route('admin.hr.payroll.payslip.show', $entityId),
                __('Payslip'),
            ),
            'user' => $this->link(
                $label ?? User::query()->find($entityId)?->name ?? __('User #:id', ['id' => $entityId]),
                $this->route('admin.users.show', $entityId),
                __('User'),
            ),
            'public_quote_request' => $this->link(
                $label ?? __('Quote request #:id', ['id' => $entityId]),
                $this->route('admin.public-quote-requests.show', $entityId),
                __('Quote request'),
            ),
            'public_contact_message' => $this->link(
                $label ?? __('Contact #:id', ['id' => $entityId]),
                $this->route('admin.public-contact-messages.show', $entityId),
                __('Contact message'),
            ),
            default => $label !== null
                ? $this->link($label, null, str($entityType)->replace('_', ' ')->title()->toString())
                : null,
        };
    }

    /**
     * @return array{label: string, url: string|null, type: string}
     */
    protected function link(string $label, ?string $url, string $type): array
    {
        return [
            'label' => $label,
            'url' => $url,
            'type' => $type,
        ];
    }

    protected function route(string $name, int $entityId): ?string
    {
        if ($entityId <= 0 || ! Route::has($name)) {
            return null;
        }

        return route($name, $entityId);
    }

    protected function numberFor(string $modelClass, int $entityId, string $column, string $prefix): string
    {
        if ($entityId <= 0) {
            return $prefix.'—';
        }

        $value = $modelClass::query()->whereKey($entityId)->value($column);

        return filled($value) ? (string) $value : $prefix.$entityId;
    }
}
