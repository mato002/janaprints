<?php

namespace App\Http\Controllers;

use App\Models\Sales\CustomerPayment;
use App\Support\Sales\CustomerPaymentReceiptService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerPaymentReceiptPublicController extends Controller
{
    public function __construct(
        protected CustomerPaymentReceiptService $receipts,
    ) {}

    public function show(Request $request, CustomerPayment $payment): View
    {
        if (! $request->hasValidSignature()) {
            abort(403);
        }

        $payment->load(['customer', 'allocations.invoice', 'branch', 'company']);
        $receipt = $this->receipts->build($payment);

        return view('public.payment-receipt', compact('payment', 'receipt'));
    }
}
