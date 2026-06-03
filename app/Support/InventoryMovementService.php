<?php

namespace App\Support;

use App\Enums\InventoryMovementType;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryMovement;
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
        InventoryStockService::assertPositiveResult($current, $quantity);

        return DB::transaction(function () use ($attributes, $itemId, $warehouseId) {
            $movement = InventoryMovement::query()->create($attributes);

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
}
