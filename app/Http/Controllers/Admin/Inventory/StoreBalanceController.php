<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryCategory;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryMovement;
use App\Models\Inventory\Warehouse;
use Illuminate\View\View;

class StoreBalanceController extends Controller
{
    public function __invoke(): View
    {
        $this->authorize('viewAny', Warehouse::class);

        $warehouses = Warehouse::query()->forTenant()->orderBy('name')->get();
        $items = InventoryItem::query()->forTenant()->with('category')->where('is_active', true)->orderBy('item_name')->get();
        $categories = InventoryCategory::query()->forTenant()->orderBy('name')->get();

        $movementMap = InventoryMovement::query()
            ->forTenant()
            ->selectRaw('warehouse_id, inventory_item_id, SUM(quantity) as balance')
            ->groupBy('warehouse_id', 'inventory_item_id')
            ->get()
            ->keyBy(fn ($movement) => "{$movement->warehouse_id}:{$movement->inventory_item_id}");

        $balances = $warehouses->flatMap(fn (Warehouse $warehouse) => $items->map(function (InventoryItem $item) use ($warehouse, $movementMap) {
            $movement = $movementMap->get("{$warehouse->id}:{$item->id}");

            return (object) [
                'item_id' => $item->id,
                'warehouse_id' => $warehouse->id,
                'warehouse_code' => $warehouse->code,
                'warehouse_name' => $warehouse->name,
                'sku' => $item->sku,
                'item_name' => $item->item_name,
                'reorder_level' => $item->reorder_level,
                'standard_cost' => $item->standard_cost,
                'category_name' => $item->category?->name,
                'balance' => (float) ($movement?->balance ?? 0),
            ];
        }));

        return view('admin.inventory.store.balances', compact('balances', 'warehouses', 'categories'));
    }
}
