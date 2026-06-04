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

        $scope = $request->query('scope', 'item');
        $valuationDate = $request->query('date', now()->toDateString());

        $totals = InventoryValuationService::dashboardTotals($companyId, $branchId);
        $reconciliation = InventoryValuationService::inventoryGlReconciliation($companyId, $totals['fifo_total']);

        $rows = match ($scope) {
            'warehouse' => InventoryValuationService::byWarehouse($companyId, $branchId),
            'category' => InventoryValuationService::byCategory($companyId, $branchId),
            'branch' => InventoryValuationService::byBranch($companyId),
            default => InventoryValuationService::byItem($companyId, $branchId),
        };

        return view('admin.inventory.valuation.index', compact(
            'totals',
            'rows',
            'scope',
            'valuationDate',
            'reconciliation',
        ));
    }

    public function snapshot(Request $request): \Illuminate\Http\RedirectResponse
    {
        abort_unless(auth()->user()?->can('inventory.valuation.view'), 403);

        $validated = $request->validate([
            'valuation_date' => ['required', 'date'],
            'scope' => ['required', 'string', 'in:branch,item'],
        ]);

        InventoryValuationService::snapshot(
            (int) tenant()->companyId(),
            tenant()->branchId(),
            $validated['valuation_date'],
            $validated['scope'],
        );

        return back()->with('status', __('Valuation snapshot saved for :date.', [
            'date' => $validated['valuation_date'],
        ]));
    }
}
