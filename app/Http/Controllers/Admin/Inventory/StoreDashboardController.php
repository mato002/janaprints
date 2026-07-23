<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Warehouse;
use Illuminate\Http\RedirectResponse;

class StoreDashboardController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        $this->authorize('viewAny', Warehouse::class);

        return redirect()->route('admin.store.desk');
    }
}
