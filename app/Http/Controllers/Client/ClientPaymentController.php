<?php

namespace App\Http\Controllers\Client;

use App\Enums\CustomerPaymentStatus;
use App\Http\Controllers\Client\Concerns\ResolvesClientCustomer;
use App\Http\Controllers\Controller;
use App\Models\Sales\CustomerPayment;
use App\Support\Sales\CustomerPaymentReceiptService;
use Illuminate\View\View;

class ClientPaymentController extends Controller
{
    use ResolvesClientCustomer;

    public function __construct(
        protected CustomerPaymentReceiptService $receipts,
    ) {}

    public function index(): View
    {
        $customer = $this->clientCustomer();

        $payments = CustomerPayment::query()
            ->where('customer_id', $customer->id)
            ->where('status', CustomerPaymentStatus::Posted)
            ->latest('payment_date')
            ->paginate(12)
            ->through(fn (CustomerPayment $payment) => [
                'payment' => $payment,
                'receipt_url' => $this->receipts->signedPublicUrl($payment),
            ]);

        return view('client.payments.index', compact('customer', 'payments'));
    }
}
