<?php

namespace App\Support\Communications;

use App\Enums\DomainCommunicationEvent;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Crm\Customer;
use App\Models\Crm\Lead;
use App\Models\Crm\LeadFollowUp;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\CustomerPayment;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use Illuminate\Database\Eloquent\Model;

class CommunicationEventContext
{
    /**
     * @return array{
     *     company_id: int,
     *     branch_id: ?int,
     *     source_type: string,
     *     source_id: int,
     *     subject_label: string,
     *     customer_id: ?int,
     *     customer_name: ?string,
     *     customer_email: ?string,
     *     customer_phone: ?string,
     * }
     */
    public function resolveSubject(Model $subject): array
    {
        $resolved = match (true) {
            $subject instanceof Customer => $this->fromCustomer($subject),
            $subject instanceof Lead => $this->fromLead($subject),
            $subject instanceof Quotation => $this->fromQuotation($subject),
            $subject instanceof ArtworkRequest => $this->fromArtworkRequest($subject),
            $subject instanceof SalesOrder => $this->fromSalesOrder($subject),
            $subject instanceof CustomerInvoice => $this->fromCustomerInvoice($subject),
            $subject instanceof CustomerPayment => $this->fromCustomerPayment($subject),
            $subject instanceof DeliveryNote => $this->fromDeliveryNote($subject),
            $subject instanceof LeadFollowUp => $this->fromLeadFollowUp($subject),
            default => [
                'company_id' => (int) ($subject->company_id ?? 0),
                'branch_id' => $subject->branch_id ?? null,
                'source_type' => $subject->getTable(),
                'source_id' => (int) $subject->getKey(),
                'subject_label' => class_basename($subject).' #'.$subject->getKey(),
                'customer_id' => null,
                'customer_name' => null,
                'customer_email' => null,
                'customer_phone' => null,
            ],
        };

        unset($resolved['metadata']);

        return $resolved;
    }

    /**
     * @param  array<string, mixed>  $metadata
     * @return array{
     *     company_id: int,
     *     branch_id: ?int,
     *     source_type: string,
     *     source_id: int,
     *     subject_label: string,
     *     customer_id: ?int,
     *     customer_name: ?string,
     *     customer_email: ?string,
     *     customer_phone: ?string,
     *     metadata: array<string, mixed>,
     * }
     */
    public function resolve(DomainCommunicationEvent $event, Model $subject, array $metadata = []): array
    {
        $base = match (true) {
            $subject instanceof Customer => $this->fromCustomer($subject),
            $subject instanceof Lead => $this->fromLead($subject),
            $subject instanceof Quotation => $this->fromQuotation($subject),
            $subject instanceof ArtworkRequest => $this->fromArtworkRequest($subject),
            $subject instanceof SalesOrder => $this->fromSalesOrder($subject),
            $subject instanceof CustomerInvoice => $this->fromCustomerInvoice($subject),
            $subject instanceof CustomerPayment => $this->fromCustomerPayment($subject),
            $subject instanceof DeliveryNote => $this->fromDeliveryNote($subject),
            $subject instanceof LeadFollowUp => $this->fromLeadFollowUp($subject),
            default => [
                'company_id' => (int) ($subject->company_id ?? 0),
                'branch_id' => $subject->branch_id ?? null,
                'source_type' => $subject->getTable(),
                'source_id' => (int) $subject->getKey(),
                'subject_label' => class_basename($subject).' #'.$subject->getKey(),
                'customer_id' => null,
                'customer_name' => null,
                'customer_email' => null,
                'customer_phone' => null,
            ],
        };

        $base['metadata'] = array_merge($base['metadata'] ?? [], $metadata, [
            'domain_event' => $event->value,
            'template_category' => $event->templateCategory()?->value,
            'email_automation_event' => $event->emailAutomationEvent()?->value,
            'whatsapp_automation_event' => $event->whatsappAutomationEvent()?->value,
            'notification_type' => $event->notificationType()?->value,
        ]);

        return $base;
    }

    /**
     * @return array<string, mixed>
     */
    protected function fromCustomer(Customer $customer): array
    {
        return [
            'company_id' => (int) $customer->company_id,
            'branch_id' => $customer->branch_id,
            'source_type' => 'customer',
            'source_id' => (int) $customer->id,
            'subject_label' => $customer->company_name,
            'customer_id' => (int) $customer->id,
            'customer_name' => $customer->company_name,
            'customer_email' => $customer->email,
            'customer_phone' => $customer->phone,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function fromLead(Lead $lead): array
    {
        $lead->loadMissing('customer');

        return [
            'company_id' => (int) $lead->company_id,
            'branch_id' => $lead->branch_id,
            'source_type' => 'lead',
            'source_id' => (int) $lead->id,
            'subject_label' => $lead->lead_name,
            'customer_id' => $lead->customer_id,
            'customer_name' => $lead->customer?->company_name ?? $lead->company_name ?? $lead->lead_name,
            'customer_email' => $lead->customer?->email ?? $lead->email,
            'customer_phone' => $lead->customer?->phone ?? $lead->phone,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function fromQuotation(Quotation $quotation): array
    {
        $quotation->loadMissing('customer');

        return [
            'company_id' => (int) $quotation->company_id,
            'branch_id' => $quotation->branch_id,
            'source_type' => 'quotation',
            'source_id' => (int) $quotation->id,
            'subject_label' => $quotation->quotation_number,
            'customer_id' => $quotation->customer_id,
            'customer_name' => $quotation->customer?->company_name,
            'customer_email' => $quotation->customer?->email,
            'customer_phone' => $quotation->customer?->phone,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function fromArtworkRequest(ArtworkRequest $request): array
    {
        $request->loadMissing('customer');

        return [
            'company_id' => (int) $request->company_id,
            'branch_id' => $request->branch_id,
            'source_type' => 'artwork_request',
            'source_id' => (int) $request->id,
            'subject_label' => $request->request_number,
            'customer_id' => $request->customer_id,
            'customer_name' => $request->customer?->company_name,
            'customer_email' => $request->customer?->email,
            'customer_phone' => $request->customer?->phone,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function fromSalesOrder(SalesOrder $order): array
    {
        $order->loadMissing('customer');

        return [
            'company_id' => (int) $order->company_id,
            'branch_id' => $order->branch_id,
            'source_type' => 'sales_order',
            'source_id' => (int) $order->id,
            'subject_label' => $order->order_number,
            'customer_id' => $order->customer_id,
            'customer_name' => $order->customer?->company_name,
            'customer_email' => $order->customer?->email,
            'customer_phone' => $order->customer?->phone,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function fromCustomerInvoice(CustomerInvoice $invoice): array
    {
        $invoice->loadMissing('customer');

        return [
            'company_id' => (int) $invoice->company_id,
            'branch_id' => $invoice->branch_id,
            'source_type' => 'customer_invoice',
            'source_id' => (int) $invoice->id,
            'subject_label' => $invoice->invoice_number,
            'customer_id' => $invoice->customer_id,
            'customer_name' => $invoice->customer?->company_name,
            'customer_email' => $invoice->customer?->email,
            'customer_phone' => $invoice->customer?->phone,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function fromCustomerPayment(CustomerPayment $payment): array
    {
        $payment->loadMissing('customer');

        return [
            'company_id' => (int) $payment->company_id,
            'branch_id' => $payment->branch_id,
            'source_type' => 'customer_payment',
            'source_id' => (int) $payment->id,
            'subject_label' => $payment->payment_number,
            'customer_id' => $payment->customer_id,
            'customer_name' => $payment->customer?->company_name,
            'customer_email' => $payment->customer?->email,
            'customer_phone' => $payment->customer?->phone,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function fromDeliveryNote(DeliveryNote $note): array
    {
        $note->loadMissing('customer');

        return [
            'company_id' => (int) $note->company_id,
            'branch_id' => $note->branch_id,
            'source_type' => 'delivery_note',
            'source_id' => (int) $note->id,
            'subject_label' => $note->delivery_note_number,
            'customer_id' => $note->customer_id,
            'customer_name' => $note->customer?->company_name ?? $note->recipient_name,
            'customer_email' => $note->customer?->email,
            'customer_phone' => $note->customer?->phone ?? $note->recipient_phone,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function fromLeadFollowUp(LeadFollowUp $followUp): array
    {
        $followUp->loadMissing(['lead.customer', 'assignee']);

        return [
            'company_id' => (int) $followUp->company_id,
            'branch_id' => $followUp->branch_id,
            'source_type' => 'lead_follow_up',
            'source_id' => (int) $followUp->id,
            'subject_label' => $followUp->lead?->lead_name ?? __('Follow-up'),
            'customer_id' => $followUp->lead?->customer_id,
            'customer_name' => $followUp->lead?->customer?->company_name ?? $followUp->lead?->lead_name,
            'customer_email' => $followUp->lead?->customer?->email ?? $followUp->lead?->email,
            'customer_phone' => $followUp->lead?->customer?->phone ?? $followUp->lead?->phone,
            'metadata' => [
                'assigned_to_user_id' => $followUp->assigned_to,
                'scheduled_at' => $followUp->scheduled_at?->toIso8601String(),
            ],
        ];
    }
}
