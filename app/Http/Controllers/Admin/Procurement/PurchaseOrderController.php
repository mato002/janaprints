<?php

namespace App\Http\Controllers\Admin\Procurement;

use App\Enums\DocumentType;
use App\Enums\ProcurementItemClassification;
use App\Enums\PurchaseOrderStatus;
use App\Models\Assets\AssetCategory;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Procurement\Concerns\ResolvesProcurementTenant;
use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryItem;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\Vendor;
use App\Support\Procurement\PurchaseOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    use ResolvesProcurementTenant, ScopesToTenant;

    public function index(): View
    {
        $this->authorize('viewAny', PurchaseOrder::class);

        $orders = $this->scopeToTenant(
            PurchaseOrder::query()->with(['vendor'])->latest('order_date')
        )->paginate(config('platform.pagination.default', 15));

        return view('admin.procurement.orders.index', compact('orders'));
    }

    public function create(): View
    {
        $this->authorize('create', PurchaseOrder::class);

        return view('admin.procurement.orders.create', $this->formMeta());
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', PurchaseOrder::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds($request);
        $header = $this->validateHeader($request, $companyId, $branchId);
        $lines = $this->validateLines($request, $companyId, $branchId);
        $totals = $this->totalsFromLines($lines, $header);

        $order = PurchaseOrder::query()->create([
            ...$header,
            ...$totals,
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'po_number' => $this->nextNumber(DocumentType::PurchaseOrder, $companyId, $branchId),
            'status' => PurchaseOrderStatus::Draft,
            'prepared_by' => auth()->id(),
        ]);

        foreach ($lines as $line) {
            $order->items()->create($line);
        }

        return redirect()->route('admin.procurement.orders.show', $order)->with('status', __('Purchase order created.'));
    }

    public function show(PurchaseOrder $order): View
    {
        $this->authorize('view', $order);

        $order->load(['items.inventoryItem', 'vendor', 'preparer', 'approver', 'purchaseRequest', 'goodsReceipts']);

        return view('admin.procurement.orders.show', compact('order'));
    }

    public function edit(PurchaseOrder $order): View
    {
        $this->authorize('update', $order);

        $order->load('items');

        return view('admin.procurement.orders.edit', array_merge(
            ['order' => $order],
            $this->formMeta(),
        ));
    }

    public function update(Request $request, PurchaseOrder $order): RedirectResponse
    {
        $this->authorize('update', $order);

        $header = $this->validateHeader($request, $order->company_id, $order->branch_id);
        $lines = $this->validateLines($request, $order->company_id, $order->branch_id);
        $totals = $this->totalsFromLines($lines, $header);

        $order->update([...$header, ...$totals]);
        $order->items()->delete();

        foreach ($lines as $line) {
            $order->items()->create($line);
        }

        return redirect()->route('admin.procurement.orders.show', $order)->with('status', __('Purchase order updated.'));
    }

    public function destroy(PurchaseOrder $order): RedirectResponse
    {
        $this->authorize('delete', $order);

        $order->delete();

        return redirect()->route('admin.procurement.orders.index')->with('status', __('Purchase order deleted.'));
    }

    public function submit(PurchaseOrder $order): RedirectResponse
    {
        $this->authorize('submit', $order);

        try {
            PurchaseOrderService::submit($order, (int) auth()->id());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('status', __('Purchase order submitted for approval.'));
    }

    public function approve(Request $httpRequest, PurchaseOrder $order): RedirectResponse
    {
        $this->authorize('approve', $order);

        try {
            PurchaseOrderService::approve($order, $httpRequest->user(), $httpRequest->input('notes'));
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('status', __('Purchase order approved.'));
    }

    public function reject(Request $httpRequest, PurchaseOrder $order): RedirectResponse
    {
        $this->authorize('reject', $order);

        $validated = $httpRequest->validate([
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        try {
            PurchaseOrderService::reject($order, $httpRequest->user(), $validated['reason']);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('status', __('Purchase order rejected.'));
    }

    public function send(PurchaseOrder $order): RedirectResponse
    {
        $this->authorize('send', $order);

        try {
            PurchaseOrderService::assertCanSend($order);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        $order->update(['status' => PurchaseOrderStatus::Sent]);

        return back()->with('status', __('Purchase order marked as sent.'));
    }

    public function cancel(PurchaseOrder $order): RedirectResponse
    {
        $this->authorize('cancel', $order);

        $order->update(['status' => PurchaseOrderStatus::Cancelled]);

        return back()->with('status', __('Purchase order cancelled.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateHeader(Request $request, int $companyId, int $branchId): array
    {
        return $request->validate([
            'vendor_id' => ['required', Rule::exists('vendors', 'id')->where('company_id', $companyId)],
            'order_date' => ['required', 'date'],
            'expected_delivery_date' => ['nullable', 'date'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function validateLines(Request $request, int $companyId, int $branchId): array
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.inventory_item_id' => ['nullable', Rule::exists('inventory_items', 'id')->where('company_id', $companyId)->where('branch_id', $branchId)],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'items.*.item_classification' => ['nullable', 'string', Rule::in(array_column(ProcurementItemClassification::cases(), 'value'))],
            'items.*.asset_category_id' => ['nullable', Rule::exists('asset_categories', 'id')->where('company_id', $companyId)],
        ]);

        return collect($validated['items'])->map(function (array $line) {
            $line['item_classification'] = $line['item_classification'] ?? ProcurementItemClassification::InventoryItem->value;
            $line['line_total'] = round((float) $line['quantity'] * (float) $line['unit_cost'], 2);

            return $line;
        })->all();
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     * @param  array<string, mixed>  $header
     * @return array<string, float>
     */
    protected function totalsFromLines(array $lines, array $header): array
    {
        $subtotal = collect($lines)->sum(fn (array $line) => (float) $line['line_total']);
        $tax = (float) ($header['tax_amount'] ?? 0);
        $discount = (float) ($header['discount_amount'] ?? 0);

        return [
            'subtotal' => round($subtotal, 2),
            'tax_amount' => round($tax, 2),
            'discount_amount' => round($discount, 2),
            'total_amount' => round($subtotal + $tax - $discount, 2),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function formMeta(): array
    {
        return [
            'vendors' => Vendor::query()->forTenant()->where('status', 'active')->orderBy('vendor_name')->get(),
            'items' => InventoryItem::query()->forTenant()->where('is_active', true)->orderBy('item_name')->get(),
            'classifications' => ProcurementItemClassification::cases(),
            'assetCategories' => AssetCategory::query()->forTenant()->where('is_active', true)->orderBy('name')->get(),
        ];
    }
}
