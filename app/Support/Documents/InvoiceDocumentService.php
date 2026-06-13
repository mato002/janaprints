<?php

namespace App\Support\Documents;

use App\Models\Sales\CustomerInvoice;
use App\Support\Documents\Presenters\InvoiceDocumentPresenter;
use App\Support\Export\PdfExportService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvoiceDocumentService
{
    public function __construct(
        protected InvoiceDocumentPresenter $presenter,
        protected PdfExportService $pdfExports,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(CustomerInvoice $invoice): array
    {
        return $this->presenter->present($invoice);
    }

    public function downloadPdf(CustomerInvoice $invoice): StreamedResponse
    {
        $document = $this->build($invoice);

        $html = view('documents.invoice.pdf', compact('document'))->render();

        return $this->pdfExports->downloadHtml(
            $document['documentNumber'],
            $html,
        );
    }

}
