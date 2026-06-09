<?php

namespace App\Support\Production;

use App\Enums\InventoryMovementType;
use App\Enums\ProductionMaterialFlowType;
use App\Enums\ProductionWasteType;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\ProductionMaterialConsumption;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionWastageRecord;
use App\Support\Inventory\InventoryCostingService;
use App\Support\InventoryMovementService;
use App\Support\InventoryStockService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductionWastageService
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function recordWaste(ProductionJobCard $jobCard, array $payload, int $userId): ProductionWastageRecord
    {
        return $this->recordFlow($jobCard, $payload, $userId, ProductionMaterialFlowType::Wasted);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function recordReturn(ProductionJobCard $jobCard, array $payload, int $userId): ProductionWastageRecord
    {
        return $this->recordFlow($jobCard, $payload, $userId, ProductionMaterialFlowType::Returned);
    }

    /**
     * @return array<string, mixed>
     */
    public function jobMetrics(ProductionJobCard $jobCard): array
    {
        $consumed = (float) ProductionMaterialConsumption::query()
            ->where('production_job_card_id', $jobCard->id)
            ->sum('quantity');

        $wasted = (float) ProductionWastageRecord::query()
            ->where('production_job_card_id', $jobCard->id)
            ->where('flow_type', ProductionMaterialFlowType::Wasted)
            ->sum('quantity');

        $returned = (float) ProductionWastageRecord::query()
            ->where('production_job_card_id', $jobCard->id)
            ->where('flow_type', ProductionMaterialFlowType::Returned)
            ->sum('quantity');

        $wasteCost = (float) ProductionWastageRecord::query()
            ->where('production_job_card_id', $jobCard->id)
            ->where('flow_type', ProductionMaterialFlowType::Wasted)
            ->sum('line_cost');

        $issued = max(0, $consumed + $wasted - $returned);
        $denominator = $consumed + $wasted;

        return [
            'material_issued' => round($issued, 3),
            'material_consumed' => round($consumed, 3),
            'material_wasted' => round($wasted, 3),
            'material_returned' => round($returned, 3),
            'waste_cost' => round($wasteCost, 2),
            'waste_percent' => $issued > 0 ? round(($wasted / $issued) * 100, 2) : 0,
            'yield_percent' => $issued > 0 ? round(($consumed / $issued) * 100, 2) : 0,
            'material_efficiency_percent' => $denominator > 0 ? round(($consumed / $denominator) * 100, 2) : 0,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function summaryForJob(ProductionJobCard $jobCard): array
    {
        $metrics = $this->jobMetrics($jobCard);
        $lineCount = (int) ProductionWastageRecord::query()
            ->where('production_job_card_id', $jobCard->id)
            ->where('flow_type', ProductionMaterialFlowType::Wasted)
            ->count();

        return [
            'activated' => true,
            'total_quantity' => $metrics['material_wasted'],
            'line_count' => $lineCount,
            'waste_cost' => $metrics['waste_cost'],
            'metrics' => $metrics,
            'placeholder' => null,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function recordFlow(
        ProductionJobCard $jobCard,
        array $payload,
        int $userId,
        ProductionMaterialFlowType $flowType,
    ): ProductionWastageRecord {
        $quantity = (float) ($payload['quantity'] ?? 0);
        if ($quantity <= 0) {
            throw ValidationException::withMessages([
                'quantity' => __('Quantity must be greater than zero.'),
            ]);
        }

        /** @var InventoryItem $item */
        $item = InventoryItem::query()->findOrFail((int) $payload['inventory_item_id']);
        $warehouseId = (int) $payload['warehouse_id'];

        if ($item->company_id !== $jobCard->company_id || $item->branch_id !== $jobCard->branch_id) {
            throw ValidationException::withMessages([
                'inventory_item_id' => __('Item must belong to the same company and branch as the job card.'),
            ]);
        }

        if ($flowType->isOutbound()) {
            InventoryStockService::assertSufficientStock($item->id, $warehouseId, $quantity);
        }

        $wasteType = null;
        $customReason = null;
        if ($flowType === ProductionMaterialFlowType::Wasted) {
            $wasteType = ProductionWasteType::from((string) $payload['waste_type']);
            if ($wasteType === ProductionWasteType::Custom) {
                $customReason = trim((string) ($payload['custom_reason'] ?? ''));
                if ($customReason === '') {
                    throw ValidationException::withMessages([
                        'custom_reason' => __('Custom waste reason is required.'),
                    ]);
                }
            }
        }

        return DB::transaction(function () use (
            $jobCard, $item, $warehouseId, $quantity, $userId, $flowType, $payload, $wasteType, $customReason
        ) {
            $unitCost = isset($payload['unit_cost'])
                ? (float) $payload['unit_cost']
                : InventoryCostingService::resolveIssueUnitCost(
                    $jobCard->company_id,
                    $jobCard->branch_id,
                    $item->id,
                    $warehouseId,
                    $quantity,
                );

            $movementType = $flowType->isOutbound()
                ? InventoryMovementType::ProductionConsumption
                : InventoryMovementType::Adjustment;

            $signedQty = $flowType->isOutbound()
                ? InventoryMovementService::issueQuantity($quantity)
                : InventoryMovementService::receiptQuantity($quantity);

            $movement = InventoryMovementService::record([
                'company_id' => $jobCard->company_id,
                'branch_id' => $jobCard->branch_id,
                'inventory_item_id' => $item->id,
                'warehouse_id' => $warehouseId,
                'movement_type' => $movementType,
                'quantity' => $signedQty,
                'unit_cost' => $unitCost,
                'reference_type' => ProductionJobCard::class,
                'reference_id' => $jobCard->id,
                'movement_date' => now()->toDateString(),
                'created_by' => $userId,
            ]);

            $record = ProductionWastageRecord::query()->create([
                'company_id' => $jobCard->company_id,
                'branch_id' => $jobCard->branch_id,
                'production_job_card_id' => $jobCard->id,
                'inventory_item_id' => $item->id,
                'warehouse_id' => $warehouseId,
                'flow_type' => $flowType,
                'waste_type' => $wasteType,
                'custom_reason' => $customReason,
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'line_cost' => round($quantity * $unitCost, 2),
                'inventory_movement_id' => $movement->id,
                'employee_id' => $payload['employee_id'] ?? null,
                'machine_profile_id' => $payload['machine_profile_id']
                    ?? $jobCard->assignedMachine?->machineProfile?->id,
                'recorded_by' => $userId,
                'recorded_at' => now(),
                'notes' => $payload['notes'] ?? null,
            ]);

            JobCostingService::buildOrRefresh($jobCard);

            return $record;
        });
    }
}
