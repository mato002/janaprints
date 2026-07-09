<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Enums\InventoryDocumentStatus;
use App\Enums\StockIssueDestination;
use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryMovement;
use App\Models\Inventory\InventoryReorderAlert;
use App\Models\Inventory\StockIssue;
use App\Models\Inventory\Warehouse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StoreDashboardController extends Controller
{
    public function __invoke(): View
    {
        $this->authorize('viewAny', Warehouse::class);

        $warehouses = Warehouse::query()
            ->forTenant()
            ->withCount('managers')
            ->orderBy('name')
            ->get();

        $balances = InventoryMovement::query()
            ->forTenant()
            ->select('warehouse_id', DB::raw('SUM(quantity) as balance'), DB::raw('SUM(quantity * unit_cost) as stock_value'))
            ->groupBy('warehouse_id')
            ->get()
            ->keyBy('warehouse_id');

        $stats = [
            'stores' => $warehouses->count(),
            'active_stores' => $warehouses->where('is_active', true)->count(),
            'store_managers' => $warehouses->sum('managers_count'),
            'pending_transfers' => StockIssue::query()
                ->forTenant()
                ->where('destination', StockIssueDestination::Transfer)
                ->where('status', InventoryDocumentStatus::Draft)
                ->count(),
            'reorder_alerts' => InventoryReorderAlert::query()->forTenant()->where('is_resolved', false)->count(),
        ];

        $lowStockItems = InventoryItem::query()
            ->forTenant()
            ->where('is_active', true)
            ->orderBy('item_name')
            ->limit(200)
            ->get()
            ->filter(fn (InventoryItem $item) => (float) InventoryMovement::query()
                ->where('company_id', $item->company_id)
                ->where('branch_id', $item->branch_id)
                ->where('inventory_item_id', $item->id)
                ->sum('quantity') <= (float) $item->reorder_level)
            ->take(8);

        return view('admin.inventory.store.dashboard', compact('warehouses', 'balances', 'stats', 'lowStockItems'));
    }
}
