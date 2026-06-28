<?php

namespace App\Support;

use App\Enums\InventoryMovementType;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryMovement;
use App\Support\Inventory\InventoryCostingService;
use Illuminate\Support\Facades\DB;

class InventoryMovementService
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public static function record(array $attributes): InventoryMovement
    {
        $quantity = (float) $attributes['quantity'];
        $itemId = (int) $attributes['inventory_item_id'];
        $warehouseId = (int) $attributes['warehouse_id'];

        $current = InventoryStockService::balanceUncached($itemId, $warehouseId);
        InventoryStockService::assertPositiveResult(
            $current,
            $quantity,
            (int) ($attributes['company_id'] ?? 0) ?: null,
            (int) ($attributes['branch_id'] ?? 0) ?: null,
        );

        return DB::transaction(function () use ($attributes, $itemId, $warehouseId) {
            $attributes = self::applyCosting($attributes);

            $movement = InventoryMovement::query()->create($attributes);

            if (self::isInboundType($attributes['movement_type'])) {
                InventoryCostingService::processReceipt($movement);
            } elseif (self::isOutboundType($attributes['movement_type'])) {
                InventoryCostingService::processIssue($movement);
            }

            InventoryStockService::forgetBalanceCache($itemId, $warehouseId);

            $item = InventoryItem::query()->find($itemId);
            if ($item) {
                InventoryStockService::syncReorderAlerts($item);
            }

            return $movement;
        });
    }

    public static function receiptQuantity(float $qty): float
    {
        return abs($qty);
    }

    public static function issueQuantity(float $qty): float
    {
        return -abs($qty);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private static function applyCosting(array $attributes): array
    {
        $type = $attributes['movement_type'] ?? null;

        if (! self::isOutboundType($type)) {
            return $attributes;
        }

        $qty = abs((float) ($attributes['quantity'] ?? 0));
        if ($qty <= 0) {
            return $attributes;
        }

        $unitCost = (float) ($attributes['unit_cost'] ?? 0);
        if ($unitCost > 0) {
            return $attributes;
        }

        $attributes['unit_cost'] = InventoryCostingService::resolveIssueUnitCost(
            (int) $attributes['company_id'],
            (int) $attributes['branch_id'],
            (int) $attributes['inventory_item_id'],
            (int) $attributes['warehouse_id'],
            $qty,
        );

        return $attributes;
    }

    private static function isInboundType(mixed $type): bool
    {
        return in_array($type, [
            InventoryMovementType::Receipt,
            InventoryMovementType::TransferIn,
            InventoryMovementType::FinishedGoodsReceipt,
            InventoryMovementType::ProductionOutput,
            InventoryMovementType::DispatchToTransit,
            InventoryMovementType::ProductionReturn,
        ], true);
    }

    private static function isOutboundType(mixed $type): bool
    {
        return in_array($type, [
            InventoryMovementType::Issue,
            InventoryMovementType::TransferOut,
            InventoryMovementType::ProductionConsumption,
            InventoryMovementType::ProductionIssue,
            InventoryMovementType::ProductionWaste,
            InventoryMovementType::DispatchToTransit,
            InventoryMovementType::DeliveryCogs,
        ], true);
    }
}
