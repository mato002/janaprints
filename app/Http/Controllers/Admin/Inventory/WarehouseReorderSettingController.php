<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Admin\Inventory\Concerns\ResolvesInventoryTenant;
use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryItemWarehouseReorderSetting;
use App\Models\Inventory\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WarehouseReorderSettingController extends Controller
{
    use ResolvesInventoryTenant;

    public function index(Request $request): View
    {
        $this->authorize('viewAny', InventoryItemWarehouseReorderSetting::class);
        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds();

        $settings = InventoryItemWarehouseReorderSetting::query()
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->with(['warehouse:id,name', 'inventoryItem:id,sku,item_name'])
            ->when($request->filled('warehouse_id'), fn ($q) => $q->where('warehouse_id', (int) $request->input('warehouse_id')))
            ->orderBy('warehouse_id')
            ->orderBy('inventory_item_id')
            ->paginate(config('platform.pagination.default', 15));

        return view('admin.inventory.reorder-settings.index', [
            'settings' => $settings,
            'warehouses' => Warehouse::query()->forTenant()->orderBy('name')->get(['id', 'name']),
            'filters' => $request->only(['warehouse_id']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', InventoryItemWarehouseReorderSetting::class);
        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds();

        $validated = $request->validate([
            'warehouse_id' => ['required', Rule::exists('warehouses', 'id')->where('company_id', $companyId)->where('branch_id', $branchId)],
            'inventory_item_id' => ['required', Rule::exists('inventory_items', 'id')->where('company_id', $companyId)->where('branch_id', $branchId)],
            'min_level' => ['required', 'numeric', 'min:0'],
            'max_level' => ['nullable', 'numeric', 'min:0'],
            'reorder_quantity' => ['required', 'numeric', 'min:0'],
            'safety_stock' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $setting = InventoryItemWarehouseReorderSetting::query()->updateOrCreate(
            [
                'warehouse_id' => (int) $validated['warehouse_id'],
                'inventory_item_id' => (int) $validated['inventory_item_id'],
            ],
            [
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'min_level' => $validated['min_level'],
                'max_level' => $validated['max_level'] ?? null,
                'reorder_quantity' => $validated['reorder_quantity'],
                'safety_stock' => $validated['safety_stock'],
                'is_active' => (bool) ($validated['is_active'] ?? true),
            ],
        );

        $item = InventoryItem::query()->findOrFail($setting->inventory_item_id);
        \App\Support\InventoryStockService::syncReorderAlerts($item, $setting->warehouse_id);

        return back()->with('status', __('Warehouse reorder settings saved.'));
    }
}
