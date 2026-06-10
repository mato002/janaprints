<?php

namespace App\Support;

use App\Enums\InventoryStockRole;
use App\Enums\VirtualWarehouseRole;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryMovement;
use App\Models\Inventory\InventoryReorderAlert;
use App\Models\Inventory\Warehouse;
use App\Services\Inventory\VirtualWarehouseResolverService;
use App\Support\Platform\PlatformCacheService;
use App\Support\Platform\SystemSettingsService;
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

    public static function allowsNegativeStock(?int $companyId = null, ?int $branchId = null): bool
    {
        return (bool) app(SystemSettingsService::class)->get(
            'inventory_allow_negative_stock',
            false,
            $companyId,
            $branchId,
        );
    }

    public static function assertSufficientStock(
        int $inventoryItemId,
        int $warehouseId,
        float $quantity,
        ?int $companyId = null,
        ?int $branchId = null,
    ): void {
        if (self::allowsNegativeStock($companyId, $branchId)) {
            return;
        }

        $available = self::balance($inventoryItemId, $warehouseId);

        if ($quantity > $available) {
            throw ValidationException::withMessages([
                'quantity' => __('Insufficient stock. Available: :qty', ['qty' => $available]),
            ]);
        }
    }

    public static function assertPositiveResult(
        float $currentBalance,
        float $delta,
        ?int $companyId = null,
        ?int $branchId = null,
    ): void {
        if (self::allowsNegativeStock($companyId, $branchId)) {
            return;
        }

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
                    'warehouse_id' => null,
                    'alert_type' => config('inventory_intelligence.reorder_alert_type', 'reorder_level'),
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

    public static function getBalanceByVirtualRole(
        int $inventoryItemId,
        int $companyId,
        VirtualWarehouseRole|string $virtualRole,
    ): float {
        $warehouse = app(VirtualWarehouseResolverService::class)->resolveByRole($companyId, $virtualRole);

        if ($warehouse === null) {
            return 0.0;
        }

        return self::balance($inventoryItemId, $warehouse->id);
    }

    /**
     * @return Collection<int, array{item: InventoryItem, balance: float}>
     */
    public static function getCompanyStockByRole(int $companyId, InventoryStockRole|string $stockRole): Collection
    {
        $roleValue = $stockRole instanceof InventoryStockRole ? $stockRole->value : $stockRole;

        return InventoryItem::query()
            ->where('company_id', $companyId)
            ->where('stock_role', $roleValue)
            ->where('is_active', true)
            ->get()
            ->map(function (InventoryItem $item) {
                $balance = self::branchBalance($item->id, $item->company_id, $item->branch_id);

                return [
                    'item' => $item,
                    'balance' => $balance,
                ];
            })
            ->filter(fn (array $row) => abs($row['balance']) >= 0.001)
            ->values();
    }

    /**
     * @return list<array{
     *     role: VirtualWarehouseRole,
     *     warehouse: Warehouse|null,
     *     item_count: int,
     *     total_value: float,
     *     last_movement_at: \Illuminate\Support\Carbon|null,
     *     empty_message: string|null
     * }>
     */
    public static function getVirtualWarehouseBalances(int $companyId): array
    {
        $resolver = app(VirtualWarehouseResolverService::class);
        $resolver->ensureDefaults($companyId);

        $rows = [];

        foreach (VirtualWarehouseRole::seededRoles() as $role) {
            $warehouse = $resolver->resolveByRole($companyId, $role);

            if ($warehouse === null) {
                $rows[] = [
                    'role' => $role,
                    'warehouse' => null,
                    'item_count' => 0,
                    'total_value' => 0.0,
                    'last_movement_at' => null,
                    'empty_message' => $role->emptyStateMessage(),
                ];

                continue;
            }

            $stats = self::virtualWarehouseStats($warehouse);

            $rows[] = [
                'role' => $role,
                'warehouse' => $warehouse,
                'item_count' => $stats['item_count'],
                'total_value' => $stats['total_value'],
                'last_movement_at' => $stats['last_movement_at'],
                'empty_message' => $stats['item_count'] === 0 ? $role->emptyStateMessage() : null,
            ];
        }

        return $rows;
    }

    /**
     * @return array{item_count: int, total_value: float, last_movement_at: \Illuminate\Support\Carbon|null}
     */
    protected static function virtualWarehouseStats(Warehouse $warehouse): array
    {
        $aggregates = InventoryMovement::query()
            ->where('warehouse_id', $warehouse->id)
            ->select([
                'inventory_item_id',
                DB::raw('SUM(quantity) as balance'),
                DB::raw('SUM(quantity * unit_cost) as ledger_value'),
            ])
            ->groupBy('inventory_item_id')
            ->havingRaw('ABS(SUM(quantity)) >= 0.001')
            ->get();

        $lastMovementAt = InventoryMovement::query()
            ->where('warehouse_id', $warehouse->id)
            ->latest('movement_date')
            ->latest('id')
            ->value('movement_date');

        return [
            'item_count' => $aggregates->count(),
            'total_value' => round((float) $aggregates->sum('ledger_value'), 2),
            'last_movement_at' => $lastMovementAt,
        ];
    }
}
