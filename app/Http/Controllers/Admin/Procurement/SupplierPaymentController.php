<?php

namespace App\Http\Controllers\Admin\Procurement;

use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Controller;
use App\Models\Procurement\SupplierBill;
use App\Models\Procurement\SupplierPayment;
use App\Models\Procurement\Vendor;
use App\Support\Procurement\SupplierPaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierPaymentController extends Controller
{
    use ScopesToTenant;

    public function __construct(
        protected SupplierPaymentService $payments,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', SupplierPayment::class);

        $payments = $this->scopeToTenant(
            SupplierPayment::query()->with('vendor')
        )->latest('payment_date')->paginate(20);

        return view('admin.payables.payments.index', compact('payments'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', SupplierPayment::class);

        $vendor = null;
        $openBills = [];

        if ($request->filled('vendor_id')) {
            $vendor = Vendor::query()->forTenant()->findOrFail($request->integer('vendor_id'));
            $openBills = $this->payments->openBillsForVendor($vendor->id);
        }

        if ($request->filled('bill_id')) {
            $bill = SupplierBill::query()->forTenant()->findOrFail($request->integer('bill_id'));
            $vendor = $bill->vendor;
            $openBills = $this->payments->openBillsForVendor($vendor->id);
        }

        $vendors = Vendor::query()->forTenant()->orderBy('vendor_name')->get(['id', 'vendor_name']);

        return view('admin.payables.payments.create', compact('vendor', 'vendors', 'openBills'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', SupplierPayment::class);

        $validated = $request->validate([
            'vendor_id' => ['required', 'exists:vendors,id'],
            'branch_id' => ['required', 'exists:branches,id'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', 'in:cash,bank'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reference' => ['nullable', 'string', 'max:100'],
            'bank_reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'allocations' => ['nullable', 'array'],
            'allocations.*.supplier_bill_id' => ['required_with:allocations', 'integer'],
            'allocations.*.amount' => ['required_with:allocations', 'numeric', 'min:0.01'],
        ]);

        $vendor = Vendor::query()->forTenant()->findOrFail($validated['vendor_id']);

        $payment = $this->payments->create($vendor, (int) auth()->id(), $validated);

        return redirect()->route('admin.payables.payments.show', $payment)->with('status', __('Payment saved as draft.'));
    }

    public function show(SupplierPayment $payment): View
    {
        $this->authorize('view', $payment);

        $payment->load(['vendor', 'allocations.bill', 'postedJournal', 'creator', 'poster']);

        return view('admin.payables.payments.show', compact('payment'));
    }

    public function post(SupplierPayment $payment): RedirectResponse
    {
        $this->authorize('post', $payment);
        $this->payments->post($payment, (int) auth()->id());

        return back()->with('status', __('Payment posted.'));
    }

    public function cancel(Request $request, SupplierPayment $payment): RedirectResponse
    {
        $this->authorize('cancel', $payment);
        $this->payments->cancel($payment, (int) auth()->id(), $request->input('reason'));

        return back()->with('status', __('Payment cancelled.'));
    }
}
