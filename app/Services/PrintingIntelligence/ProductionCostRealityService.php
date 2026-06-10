<?php

namespace App\Services\PrintingIntelligence;

use App\Models\Inventory\InventoryMovement;
use App\Models\Inventory\ProductionMaterialConsumption;
use App\Models\Production\JobCostSheet;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionOutput;

class ProductionCostRealityService
{
    public function actualMaterialCost(int $jobCardId): float
    {
        $sheet = $this->costSheet($jobCardId);

        return (float) ($sheet?->material_cost ?? 0);
    }

    public function actualProductionCost(int $jobCardId): float
    {
        $sheet = $this->costSheet($jobCardId);

        return (float) ($sheet?->total_cost ?? 0);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function actualConsumption(int $jobCardId): array
    {
        return ProductionMaterialConsumption::query()
            ->where('production_job_card_id', $jobCardId)
            ->with('inventoryItem:id,sku,item_name')
            ->get()
            ->map(fn (ProductionMaterialConsumption $row) => [
                'inventory_item_id' => $row->inventory_item_id,
                'sku' => $row->inventoryItem?->sku,
                'name' => $row->inventoryItem?->item_name,
                'quantity' => (float) $row->quantity,
                'unit_cost' => (float) $row->unit_cost,
                'line_total' => round((float) $row->quantity * (float) $row->unit_cost, 2),
                'inventory_movement_id' => $row->inventory_movement_id,
            ])
            ->values()
            ->all();
    }

    public function actualMachineCost(int $jobCardId): float
    {
        $sheet = $this->costSheet($jobCardId);

        return (float) ($sheet?->machine_cost ?? 0);
    }

    public function actualJobCost(int $jobCardId): float
    {
        return $this->actualProductionCost($jobCardId);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function jobProfitability(int $jobCardId): ?array
    {
        $sheet = $this->costSheet($jobCardId);

        if ($sheet === null) {
            return null;
        }

        $output = ProductionOutput::query()
            ->where('production_job_card_id', $jobCardId)
            ->latest('completed_at')
            ->first();

        return [
            'material_cost' => (float) $sheet->material_cost,
            'machine_cost' => (float) $sheet->machine_cost,
            'labor_cost' => (float) $sheet->labor_cost,
            'overhead_cost' => (float) $sheet->overhead_cost,
            'total_cost' => (float) $sheet->total_cost,
            'revenue' => (float) $sheet->revenue,
            'gross_profit' => (float) $sheet->gross_profit,
            'gross_margin_percent' => (float) $sheet->gross_margin_percent,
            'output_quantity' => $output !== null ? (float) $output->quantity_completed : null,
            'output_total_cost' => $output !== null ? (float) $output->total_cost : null,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function movementLedgerForJob(int $jobCardId): array
    {
        $consumptionMovementIds = ProductionMaterialConsumption::query()
            ->where('production_job_card_id', $jobCardId)
            ->whereNotNull('inventory_movement_id')
            ->pluck('inventory_movement_id');

        if ($consumptionMovementIds->isEmpty()) {
            return [];
        }

        return InventoryMovement::query()
            ->whereIn('id', $consumptionMovementIds)
            ->get(['id', 'inventory_item_id', 'warehouse_id', 'movement_type', 'quantity', 'unit_cost', 'movement_date'])
            ->map(fn (InventoryMovement $movement) => [
                'id' => $movement->id,
                'inventory_item_id' => $movement->inventory_item_id,
                'warehouse_id' => $movement->warehouse_id,
                'movement_type' => $movement->movement_type?->value ?? (string) $movement->movement_type,
                'quantity' => (float) $movement->quantity,
                'unit_cost' => (float) $movement->unit_cost,
                'movement_date' => $movement->movement_date?->toDateString(),
            ])
            ->values()
            ->all();
    }

    protected function costSheet(int $jobCardId): ?JobCostSheet
    {
        return JobCostSheet::query()
            ->where('production_job_card_id', $jobCardId)
            ->latest('calculated_at')
            ->first();
    }

    public function jobCardExists(int $jobCardId): bool
    {
        return ProductionJobCard::query()->whereKey($jobCardId)->exists();
    }
}
