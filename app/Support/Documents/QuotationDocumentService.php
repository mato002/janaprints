<?php

namespace App\Support\Documents;

use App\Models\Sales\Quotation;
use App\Support\Documents\Presenters\QuotationDocumentPresenter;
use App\Support\Export\PdfExportService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QuotationDocumentService
{
    public function __construct(
        protected QuotationDocumentPresenter $presenter,
        protected PdfExportService $pdfExports,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(Quotation $quotation): array
    {
        return $this->presenter->present($quotation);
    }

    public function downloadPdf(Quotation $quotation): StreamedResponse
    {
        $document = $this->build($quotation);

        $html = view('documents.quotation.pdf', compact('document'))->render();

        return $this->pdfExports->downloadHtml(
            $document['documentNumber'],
            $html,
        );
    }

}
