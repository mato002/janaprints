<?php

namespace App\Http\Controllers\Admin\Procurement;

use App\Enums\SupplierBillLineType;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Controller;
use App\Models\Procurement\GoodsReceipt;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\SupplierBill;
use App\Models\Procurement\Vendor;
use App\Support\Procurement\SupplierBillService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierBillController extends Controller
{
    use ScopesToTenant;

    public function __construct(
        protected SupplierBillService $bills,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', SupplierBill::class);

        $bills = $this->scopeToTenant(
            SupplierBill::query()->with(['vendor', 'purchaseOrder'])
        )->latest('bill_date')->latest('id')->paginate(20);

        return view('admin.payables.bills.index', compact('bills'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', SupplierBill::class);

        $vendors = Vendor::query()->forTenant()->orderBy('vendor_name')->get(['id', 'vendor_name']);

        return view('admin.payables.bills.create', compact('vendors'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', SupplierBill::class);

        $validated = $request->validate([
            'vendor_id' => ['required', 'exists:vendors,id'],
            'branch_id' => ['required', 'exists:branches,id'],
            'bill_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.item_name' => ['required', 'string'],
            'lines.*.line_type' => ['required', 'in:inventory,expense'],
            'lines.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'lines.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'lines.*.tax_rate' => ['nullable', 'numeric', 'min:0'],
        ]);

        $vendor = Vendor::query()->forTenant()->findOrFail($validated['vendor_id']);

        $bill = $this->bills->createBill([
            'company_id' => $vendor->company_id,
            'branch_id' => $validated['branch_id'],
            'vendor_id' => $vendor->id,
            'bill_date' => $validated['bill_date'],
            'due_date' => $validated['due_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'currency' => 'KES',
        ], $validated['lines'], (int) auth()->id());

        return redirect()->route('admin.payables.bills.show', $bill)->with('status', __('Supplier bill created as draft.'));
    }

    public function createFromPurchaseOrder(PurchaseOrder $order): View
    {
        $this->authorize('create', SupplierBill::class);
        $order->load(['vendor', 'items']);

        return view('admin.payables.bills.create-from-po', compact('order'));
    }

    public function storeFromPurchaseOrder(Request $request, PurchaseOrder $order): RedirectResponse
    {
        $this->authorize('create', SupplierBill::class);

        $validated = $request->validate([
            'bill_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'default_tax_rate' => ['nullable', 'numeric', 'min:0'],
        ]);

        $bill = $this->bills->createFromPurchaseOrder($order, (int) auth()->id(), $validated);

        return redirect()->route('admin.payables.bills.show', $bill)->with('status', __('Bill created from purchase order.'));
    }

    public function createFromGoodsReceipt(GoodsReceipt $receipt): View
    {
        $this->authorize('create', SupplierBill::class);
        $receipt->load(['purchaseOrder.vendor', 'items']);

        return view('admin.payables.bills.create-from-grn', compact('receipt'));
    }

    public function storeFromGoodsReceipt(Request $request, GoodsReceipt $receipt): RedirectResponse
    {
        $this->authorize('create', SupplierBill::class);

        $validated = $request->validate([
            'bill_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'default_tax_rate' => ['nullable', 'numeric', 'min:0'],
        ]);

        $bill = $this->bills->createFromGoodsReceipt($receipt, (int) auth()->id(), $validated);

        return redirect()->route('admin.payables.bills.show', $bill)->with('status', __('Bill created from goods receipt.'));
    }

    public function show(SupplierBill $bill): View
    {
        $this->authorize('view', $bill);

        $bill->load([
            'vendor',
            'purchaseOrder',
            'goodsReceipt',
            'lines',
            'taxLines',
            'creditedBill',
            'creditNotes',
            'postedJournal',
            'creator',
            'approver',
            'poster',
        ]);

        return view('admin.payables.bills.show', compact('bill'));
    }

    public function approve(SupplierBill $bill): RedirectResponse
    {
        $this->authorize('approve', $bill);
        $this->bills->approve($bill, (int) auth()->id());

        return back()->with('status', __('Bill approved.'));
    }

    public function post(SupplierBill $bill): RedirectResponse
    {
        $this->authorize('post', $bill);
        $this->bills->post($bill, (int) auth()->id());

        return back()->with('status', __('Bill posted to accounts payable.'));
    }

    public function cancel(Request $request, SupplierBill $bill): RedirectResponse
    {
        $this->authorize('cancel', $bill);
        $this->bills->cancel($bill, (int) auth()->id(), $request->input('reason'));

        return back()->with('status', __('Bill cancelled.'));
    }

    public function storeCreditNote(Request $request, SupplierBill $bill): RedirectResponse
    {
        $this->authorize('creditNote', $bill);

        $validated = $request->validate([
            'bill_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $credit = $this->bills->createCreditNote($bill, (int) auth()->id(), $validated);

        return redirect()->route('admin.payables.bills.show', $credit)->with('status', __('Credit note created.'));
    }
}
