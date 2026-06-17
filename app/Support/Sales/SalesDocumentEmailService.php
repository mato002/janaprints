<?php

namespace App\Support\Sales;

use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\CustomerPayment;
use App\Models\Sales\Quotation;
use App\Models\User;
use App\Support\Communications\Email\CorporateMailDispatcher;
use App\Support\Communications\Email\DocumentEmailPdfService;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\ValidationException;

class SalesDocumentEmailService
{
    public function __construct(
        protected CorporateMailDispatcher $mail,
        protected DocumentEmailPdfService $pdfs,
    ) {}

    public function sendQuotation(Quotation $quotation, ?User $actor = null): bool
    {
        $quotation->loadMissing(['customer', 'company']);

        $email = $quotation->customer?->email;

        if (! filled($email)) {
            return false;
        }

        $attachment = $this->pdfs->quotationAttachment($quotation);
        $actorId = (int) ($actor?->id ?? $quotation->prepared_by ?? $quotation->approved_by ?? 1);

        $message = $this->mail->dispatch([
            'company_id' => (int) $quotation->company_id,
            'branch_id' => $quotation->branch_id,
            'user_id' => $actorId,
            'to' => [[
                'email' => (string) $email,
                'name' => (string) ($quotation->customer?->company_name ?? $quotation->customer?->contact_person),
            ]],
            'subject' => __('Quotation :number', ['number' => $quotation->quotation_number]),
            'body' => $this->quotationBody($quotation),
            'sender_purpose' => 'quotation',
            'attachments' => [$attachment],
            'metadata' => [
                'module' => 'sales',
                'entity_type' => 'quotation',
                'entity_id' => $quotation->id,
                'document_number' => $quotation->quotation_number,
            ],
        ]);

        return $message !== null;
    }

    public function sendInvoice(CustomerInvoice $invoice, ?User $actor = null): bool
    {
        $invoice->loadMissing(['customer', 'company']);

        $email = $invoice->customer?->email;

        if (! filled($email)) {
            throw ValidationException::withMessages([
                'email' => __('Customer does not have an email address on file.'),
            ]);
        }

        $attachment = $this->pdfs->invoiceAttachment($invoice);
        $actorId = (int) ($actor?->id ?? $invoice->created_by ?? 1);

        $message = $this->mail->dispatch([
            'company_id' => (int) $invoice->company_id,
            'branch_id' => $invoice->branch_id,
            'user_id' => $actorId,
            'to' => [[
                'email' => (string) $email,
                'name' => (string) ($invoice->customer?->company_name ?? $invoice->customer?->contact_person),
            ]],
            'subject' => __('Invoice :number', ['number' => $invoice->invoice_number]),
            'body' => $this->invoiceBody($invoice),
            'sender_purpose' => 'invoice',
            'attachments' => [$attachment],
            'metadata' => [
                'module' => 'sales',
                'entity_type' => 'customer_invoice',
                'entity_id' => $invoice->id,
                'document_number' => $invoice->invoice_number,
            ],
        ]);

        return $message !== null;
    }

    /**
     * @param  array<string, mixed>  $receipt
     */
    public function sendReceipt(CustomerPayment $payment, array $receipt, ?User $actor = null): bool
    {
        $payment->loadMissing(['customer', 'company']);
        $email = $payment->customer?->email;

        if (! filled($email)) {
            throw ValidationException::withMessages([
                'email' => __('Customer does not have an email address on file.'),
            ]);
        }

        $attachment = $this->pdfs->receiptAttachment($payment);
        $actorId = (int) ($actor?->id ?? $payment->posted_by ?? $payment->created_by ?? 1);

        $message = $this->mail->dispatch([
            'company_id' => (int) $payment->company_id,
            'branch_id' => $payment->branch_id,
            'user_id' => $actorId,
            'to' => [[
                'email' => (string) $email,
                'name' => (string) ($payment->customer?->company_name ?? $payment->customer?->contact_person),
            ]],
            'subject' => __('Payment receipt :number', ['number' => $receipt['receipt_number']]),
            'body' => View::make('mail.customer-payment-receipt', [
                'payment' => $payment,
                'receipt' => $receipt,
            ])->render(),
            'sender_purpose' => 'receipt',
            'attachments' => [$attachment],
            'metadata' => [
                'module' => 'sales',
                'entity_type' => 'customer_payment',
                'entity_id' => $payment->id,
                'document_number' => $receipt['receipt_number'],
            ],
        ]);

        if ($message !== null) {
            $payment->update(['receipt_emailed_at' => now()]);
        }

        return $message !== null;
    }

    protected function quotationBody(Quotation $quotation): string
    {
        return '<p>'.e(__('Please find quotation :number attached.', [
            'number' => $quotation->quotation_number,
        ])).'</p>';
    }

    protected function invoiceBody(CustomerInvoice $invoice): string
    {
        return '<p>'.e(__('Please find invoice :number attached.', [
            'number' => $invoice->invoice_number,
        ])).'</p>';
    }
}
