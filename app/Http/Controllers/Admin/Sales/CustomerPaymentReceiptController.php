<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use App\Models\Sales\CustomerPayment;
use App\Models\Sales\SalesOrder;
use App\Support\Documents\ReceiptDocumentService;
use App\Support\Sales\CustomerPaymentReceiptService;
use App\Support\Sales\ReturnsToSalesDesk;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerPaymentReceiptController extends Controller
{
    use ReturnsToSalesDesk;

    public function __construct(
        protected CustomerPaymentReceiptService $receipts,
        protected ReceiptDocumentService $documents,
    ) {}

    public function show(Request $request, CustomerPayment $payment): View
    {
        $this->authorize('viewReceipt', $payment);

        $payment->load(['customer', 'allocations.invoice.salesOrder']);
        $document = $this->documents->build($payment);

        if ($this->wantsSalesDeskReturn($request)) {
            $salesOrder = $this->resolveSalesOrderForPayment($payment, $request);

            return view('admin.sales.desk.receipt-modal', [
                'payment' => $payment,
                'document' => $document,
                'deskReturnUrl' => $salesOrder && $payment->customer
                    ? route('admin.sales.desk', [
                        'customer' => $payment->customer->getRouteKey(),
                        'order' => $salesOrder->getRouteKey(),
                        'step' => 4,
                    ])
                    : route('admin.sales.desk'),
            ]);
        }

        return view('documents.receipt.show', compact('payment', 'document'));
    }

    public function pdf(CustomerPayment $payment): StreamedResponse
    {
        $this->authorize('downloadReceiptPdf', $payment);

        return $this->documents->downloadPdf($payment);
    }

    public function email(CustomerPayment $payment): RedirectResponse
    {
        $this->authorize('emailReceipt', $payment);

        $this->receipts->sendEmail($payment);

        return back()->with('status', __('Payment receipt emailed to customer.'));
    }

    public function sms(CustomerPayment $payment): RedirectResponse
    {
        $this->authorize('smsReceipt', $payment);

        $this->receipts->sendSmsLink($payment);

        return back()->with('status', __('Payment receipt link sent via SMS.'));
    }

    protected function resolveSalesOrderForPayment(CustomerPayment $payment, Request $request): ?SalesOrder
    {
        if ($request->filled('sales_order_id')) {
            return SalesOrder::query()->forTenant()->find($request->integer('sales_order_id'));
        }

        $invoice = $payment->allocations->first()?->invoice;

        return $invoice?->salesOrder;
    }
}
