<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Controller;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\CustomerPayment;
use App\Support\Sales\CustomerCreditWalletService;
use App\Support\Sales\CustomerDepositService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerDepositController extends Controller
{
    use ScopesToTenant;

    public function __construct(
        protected CustomerDepositService $deposits,
        protected CustomerCreditWalletService $wallet,
    ) {}

    public function applyForm(CustomerInvoice $invoice): View
    {
        $this->authorize('applyDeposit', CustomerPayment::class);
        $this->scopeToTenant(CustomerInvoice::query()->whereKey($invoice->id))->firstOrFail();

        $invoice->load('customer');
        $canCrossBranch = auth()->user()->can('finance.cross_branch.allocate');
        $deposits = $this->wallet->depositsWithCredit(
            $invoice->customer_id,
            ignoreBranchFilter: $canCrossBranch,
        );

        return view('admin.sales.deposits.apply', compact('invoice', 'deposits', 'canCrossBranch'));
    }

    public function apply(Request $request, CustomerInvoice $invoice): RedirectResponse
    {
        $this->authorize('applyDeposit', CustomerPayment::class);
        $this->scopeToTenant(CustomerInvoice::query()->whereKey($invoice->id))->firstOrFail();

        $validated = $request->validate([
            'customer_payment_id' => ['required', 'integer', 'exists:customer_payments,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'application_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'override_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $deposit = $this->scopeToTenant(
            CustomerPayment::query()->whereKey($validated['customer_payment_id'])
        )->firstOrFail();

        $application = $this->deposits->applyToInvoice(
            $deposit,
            $invoice,
            (float) $validated['amount'],
            (int) auth()->id(),
            [
                'application_date' => $validated['application_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'override_reason' => $validated['override_reason'] ?? null,
            ],
        );

        return redirect()
            ->route('admin.invoices.show', $invoice)
            ->with('status', __('Deposit :ref applied to invoice.', ['ref' => $application->application_number]));
    }

    public function refundForm(CustomerPayment $payment): View
    {
        $this->authorize('refundDeposit', $payment);
        $payment->load('customer');

        return view('admin.sales.deposits.refund', compact('payment'));
    }

    public function refund(Request $request, CustomerPayment $payment): RedirectResponse
    {
        $this->authorize('refundDeposit', $payment);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'refund_date' => ['nullable', 'date'],
            'payment_method' => ['nullable', 'in:cash,bank,mpesa'],
            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $refund = $this->deposits->refund(
            $payment,
            (float) $validated['amount'],
            (int) auth()->id(),
            $validated,
        );

        return redirect()
            ->route('admin.payments.show', $payment)
            ->with('status', __('Deposit refund :ref posted.', ['ref' => $refund->refund_number]));
    }
}
