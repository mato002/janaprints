<?php

namespace App\Http\Controllers\Admin\Procurement;

use App\Enums\DocumentType;
use App\Enums\GoodsReceiptStatus;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Procurement\Concerns\ResolvesProcurementTenant;
use App\Http\Controllers\Controller;
use App\Models\Inventory\Warehouse;
use App\Models\Procurement\GoodsReceipt;
use App\Models\Procurement\PurchaseOrder;
use App\Support\Procurement\GoodsReceiptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class GoodsReceiptController extends Controller
{
    use ResolvesProcurementTenant, ScopesToTenant;

    public function index(): View
    {
        $this->authorize('viewAny', GoodsReceipt::class);

        $receipts = $this->scopeToTenant(
            GoodsReceipt::query()->with(['purchaseOrder.vendor', 'receiver'])->latest('receipt_date')
        )->paginate(config('platform.pagination.default', 15));

        return view('admin.procurement.receipts.index', compact('receipts'));
    }

    public function create(PurchaseOrder $order): View
    {
        $this->authorize('receive', $order);

        $order->load('items.inventoryItem');

        return view('admin.procurement.receipts.create', [
            'order' => $order,
            'warehouses' => Warehouse::query()->forTenant()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, PurchaseOrder $order): RedirectResponse
    {
        $this->authorize('receive', $order);

        $validated = $request->validate([
            'warehouse_id' => ['required', Rule::exists('warehouses', 'id')->where('company_id', $order->company_id)->where('branch_id', $order->branch_id)],
            'receipt_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.purchase_order_item_id' => ['required', Rule::exists('purchase_order_items', 'id')],
            'items.*.quantity_received' => ['required', 'numeric', 'min:0.001'],
        ]);

        $goodsReceipt = GoodsReceipt::query()->create([
            'company_id' => $order->company_id,
            'branch_id' => $order->branch_id,
            'purchase_order_id' => $order->id,
            'warehouse_id' => $validated['warehouse_id'],
            'receipt_number' => $this->nextNumber(DocumentType::GoodsReceipt, $order->company_id, $order->branch_id),
            'receipt_date' => $validated['receipt_date'],
            'status' => GoodsReceiptStatus::Draft,
            'received_by' => auth()->id(),
            'notes' => $validated['notes'] ?? null,
        ]);

        $orderItems = $order->items()->get()->keyBy('id');

        foreach ($validated['items'] as $line) {
            $poItem = $orderItems->get((int) $line['purchase_order_item_id']);

            if (! $poItem) {
                continue;
            }

            $goodsReceipt->items()->create([
                'purchase_order_item_id' => $poItem->id,
                'inventory_item_id' => $poItem->inventory_item_id,
                'quantity_received' => $line['quantity_received'],
                'unit_cost' => $poItem->unit_cost,
            ]);
        }

        return redirect()->route('admin.procurement.receipts.show', $goodsReceipt)->with('status', __('Goods receipt created.'));
    }

    public function show(GoodsReceipt $receipt): View
    {
        $this->authorize('view', $receipt);

        $receipt->load(['items.purchaseOrderItem', 'purchaseOrder.vendor', 'warehouse', 'receiver', 'stockReceipt']);

        return view('admin.procurement.receipts.show', compact('receipt'));
    }

    public function post(GoodsReceipt $receipt): RedirectResponse
    {
        $this->authorize('post', $receipt);

        try {
            GoodsReceiptService::post($receipt, (int) auth()->id());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('status', __('Goods receipt posted to inventory.'));
    }
}
