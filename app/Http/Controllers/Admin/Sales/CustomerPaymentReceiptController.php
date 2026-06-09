<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use App\Models\Sales\CustomerPayment;
use App\Support\Sales\CustomerPaymentReceiptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerPaymentReceiptController extends Controller
{
    public function __construct(
        protected CustomerPaymentReceiptService $receipts,
    ) {}

    public function show(CustomerPayment $payment): View
    {
        $this->authorize('viewReceipt', $payment);

        $payment->load(['customer', 'allocations.invoice', 'branch', 'company']);
        $receipt = $this->receipts->build($payment);

        return view('admin.sales.payments.receipt', compact('payment', 'receipt'));
    }

    public function pdf(CustomerPayment $payment): StreamedResponse
    {
        $this->authorize('downloadReceiptPdf', $payment);

        return $this->receipts->downloadPdf($payment);
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
}
