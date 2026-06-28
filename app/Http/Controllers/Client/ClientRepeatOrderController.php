<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Client\Concerns\ResolvesClientCustomer;
use App\Http\Controllers\Controller;
use App\Models\Sales\SalesOrder;
use App\Services\Client\ClientPortalRepeatOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientRepeatOrderController extends Controller
{
    use ResolvesClientCustomer;

    public function __construct(
        protected ClientPortalRepeatOrderService $repeatOrders,
    ) {}

    public function index(): View
    {
        $customer = $this->clientCustomer();

        $orders = $this->repeatOrders->paginateEligibleOrders($customer);

        return view('client.repeat-orders.index', compact('customer', 'orders'));
    }

    public function store(Request $request, SalesOrder $order): RedirectResponse
    {
        $customer = $this->clientCustomer();
        $this->assertClientOwns($order, $customer);

        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $repeatRequest = $this->repeatOrders->requestRepeat(
            $order,
            $customer,
            $this->clientUser(),
            $validated['notes'] ?? null,
        );

        return redirect()
            ->route('client.repeat-orders.index')
            ->with('status', __('Repeat order request :reference submitted for staff approval.', [
                'reference' => '#'.$repeatRequest->id,
            ]));
    }
}
