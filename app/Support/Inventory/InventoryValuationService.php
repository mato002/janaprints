<?php

namespace App\Support\Inventory;

use App\Models\Inventory\InventoryCostLayer;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryValuation;
use App\Models\Inventory\InventoryValuationSnapshot;
use App\Models\Inventory\Warehouse;
use App\Support\InventoryStockService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InventoryValuationService
{
    /**
     * @return array{fifo_value: float, average_cost_value: float, quantity: float}
     */
    public static function itemWarehouseValue(int $itemId, int $warehouseId): array
    {
        $qty = InventoryStockService::balanceUncached($itemId, $warehouseId);
        $fifo = InventoryCostingService::fifoValue($itemId, $warehouseId);

        $valuation = InventoryValuation::query()
            ->where('inventory_item_id', $itemId)
            ->where('warehouse_id', $warehouseId)
            ->first();

        $avgUnit = (float) ($valuation?->average_unit_cost ?? 0);
        if ($avgUnit <= 0) {
            $item = InventoryItem::query()->find($itemId);
            $avgUnit = (float) ($item?->standard_cost ?? 0);
        }

        return [
            'quantity' => $qty,
            'fifo_value' => round($fifo, 2),
            'average_cost_value' => round($qty * $avgUnit, 2),
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public static function byItem(int $companyId, ?int $branchId = null): Collection
    {
        $query = InventoryItem::query()->where('company_id', $companyId);
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query->get()->map(function (InventoryItem $item) use ($branchId) {
            $warehouses = Warehouse::query()
                ->where('company_id', $item->company_id)
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->where('is_active', true)
                ->get();

            $fifo = 0.0;
            $avg = 0.0;
            $qty = 0.0;

            foreach ($warehouses as $warehouse) {
                $values = self::itemWarehouseValue($item->id, $warehouse->id);
                $fifo += $values['fifo_value'];
                $avg += $values['average_cost_value'];
                $qty += $values['quantity'];
            }

            return [
                'item' => $item,
                'quantity' => $qty,
                'fifo_value' => $fifo,
                'average_cost_value' => $avg,
            ];
        });
    }

    public static function snapshot(
        int $companyId,
        ?int $branchId,
        string $valuationDate,
        string $scope = 'branch',
    ): void {
        DB::transaction(function () use ($companyId, $branchId, $valuationDate, $scope) {
            $rows = self::byItem($companyId, $branchId);

            foreach ($rows as $row) {
                InventoryValuationSnapshot::query()->create([
                    'company_id' => $companyId,
                    'branch_id' => $branchId,
                    'valuation_date' => $valuationDate,
                    'snapshot_scope' => $scope,
                    'inventory_item_id' => $row['item']->id,
                    'quantity' => $row['quantity'],
                    'fifo_value' => $row['fifo_value'],
                    'average_cost_value' => $row['average_cost_value'],
                ]);
            }
        });
    }

    /**
     * @return array<string, float|int>
     */
    public static function dashboardTotals(int $companyId, ?int $branchId = null): array
    {
        $rows = self::byItem($companyId, $branchId);

        $fifoTotal = $rows->sum('fifo_value');
        $avgTotal = $rows->sum('average_cost_value');

        $topItems = $rows->sortByDesc('fifo_value')->take(5)->values();

        $deadStock = $rows->filter(fn ($r) => $r['quantity'] <= 0 && $r['fifo_value'] > 0);

        return [
            'fifo_total' => round($fifoTotal, 2),
            'average_total' => round($avgTotal, 2),
            'top_items' => $topItems,
            'dead_stock_value' => round($deadStock->sum('fifo_value'), 2),
            'item_count' => $rows->count(),
        ];
    }

    public static function layersRemainingValue(int $companyId, ?int $branchId = null): float
    {
        $query = InventoryCostLayer::query()
            ->where('company_id', $companyId)
            ->where('quantity_remaining', '>', 0);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return (float) $query
            ->selectRaw('SUM(quantity_remaining * unit_cost) as total')
            ->value('total');
    }
}
