<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use App\Models\Sales\Quotation;
use App\Support\Documents\QuotationDocumentService;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QuotationDocumentController extends Controller
{
    public function __construct(
        protected QuotationDocumentService $documents,
    ) {}

    public function show(Quotation $quotation): View
    {
        $this->authorize('view', $quotation);

        $document = $this->documents->build($quotation);

        return view('documents.quotation.show', compact('quotation', 'document'));
    }

    public function pdf(Quotation $quotation): StreamedResponse
    {
        $this->authorize('view', $quotation);

        return $this->documents->downloadPdf($quotation);
    }

}
