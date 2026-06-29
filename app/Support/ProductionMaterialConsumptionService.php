<?php

namespace App\Support;

use App\Enums\InventoryMovementType;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\ProductionMaterialConsumption;
use App\Models\Inventory\Warehouse;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionMaterialRequirement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductionMaterialConsumptionService
{
    /**
     * Records raw material consumption for a production job.
     *
     * This is the sole accounting source for Dr WIP / Cr Raw Materials.
     * Stock issues to production do not post WIP journals (Phase I4.1).
     *
     * Inventory lifecycle: reduces physical raw material stock only (WIP is GL-only).
     *
     * @see config('inventory_lifecycle')
     */
    public static function consume(
        ProductionJobCard $jobCard,
        InventoryItem $item,
        int $warehouseId,
        float $quantity,
        int $userId,
        ?float $unitCost = null,
        ?int $requirementId = null,
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

        $warehouse = Warehouse::query()->find($warehouseId);
        if ($warehouse === null || $warehouse->is_virtual) {
            throw ValidationException::withMessages([
                'warehouse_id' => __('Consume raw materials from a physical warehouse. Virtual locations such as Finished Goods only hold completed output.'),
            ]);
        }

        InventoryStockService::assertSufficientStock($item->id, $warehouseId, $quantity);

        return DB::transaction(function () use ($jobCard, $item, $warehouseId, $quantity, $userId, $unitCost, $requirementId) {
            $requirementsService = app(\App\Support\Production\MaterialRequirementsService::class);

            if ($requirementId === null) {
                $matchedRequirement = $requirementsService->findOpenRequirement(
                    $jobCard,
                    (int) $item->id,
                    $warehouseId,
                );

                if ($matchedRequirement !== null) {
                    $matchedRequirement = ProductionMaterialRequirement::query()
                        ->whereKey($matchedRequirement->id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    $requirementsService->linkOrphanConsumptions($matchedRequirement);

                    if ($quantity > $matchedRequirement->remainingQuantity()) {
                        throw ValidationException::withMessages([
                            'quantity' => __('Quantity exceeds remaining requirement. Only :remaining units remain on this job.', [
                                'remaining' => $matchedRequirement->remainingQuantity(),
                            ]),
                        ]);
                    }

                    $requirementId = $matchedRequirement->id;
                }
            }

            $cost = $unitCost ?? \App\Support\Inventory\InventoryCostingService::resolveIssueUnitCost(
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
                'movement_type' => InventoryMovementType::ProductionConsumption,
                'quantity' => InventoryMovementService::issueQuantity($quantity),
                'unit_cost' => $cost,
                'reference_type' => ProductionJobCard::class,
                'reference_id' => $jobCard->id,
                'movement_date' => now()->toDateString(),
                'created_by' => $userId,
            ]);

            $consumption = ProductionMaterialConsumption::query()->create([
                'company_id' => $jobCard->company_id,
                'branch_id' => $jobCard->branch_id,
                'production_job_card_id' => $jobCard->id,
                'production_material_requirement_id' => $requirementId,
                'inventory_item_id' => $item->id,
                'warehouse_id' => $warehouseId,
                'inventory_movement_id' => $movement->id,
                'quantity' => $quantity,
                'unit_cost' => $cost,
                'consumed_by' => $userId,
                'consumed_at' => now(),
            ]);

            app(\App\Support\Accounting\InventoryAccountingPostingService::class)
                ->postMaterialConsumption($consumption, $userId);

            \App\Support\Production\JobCostingService::syncFromConsumption($consumption);

            if ($requirementId !== null) {
                $requirement = \App\Models\Production\ProductionMaterialRequirement::query()->find($requirementId);
                if ($requirement) {
                    app(\App\Support\Production\MaterialRequirementsService::class)
                        ->syncRequirementFromConsumption($requirement);
                }
            }

            return $consumption;
        });
    }
}
