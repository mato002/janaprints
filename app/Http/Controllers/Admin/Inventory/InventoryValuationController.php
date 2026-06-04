<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Support\Inventory\InventoryValuationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryValuationController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()?->can('inventory.valuation.view'), 403);

        $companyId = (int) tenant()->companyId();
        $branchId = tenant()->branchId();

        $totals = InventoryValuationService::dashboardTotals($companyId, $branchId);
        $rows = InventoryValuationService::byItem($companyId, $branchId);

        return view('admin.inventory.valuation.index', compact('totals', 'rows'));
    }
}
