<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Enums\InventoryDocumentStatus;
use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryReorderAlert;
use App\Models\Inventory\StockIssue;
use App\Models\Inventory\StockReceipt;
use App\Support\InventoryStockService;
use Illuminate\View\View;

class InventoryDashboardController extends Controller
{
    public function __invoke(): View
    {
        $this->authorize('viewAny', InventoryItem::class);

        $items = InventoryItem::query()
            ->forTenant()
            ->where('is_active', true)
            ->get(['id', 'company_id', 'branch_id', 'reorder_level', 'standard_cost']);

        $companyId = tenant()->companyId() ?? $items->first()?->company_id;
        $branchId = tenant()->branchId() ?? $items->first()?->branch_id;

        $balances = ($companyId && $branchId)
            ? InventoryStockService::branchBalancesMap((int) $companyId, (int) $branchId)
            : collect();

        $lowStock = 0;
        $inventoryValue = 0.0;

        foreach ($items as $item) {
            $balance = (float) ($balances[$item->id] ?? 0);
            if ($balance <= (float) $item->reorder_level) {
                $lowStock++;
            }
            $inventoryValue += $balance * (float) $item->standard_cost;
        }

        $stats = [
            'low_stock' => $lowStock,
            'inventory_value' => round($inventoryValue, 2),
            'recent_receipts' => StockReceipt::query()->forTenant()
                ->where('status', InventoryDocumentStatus::Posted)
                ->latest('posted_at')->limit(5)->count(),
            'recent_issues' => StockIssue::query()->forTenant()
                ->where('status', InventoryDocumentStatus::Posted)
                ->latest('posted_at')->limit(5)->count(),
            'reorder_alerts' => InventoryReorderAlert::query()->forTenant()
                ->where('is_resolved', false)->count(),
        ];

        return view('admin.inventory.dashboard', compact('stats'));
    }
}
