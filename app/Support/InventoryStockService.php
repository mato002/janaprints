<?php

namespace App\Support;

use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryMovement;
use App\Models\Inventory\InventoryReorderAlert;
use App\Support\Platform\PlatformCacheService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryStockService
{
    public static function balance(int $inventoryItemId, int $warehouseId): float
    {
        return (float) app(PlatformCacheService::class)->remember(
            'stock_balance',
            "{$inventoryItemId}:{$warehouseId}",
            fn () => (float) InventoryMovement::query()
                ->where('inventory_item_id', $inventoryItemId)
                ->where('warehouse_id', $warehouseId)
                ->sum('quantity'),
        );
    }

    public static function forgetBalanceCache(int $inventoryItemId, int $warehouseId): void
    {
        app(PlatformCacheService::class)->forget('stock_balance', "{$inventoryItemId}:{$warehouseId}");
    }

    public static function balanceUncached(int $inventoryItemId, int $warehouseId): float
    {
        return (float) InventoryMovement::query()
            ->where('inventory_item_id', $inventoryItemId)
            ->where('warehouse_id', $warehouseId)
            ->sum('quantity');
    }

    public static function branchBalance(int $inventoryItemId, int $companyId, int $branchId): float
    {
        return (float) InventoryMovement::query()
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('inventory_item_id', $inventoryItemId)
            ->sum('quantity');
    }

    /**
     * @return Collection<int, float>
     */
    public static function branchBalancesMap(int $companyId, int $branchId): Collection
    {
        return InventoryMovement::query()
            ->select('inventory_item_id', DB::raw('SUM(quantity) as balance'))
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->groupBy('inventory_item_id')
            ->pluck('balance', 'inventory_item_id')
            ->map(fn ($balance) => (float) $balance);
    }

    public static function assertSufficientStock(int $inventoryItemId, int $warehouseId, float $quantity): void
    {
        $available = self::balance($inventoryItemId, $warehouseId);

        if ($quantity > $available) {
            throw ValidationException::withMessages([
                'quantity' => __('Insufficient stock. Available: :qty', ['qty' => $available]),
            ]);
        }
    }

    public static function assertPositiveResult(float $currentBalance, float $delta): void
    {
        if ($currentBalance + $delta < 0) {
            throw ValidationException::withMessages([
                'quantity' => __('This movement would result in negative stock.'),
            ]);
        }
    }

    public static function syncReorderAlerts(InventoryItem $item): void
    {
        $balance = self::branchBalance($item->id, $item->company_id, $item->branch_id);

        if ($balance <= (float) $item->reorder_level) {
            InventoryReorderAlert::query()->updateOrCreate(
                [
                    'company_id' => $item->company_id,
                    'branch_id' => $item->branch_id,
                    'inventory_item_id' => $item->id,
                ],
                [
                    'current_quantity' => $balance,
                    'reorder_level' => $item->reorder_level,
                    'is_resolved' => false,
                    'alerted_at' => now(),
                ],
            );
        } else {
            InventoryReorderAlert::query()
                ->where('inventory_item_id', $item->id)
                ->where('company_id', $item->company_id)
                ->where('branch_id', $item->branch_id)
                ->update(['is_resolved' => true]);
        }
    }
}
