<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryMovement;
use App\Models\Inventory\Warehouse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryMovementController extends Controller
{
    use ScopesToTenant;

    public function index(Request $request): View
    {
        $this->authorize('viewAny', InventoryMovement::class);

        $warehouseId = $request->integer('warehouse_id') ?: null;

        $query = $this->scopeToTenant(
            InventoryMovement::query()->with(['item', 'warehouse', 'creator'])->latest('created_at')
        );

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        $movements = $query->paginate(20)->withQueryString();
        $warehouse = $warehouseId
            ? Warehouse::query()->find($warehouseId)
            : null;

        return view('admin.inventory.movements.index', compact('movements', 'warehouse'));
    }
}
