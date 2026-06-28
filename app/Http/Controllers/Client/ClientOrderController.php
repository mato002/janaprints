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

        $order->load(['items', 'quotation', 'jobCard.fulfilment']);
        $tracking = $this->tracking->track($order);

        return view('client.orders.show', compact('customer', 'order', 'tracking'));
    }
}
