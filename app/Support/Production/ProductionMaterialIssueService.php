<?php

namespace App\Support\Production;

use App\Enums\InventoryMovementType;
use App\Models\Inventory\InventoryItem;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionMaterialIssue;
use App\Models\Production\ProductionMaterialRequirement;
use App\Support\Inventory\InventoryCostingService;
use App\Support\InventoryMovementService;
use App\Support\InventoryStockService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductionMaterialIssueService
{
    public function issue(
        ProductionJobCard $jobCard,
        InventoryItem $item,
        int $warehouseId,
        float $quantity,
        int $userId,
        ?ProductionMaterialRequirement $requirement = null,
        ?string $notes = null,
    ): ProductionMaterialIssue {
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

        return DB::transaction(function () use ($jobCard, $item, $warehouseId, $quantity, $userId, $requirement, $notes) {
            $unitCost = InventoryCostingService::resolveIssueUnitCost(
                $jobCard->company_id,
                $jobCard->branch_id,
                $item->id,
                $warehouseId,
                $quantity,
            );

            $movement = InventoryMovementService::record([
                'company_id' => $jobCard->company_id,
                'branch_id' => $jobCard->branch_id,
                'inventory_item_id' => $item->id,
                'warehouse_id' => $warehouseId,
                'movement_type' => InventoryMovementType::ProductionIssue,
                'quantity' => InventoryMovementService::issueQuantity($quantity),
                'unit_cost' => $unitCost,
                'reference_type' => ProductionJobCard::class,
                'reference_id' => $jobCard->id,
                'movement_date' => now()->toDateString(),
                'created_by' => $userId,
            ]);

            $issue = ProductionMaterialIssue::query()->create([
                'company_id' => $jobCard->company_id,
                'branch_id' => $jobCard->branch_id,
                'production_job_card_id' => $jobCard->id,
                'production_material_requirement_id' => $requirement?->id,
                'inventory_item_id' => $item->id,
                'warehouse_id' => $warehouseId,
                'inventory_movement_id' => $movement->id,
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'issued_by' => $userId,
                'issued_at' => now(),
                'notes' => $notes,
            ]);

            if ($requirement !== null) {
                $requirement->update([
                    'issued_quantity' => round((float) $requirement->issued_quantity + $quantity, 3),
                ]);
            }

            return $issue->fresh(['inventoryItem', 'warehouse', 'issuer']);
        });
    }

    public function issueFromRequirement(
        ProductionMaterialRequirement $requirement,
        int $userId,
        ?float $quantity = null,
    ): ProductionMaterialIssue {
        $qty = $quantity ?? $requirement->remainingToIssue();

        if ($qty <= 0) {
            throw ValidationException::withMessages([
                'quantity' => __('Nothing remaining to issue for this requirement.'),
            ]);
        }

        $jobCard = $requirement->jobCard ?? ProductionJobCard::query()->findOrFail($requirement->production_job_card_id);
        $item = $requirement->inventoryItem ?? InventoryItem::query()->findOrFail($requirement->inventory_item_id);

        return $this->issue(
            $jobCard,
            $item,
            (int) $requirement->warehouse_id,
            $qty,
            $userId,
            $requirement,
        );
    }
}
