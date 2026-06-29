<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Enums\CustomerPaymentMethod;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Crm\Concerns\ResolvesCrmTenant;
use App\Http\Controllers\Controller;
use App\Models\Crm\Customer;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\CustomerPayment;
use App\Support\Sales\CustomerPaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerPaymentController extends Controller
{
    use ResolvesCrmTenant, ScopesToTenant;

    public function __construct(
        protected CustomerPaymentService $payments,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', CustomerPayment::class);

        $payments = $this->scopeToTenant(
            CustomerPayment::query()->with(['customer'])
        )
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.sales.payments.index', compact('payments'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', CustomerPayment::class);

        $customer = null;
        $openInvoices = [];
        $sourceInvoice = null;

        if ($request->filled('customer_id')) {
            $customer = Customer::query()->forTenant()->findOrFail($request->integer('customer_id'));
            $openInvoices = $this->payments->openInvoicesForCustomer($customer->id);
        }

        if ($request->filled('invoice_id')) {
            $sourceInvoice = CustomerInvoice::query()->forTenant()->findOrFail($request->integer('invoice_id'));
            $customer = $sourceInvoice->customer;
            $openInvoices = $this->payments->openInvoicesForCustomer($customer->id);
        }

        $customers = Customer::query()->forTenant()->orderBy('company_name')->get(['id', 'company_name']);

        return view('admin.sales.payments.create', compact('customer', 'customers', 'openInvoices', 'sourceInvoice'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', CustomerPayment::class);

        $request->merge([
            'allocations' => collect($request->input('allocations', []))
                ->filter(fn (array $row) => (float) ($row['amount'] ?? 0) > 0)
                ->values()
                ->all(),
        ]);

        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', 'in:cash,bank,mpesa'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'is_deposit' => ['nullable', 'boolean'],
            'post_now' => ['nullable', 'boolean'],
            'reference' => ['nullable', 'string', 'max:100'],
            'bank_reference' => ['nullable', 'string', 'max:100'],
            'mpesa_reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'allocations' => ['nullable', 'array'],
            'allocations.*.customer_invoice_id' => ['required_with:allocations', 'integer'],
            'allocations.*.amount' => ['required_with:allocations', 'numeric', 'min:0.01'],
        ]);

        $customer = Customer::query()->forTenant()->findOrFail($validated['customer_id']);

        $payment = $this->payments->create($customer, (int) auth()->id(), $validated);

        if ($request->boolean('post_now')) {
            $this->authorize('post', $payment);
            $payment = $this->payments->post($payment, (int) auth()->id());

            return redirect()
                ->route('admin.payments.receipt', $payment)
                ->with('status', __('Payment recorded and receipt generated.'));
        }

        return redirect()
            ->route('admin.payments.show', $payment)
            ->with('status', __('Payment saved as draft. Post it to update invoice balances and generate a receipt.'));
    }

    public function show(CustomerPayment $payment): View
    {
        $this->authorize('view', $payment);

        $payment->load(['customer', 'allocations.invoice', 'postedJournal', 'creator', 'poster']);

        return view('admin.sales.payments.show', compact('payment'));
    }

    public function edit(CustomerPayment $payment): View
    {
        $this->authorize('update', $payment);

        $payment->load(['allocations.invoice', 'customer']);
        $openInvoices = $this->payments->openInvoicesForCustomer($payment->customer_id);

        return view('admin.sales.payments.edit', compact('payment', 'openInvoices'));
    }

    public function update(Request $request, CustomerPayment $payment): RedirectResponse
    {
        $this->authorize('update', $payment);

        $validated = $request->validate([
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', 'in:cash,bank,mpesa'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'is_deposit' => ['nullable', 'boolean'],
            'reference' => ['nullable', 'string', 'max:100'],
            'bank_reference' => ['nullable', 'string', 'max:100'],
            'mpesa_reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'allocations' => ['nullable', 'array'],
            'allocations.*.customer_invoice_id' => ['required_with:allocations', 'integer'],
            'allocations.*.amount' => ['required_with:allocations', 'numeric', 'min:0.01'],
        ]);

        $this->payments->updateDraft($payment, $validated);

        return redirect()
            ->route('admin.payments.show', $payment)
            ->with('status', __('Payment updated.'));
    }

    public function destroy(CustomerPayment $payment): RedirectResponse
    {
        $this->authorize('delete', $payment);

        $this->payments->deleteDraft($payment);

        return redirect()
            ->route('admin.payments.index')
            ->with('status', __('Payment deleted.'));
    }

    public function post(CustomerPayment $payment): RedirectResponse
    {
        $this->authorize('post', $payment);

        $this->payments->post($payment, (int) auth()->id());

        return back()->with('status', __('Payment posted to the general ledger.'));
    }

    public function cancel(Request $request, CustomerPayment $payment): RedirectResponse
    {
        $this->authorize('cancel', $payment);

        $this->payments->cancel($payment, (int) auth()->id(), $request->input('cancel_reason'));

        return back()->with('status', __('Payment cancelled.'));
    }
}
