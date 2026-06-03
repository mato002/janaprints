<?php

namespace App\Support;

use App\Enums\InventoryMovementType;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\ProductionMaterialConsumption;
use App\Models\Production\ProductionJobCard;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductionMaterialConsumptionService
{
    public static function consume(
        ProductionJobCard $jobCard,
        InventoryItem $item,
        int $warehouseId,
        float $quantity,
        int $userId,
        ?float $unitCost = null,
    ): ProductionMaterialConsumption {
        if ($quantity <= 0) {
            throw ValidationException::withMessages([
                'quantity' => __('Quantity must be greater than zero.'),
            ]);
        }

        if ($item->company_id !== $jobCard->company_id || $item->branch_id !== $jobCard->branch_id) {
            throw ValidationException::withMessages([
                'inventory_item_id' => __('Item must belong to the same company and branch as the job card.'),
            ]);
        }

        InventoryStockService::assertSufficientStock($item->id, $warehouseId, $quantity);

        return DB::transaction(function () use ($jobCard, $item, $warehouseId, $quantity, $userId, $unitCost) {
            $cost = $unitCost ?? (float) $item->standard_cost;

            $movement = InventoryMovementService::record([
                'company_id' => $jobCard->company_id,
                'branch_id' => $jobCard->branch_id,
                'inventory_item_id' => $item->id,
                'warehouse_id' => $warehouseId,
                'movement_type' => InventoryMovementType::ProductionConsumption,
                'quantity' => InventoryMovementService::issueQuantity($quantity),
                'unit_cost' => $cost,
                'reference_type' => ProductionJobCard::class,
                'reference_id' => $jobCard->id,
                'movement_date' => now()->toDateString(),
                'created_by' => $userId,
            ]);

            return ProductionMaterialConsumption::query()->create([
                'company_id' => $jobCard->company_id,
                'branch_id' => $jobCard->branch_id,
                'production_job_card_id' => $jobCard->id,
                'inventory_item_id' => $item->id,
                'warehouse_id' => $warehouseId,
                'inventory_movement_id' => $movement->id,
                'quantity' => $quantity,
                'unit_cost' => $cost,
                'consumed_by' => $userId,
                'consumed_at' => now(),
            ]);
        });
    }
}
