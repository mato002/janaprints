<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryMovement;
use App\Support\Inventory\StoreDeskViews;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InventoryMovementController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', InventoryMovement::class);

        return redirect()->to(StoreDeskViews::deskUrl(StoreDeskViews::MOVEMENTS, $request->query()));
    }
}
