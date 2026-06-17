<?php

namespace App\Support\Communications\Email;

use App\Models\Hr\PayrollPayslip;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\CustomerPayment;
use App\Models\Sales\Quotation;
use App\Support\Documents\InvoiceDocumentService;
use App\Support\Documents\QuotationDocumentService;
use App\Support\Documents\ReceiptDocumentService;
use App\Support\Export\PdfExportService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentEmailPdfService
{
    public function __construct(
        protected QuotationDocumentService $quotations,
        protected InvoiceDocumentService $invoices,
        protected ReceiptDocumentService $receipts,
        protected PdfExportService $pdfExports,
    ) {}

    /**
     * @return array{attachment_type: string, attachable_type: string, attachable_id: int, label: string, file_path: string}
     */
    public function quotationAttachment(Quotation $quotation): array
    {
        $document = $this->quotations->build($quotation);
        $html = view('documents.quotation.pdf', ['document' => $document])->render();
        $filename = Str::slug($document['documentNumber']).'.pdf';

        return $this->storePdf(
            html: $html,
            filename: $filename,
            attachmentType: 'quotation_pdf',
            attachable: $quotation,
        );
    }

    /**
     * @return array{attachment_type: string, attachable_type: string, attachable_id: int, label: string, file_path: string}
     */
    public function invoiceAttachment(CustomerInvoice $invoice): array
    {
        $document = $this->invoices->build($invoice);
        $html = view('documents.invoice.pdf', ['document' => $document])->render();
        $filename = Str::slug($document['documentNumber']).'.pdf';

        return $this->storePdf(
            html: $html,
            filename: $filename,
            attachmentType: 'invoice_pdf',
            attachable: $invoice,
        );
    }

    /**
     * @return array{attachment_type: string, attachable_type: string, attachable_id: int, label: string, file_path: string}
     */
    public function receiptAttachment(CustomerPayment $payment): array
    {
        $document = $this->receipts->build($payment, includeInternalMeta: false);
        $html = view('documents.receipt.pdf', ['document' => $document])->render();
        $filename = Str::slug($document['documentNumber']).'.pdf';

        return $this->storePdf(
            html: $html,
            filename: $filename,
            attachmentType: 'document',
            attachable: $payment,
        );
    }

    /**
     * @return array{attachment_type: string, attachable_type: string, attachable_id: int, label: string, file_path: string}
     */
    public function payslipAttachment(PayrollPayslip $payslip): array
    {
        $payslip->load(['employee.department', 'payrollRun', 'items']);
        $html = view('admin.hr.payroll.payslip-pdf', $this->pdfExports->payslipViewData($payslip))->render();
        $filename = Str::slug($payslip->reference ?? 'payslip-'.$payslip->id).'.pdf';

        return $this->storePdf(
            html: $html,
            filename: $filename,
            attachmentType: 'payslip_pdf',
            attachable: $payslip,
        );
    }

    /**
     * @return array{attachment_type: string, attachable_type: string, attachable_id: int, label: string, file_path: string}
     */
    protected function storePdf(string $html, string $filename, string $attachmentType, object $attachable): array
    {
        $disk = (string) config('communications.email_attachment_disk', 'local');
        $directory = 'email-attachments/'.now()->format('Y/m');
        $path = $directory.'/'.$filename;

        Storage::disk($disk)->put($path, $this->pdfExports->renderBrandedHtml($html));

        return [
            'attachment_type' => $attachmentType,
            'attachable_type' => $attachable::class,
            'attachable_id' => (int) $attachable->getKey(),
            'label' => $filename,
            'file_path' => $path,
        ];
    }
}
