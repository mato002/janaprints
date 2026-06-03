<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryMovement;
use Illuminate\View\View;

class InventoryMovementController extends Controller
{
    use ScopesToTenant;

    public function index(): View
    {
        $this->authorize('viewAny', InventoryMovement::class);

        $movements = $this->scopeToTenant(
            InventoryMovement::query()->with(['item', 'warehouse', 'creator'])->latest('created_at')
        )->paginate(20);

        return view('admin.inventory.movements.index', compact('movements'));
    }
}
