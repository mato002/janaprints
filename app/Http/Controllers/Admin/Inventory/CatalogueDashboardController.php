<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryItem;
use Illuminate\Http\RedirectResponse;

class CatalogueDashboardController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        $this->authorize('viewAny', InventoryItem::class);

        return redirect()->route('admin.inventory.items.index');
    }
}
