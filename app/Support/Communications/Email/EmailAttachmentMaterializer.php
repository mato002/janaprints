<?php

namespace App\Support\Communications\Email;

use App\Enums\EmailAttachmentType;
use App\Models\Communications\EmailAttachment;
use App\Models\Communications\EmailMessage;
use App\Models\Hr\PayrollPayslip;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\CustomerPayment;
use App\Models\Sales\Quotation;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EmailAttachmentMaterializer
{
    public function __construct(
        protected DocumentEmailPdfService $pdfs,
    ) {}

    public function materialize(EmailMessage $message): void
    {
        $message->loadMissing('attachments');

        foreach ($message->attachments as $attachment) {
            if (filled($attachment->file_path)) {
                continue;
            }

            $resolved = $this->resolve($attachment);

            if ($resolved === null) {
                continue;
            }

            $attachment->update([
                'file_path' => $resolved['file_path'],
                'label' => $resolved['label'] ?? $attachment->label,
            ]);
        }
    }

    /**
     * @return array{file_path: string, label?: string}|null
     */
    protected function resolve(EmailAttachment $attachment): ?array
    {
        if (! $attachment->attachable_type || ! $attachment->attachable_id) {
            return null;
        }

        $type = $attachment->attachment_type instanceof EmailAttachmentType
            ? $attachment->attachment_type
            : EmailAttachmentType::tryFrom((string) $attachment->attachment_type);

        if ($type === null) {
            return null;
        }

        return match ($type) {
            EmailAttachmentType::PayslipPdf => $this->resolvePayslip($attachment),
            EmailAttachmentType::QuotationPdf => $this->resolveQuotation($attachment),
            EmailAttachmentType::InvoicePdf => $this->resolveInvoice($attachment),
            EmailAttachmentType::Document => $this->resolveReceipt($attachment),
            default => null,
        };
    }

    /**
     * @return array{file_path: string, label?: string}
     */
    protected function resolvePayslip(EmailAttachment $attachment): array
    {
        $payslip = PayrollPayslip::query()->find($attachment->attachable_id);

        if ($payslip === null) {
            throw ValidationException::withMessages([
                'attachment' => __('Payslip not found for email attachment.'),
            ]);
        }

        return $this->pdfs->payslipAttachment($payslip);
    }

    /**
     * @return array{file_path: string, label?: string}
     */
    protected function resolveQuotation(EmailAttachment $attachment): array
    {
        $quotation = Quotation::query()->find($attachment->attachable_id);

        if ($quotation === null) {
            throw ValidationException::withMessages([
                'attachment' => __('Quotation not found for email attachment.'),
            ]);
        }

        return $this->pdfs->quotationAttachment($quotation);
    }

    /**
     * @return array{file_path: string, label?: string}
     */
    protected function resolveInvoice(EmailAttachment $attachment): array
    {
        $invoice = CustomerInvoice::query()->find($attachment->attachable_id);

        if ($invoice === null) {
            throw ValidationException::withMessages([
                'attachment' => __('Invoice not found for email attachment.'),
            ]);
        }

        return $this->pdfs->invoiceAttachment($invoice);
    }

    /**
     * @return array{file_path: string, label?: string}
     */
    protected function resolveReceipt(EmailAttachment $attachment): array
    {
        $payment = CustomerPayment::query()->find($attachment->attachable_id);

        if ($payment === null) {
            throw ValidationException::withMessages([
                'attachment' => __('Receipt not found for email attachment.'),
            ]);
        }

        return $this->pdfs->receiptAttachment($payment);
    }

    /**
     * @return array{attachment_type: string, attachable_type: string, attachable_id: int, label: string}
     */
    public static function payslipStub(PayrollPayslip $payslip): array
    {
        $filename = Str::slug($payslip->reference ?? 'payslip-'.$payslip->id).'.pdf';

        return [
            'attachment_type' => EmailAttachmentType::PayslipPdf->value,
            'attachable_type' => PayrollPayslip::class,
            'attachable_id' => (int) $payslip->id,
            'label' => $filename,
        ];
    }
}
