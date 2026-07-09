<?php

namespace App\Http\Controllers\Client;

use App\Enums\SalesOrderStatus;
use App\Http\Controllers\Client\Concerns\ResolvesClientCustomer;
use App\Http\Controllers\Controller;
use App\Models\Sales\SalesOrder;
use App\Services\Client\ClientPortalOrderTrackingService;
use Illuminate\View\View;

class ClientOrderController extends Controller
{
    use ResolvesClientCustomer;

    public function __construct(
        protected ClientPortalOrderTrackingService $tracking,
    ) {}

    public function index(): View
    {
        $customer = $this->clientCustomer();

        $orders = SalesOrder::query()
            ->where('customer_id', $customer->id)
            ->where('status', '!=', SalesOrderStatus::Draft)
            ->with(['jobCard.fulfilment'])
            ->latest('order_date')
            ->paginate(12);

        $orders->getCollection()->transform(function (SalesOrder $order) {
            $order->tracking_summary = $this->tracking->track($order);

            return $order;
        });

        return view('client.orders.index', compact('customer', 'orders'));
    }

    public function show(SalesOrder $order): View
    {
        $customer = $this->clientCustomer();
        $this->assertClientOwns($order, $customer);

        $order->load(['items', 'quotation', 'jobCard.fulfilment', 'invoices']);
        $tracking = $this->tracking->track($order);

        $paymentReceipts = \App\Models\Sales\CustomerPayment::query()
            ->where('customer_id', $customer->id)
            ->where('status', \App\Enums\CustomerPaymentStatus::Posted)
            ->whereHas('allocations.invoice', fn ($q) => $q->where('sales_order_id', $order->id))
            ->latest('payment_date')
            ->get()
            ->map(fn ($payment) => [
                'label' => $payment->payment_number,
                'receipt' => app(\App\Support\Sales\CustomerPaymentReceiptService::class)->signedPublicUrl($payment),
            ]);

        $documents = [
            'quotation_pdf' => $order->quotation ? route('client.quotations.pdf', $order->quotation) : null,
            'invoices' => $order->invoices->map(fn ($invoice) => [
                'label' => $invoice->invoice_number,
                'pdf' => route('client.invoices.pdf', $invoice),
            ]),
            'payments' => $paymentReceipts,
        ];

        return view('client.orders.show', compact('customer', 'order', 'tracking', 'documents'));
    }
}
