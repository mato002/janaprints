<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Warehouse;
use App\Support\Inventory\StoreDeskViews;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StoreBalanceController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', Warehouse::class);

        return redirect()->to(StoreDeskViews::deskUrl(StoreDeskViews::BALANCES, $request->query()));
    }
}
