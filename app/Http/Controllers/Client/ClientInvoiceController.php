<?php

namespace App\Http\Controllers\Client;

use App\Enums\CustomerInvoiceStatus;
use App\Http\Controllers\Client\Concerns\ResolvesClientCustomer;
use App\Http\Controllers\Controller;
use App\Models\Sales\CustomerInvoice;
use App\Support\Documents\InvoiceDocumentService;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClientInvoiceController extends Controller
{
    use ResolvesClientCustomer;

    public function __construct(
        protected InvoiceDocumentService $documents,
    ) {}

    public function index(): View
    {
        $customer = $this->clientCustomer();

        $invoices = CustomerInvoice::query()
            ->where('customer_id', $customer->id)
            ->where('status', CustomerInvoiceStatus::Posted)
            ->latest('invoice_date')
            ->paginate(12);

        return view('client.invoices.index', compact('customer', 'invoices'));
    }

    public function show(CustomerInvoice $invoice): View
    {
        $customer = $this->clientCustomer();
        $this->assertClientOwns($invoice, $customer);

        $invoice->load(['items', 'salesOrder']);

        return view('client.invoices.show', compact('customer', 'invoice'));
    }

    public function pdf(CustomerInvoice $invoice): StreamedResponse
    {
        $customer = $this->clientCustomer();
        $this->assertClientOwns($invoice, $customer);

        return $this->documents->downloadPdf($invoice);
    }
}
