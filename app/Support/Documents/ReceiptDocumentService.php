<?php

namespace App\Support\Documents;

use App\Models\Sales\CustomerPayment;
use App\Support\Documents\Presenters\ReceiptDocumentPresenter;
use App\Support\Export\PdfExportService;
use App\Support\Sales\CustomerPaymentReceiptService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReceiptDocumentService
{
    public function __construct(
        protected ReceiptDocumentPresenter $presenter,
        protected CustomerPaymentReceiptService $receipts,
        protected PdfExportService $pdfExports,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(CustomerPayment $payment, bool $includeInternalMeta = true): array
    {
        return $this->presenter->present($payment, $includeInternalMeta);
    }

    public function downloadPdf(CustomerPayment $payment): StreamedResponse
    {
        $document = $this->build($payment, includeInternalMeta: false);

        $html = view('documents.receipt.pdf', compact('document'))->render();

        return $this->pdfExports->downloadHtml(
            $document['documentNumber'],
            $html,
        );
    }

}
