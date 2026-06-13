<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use App\Models\Sales\CustomerInvoice;
use App\Support\Documents\InvoiceDocumentService;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvoiceDocumentController extends Controller
{
    public function __construct(
        protected InvoiceDocumentService $documents,
    ) {}

    public function show(CustomerInvoice $invoice): View
    {
        $this->authorize('view', $invoice);

        $document = $this->documents->build($invoice);

        return view('documents.invoice.show', compact('invoice', 'document'));
    }

    public function pdf(CustomerInvoice $invoice): StreamedResponse
    {
        $this->authorize('view', $invoice);

        return $this->documents->downloadPdf($invoice);
    }

}
