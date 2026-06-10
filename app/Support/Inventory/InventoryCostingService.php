<?php

namespace App\Support\Inventory;

use App\Enums\InventoryCostingMethod;
use App\Enums\InventoryMovementType;
use App\Models\Company;
use App\Models\Inventory\InventoryCostLayer;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryMovement;
use App\Models\Inventory\InventoryValuation;
use Illuminate\Support\Facades\DB;

class InventoryCostingService
{
    public static function processReceipt(InventoryMovement $movement): void
    {
        if (! self::isInbound($movement)) {
            return;
        }

        $qty = abs((float) $movement->quantity);
        $unitCost = (float) $movement->unit_cost;

        InventoryCostLayer::query()->create([
            'company_id' => $movement->company_id,
            'branch_id' => $movement->branch_id,
            'inventory_item_id' => $movement->inventory_item_id,
            'warehouse_id' => $movement->warehouse_id,
            'inventory_movement_id' => $movement->id,
            'quantity_received' => $qty,
            'quantity_remaining' => $qty,
            'unit_cost' => $unitCost,
            'layer_date' => $movement->movement_date,
        ]);

        self::applyWeightedAverageReceipt(
            $movement->company_id,
            $movement->branch_id,
            $movement->inventory_item_id,
            $movement->warehouse_id,
            $qty,
            $unitCost,
        );
    }

    public static function processIssue(InventoryMovement $movement): void
    {
        if (! self::isOutbound($movement)) {
            return;
        }

        $qty = abs((float) $movement->quantity);
        $company = Company::query()->find($movement->company_id);

        if ($company && CompanyCostingSettings::costingMethod($company) === InventoryCostingMethod::Fifo) {
            self::consumeFifoLayers(
                $movement->inventory_item_id,
                $movement->warehouse_id,
                $qty,
            );
        }

        self::applyWeightedAverageIssue(
            $movement->company_id,
            $movement->branch_id,
            $movement->inventory_item_id,
            $movement->warehouse_id,
            $qty,
        );
    }

    public static function resolveIssueUnitCost(
        int $companyId,
        int $branchId,
        int $itemId,
        int $warehouseId,
        float $quantity,
    ): float {
        if ($quantity <= 0) {
            return 0;
        }

        $company = Company::query()->find($companyId);
        $method = $company
            ? CompanyCostingSettings::costingMethod($company)
            : InventoryCostingMethod::Fifo;

        if ($method === InventoryCostingMethod::WeightedAverage) {
            $valuation = InventoryValuation::query()
                ->where('inventory_item_id', $itemId)
                ->where('warehouse_id', $warehouseId)
                ->first();

            if ($valuation && (float) $valuation->average_unit_cost > 0) {
                return (float) $valuation->average_unit_cost;
            }

            $item = InventoryItem::query()->find($itemId);

            return (float) ($item?->standard_cost ?? 0);
        }

        return self::peekFifoUnitCost($itemId, $warehouseId, $quantity);
    }

    public static function fifoValue(int $itemId, int $warehouseId): float
    {
        return (float) InventoryCostLayer::query()
            ->where('inventory_item_id', $itemId)
            ->where('warehouse_id', $warehouseId)
            ->where('quantity_remaining', '>', 0)
            ->selectRaw('SUM(quantity_remaining * unit_cost) as value')
            ->value('value');
    }

    private static function peekFifoUnitCost(int $itemId, int $warehouseId, float $quantity): float
    {
        $layers = self::orderedLayers($itemId, $warehouseId);
        $remaining = $quantity;
        $totalCost = 0.0;

        foreach ($layers as $layer) {
            if ($remaining <= 0) {
                break;
            }
            $take = min($remaining, (float) $layer->quantity_remaining);
            $totalCost += $take * (float) $layer->unit_cost;
            $remaining -= $take;
        }

        if ($remaining > 0) {
            $item = InventoryItem::query()->find($itemId);
            $totalCost += $remaining * (float) ($item?->standard_cost ?? 0);
        }

        return $quantity > 0 ? round($totalCost / $quantity, 2) : 0;
    }

    private static function consumeFifoLayers(int $itemId, int $warehouseId, float $quantity): void
    {
        DB::transaction(function () use ($itemId, $warehouseId, $quantity) {
            $remaining = $quantity;

            foreach (self::orderedLayers($itemId, $warehouseId, lock: true) as $layer) {
                if ($remaining <= 0) {
                    break;
                }

                $available = (float) $layer->quantity_remaining;
                if ($available <= 0) {
                    continue;
                }

                $take = min($remaining, $available);
                $layer->update([
                    'quantity_remaining' => round($available - $take, 3),
                ]);
                $remaining -= $take;
            }
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, InventoryCostLayer>
     */
    private static function orderedLayers(int $itemId, int $warehouseId, bool $lock = false)
    {
        $query = InventoryCostLayer::query()
            ->where('inventory_item_id', $itemId)
            ->where('warehouse_id', $warehouseId)
            ->where('quantity_remaining', '>', 0)
            ->orderBy('layer_date')
            ->orderBy('id');

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->get();
    }

    private static function applyWeightedAverageReceipt(
        int $companyId,
        int $branchId,
        int $itemId,
        int $warehouseId,
        float $qty,
        float $unitCost,
    ): void {
        $valuation = InventoryValuation::query()->firstOrCreate(
            [
                'inventory_item_id' => $itemId,
                'warehouse_id' => $warehouseId,
            ],
            [
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'quantity_on_hand' => 0,
                'average_unit_cost' => 0,
            ],
        );

        $onHand = (float) $valuation->quantity_on_hand;
        $avg = (float) $valuation->average_unit_cost;
        $newQty = $onHand + $qty;
        $newAvg = $newQty > 0
            ? round((($onHand * $avg) + ($qty * $unitCost)) / $newQty, 2)
            : $unitCost;

        $valuation->update([
            'quantity_on_hand' => round($newQty, 3),
            'average_unit_cost' => $newAvg,
            'last_calculated_at' => now(),
        ]);
    }

    private static function applyWeightedAverageIssue(
        int $companyId,
        int $branchId,
        int $itemId,
        int $warehouseId,
        float $qty,
    ): void {
        $valuation = InventoryValuation::query()->firstOrCreate(
            [
                'inventory_item_id' => $itemId,
                'warehouse_id' => $warehouseId,
            ],
            [
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'quantity_on_hand' => 0,
                'average_unit_cost' => 0,
            ],
        );

        $newQty = max(0, round((float) $valuation->quantity_on_hand - $qty, 3));

        $valuation->update([
            'quantity_on_hand' => $newQty,
            'last_calculated_at' => now(),
        ]);
    }

    private static function isInbound(InventoryMovement $movement): bool
    {
        return in_array($movement->movement_type, [
            InventoryMovementType::Receipt,
            InventoryMovementType::TransferIn,
            InventoryMovementType::FinishedGoodsReceipt,
            InventoryMovementType::ProductionOutput,
            InventoryMovementType::DispatchToTransit,
        ], true) && (float) $movement->quantity > 0;
    }

    private static function isOutbound(InventoryMovement $movement): bool
    {
        return in_array($movement->movement_type, [
            InventoryMovementType::Issue,
            InventoryMovementType::TransferOut,
            InventoryMovementType::ProductionConsumption,
            InventoryMovementType::DispatchToTransit,
            InventoryMovementType::DeliveryCogs,
        ], true);
    }
}
