<?php

namespace App\Services\PrintingIntelligence;

use App\Models\Inventory\InventoryItem;
use App\Models\PrintingIntelligence\PrintArtworkAnalysis;
use App\Models\PrintingIntelligence\PrintArtworkInkEstimate;
use App\Models\PrintingIntelligence\PrintArtworkProductionEstimate;

class QuotationCostComposerService
{
    public function __construct(
        protected MaterialCostContextService $materialCostContext,
        protected ActiveCostingProfileService $activeProfile,
    ) {}

    /**
     * @param  array{
     *     quantity?: int,
     *     material_inventory_item_id?: int|null,
     *     material_unit_cost_override?: float|null,
     *     material_quantity_override?: float|null,
     *     wastage_percent?: float|null
     * }  $params
     * @return array{
     *     material_cost: float,
     *     ink_cost: float,
     *     machine_cost: float,
     *     labour_cost: float,
     *     electricity_cost: float,
     *     overhead_cost: float,
     *     wastage_cost: float,
     *     material_unit_cost: float|null,
     *     material_quantity: float|null,
     *     material_name: string|null,
     *     material_inventory_item_id: int|null,
     *     print_artwork_ink_estimate_id: int|null,
     *     print_machine_estimate_id: int|null,
     *     warnings: list<string>,
     *     status: string,
     *     breakdown: array<string, mixed>
     * }
     */
    public function compose(PrintArtworkAnalysis $analysis, array $params = []): array
    {
        $analysis->loadMissing(['inkEstimates', 'productionEstimate', 'pages']);
        $quantity = max(1, (int) ($params['quantity'] ?? 1));
        $companyId = (int) $analysis->company_id;
        $wastagePercent = (float) ($params['wastage_percent'] ?? $this->activeProfile->value(
            \App\Enums\CalibrationRuleType::WastageFactor,
            'default_wastage_percent',
            $companyId,
            5,
        ));
        $warnings = [];
        $manualReview = false;

        /** @var PrintArtworkInkEstimate|null $inkEstimate */
        $inkEstimate = $analysis->inkEstimates->first();
        $productionEstimate = $analysis->productionEstimate;

        $inkCost = 0.0;
        if ($inkEstimate !== null && $inkEstimate->estimated_ink_cost !== null) {
            $scale = $productionEstimate !== null
                ? $this->productionScale($productionEstimate, $quantity)
                : (float) $quantity;
            $inkCost = round((float) $inkEstimate->estimated_ink_cost * $scale, 2);
        } else {
            $warnings[] = __('Ink estimate missing; ink cost unavailable.');
            $manualReview = true;
        }

        $machineCost = 0.0;
        $labourCost = 0.0;
        $electricityCost = 0.0;
        $overheadCost = 0.0;

        if ($productionEstimate !== null) {
            $machineCost = round((float) ($productionEstimate->estimated_machine_cost ?? 0) * $this->productionScale($productionEstimate, $quantity), 2);
            $labourCost = round((float) ($productionEstimate->estimated_labour_cost ?? 0) * $this->productionScale($productionEstimate, $quantity), 2);
            $electricityCost = round((float) ($productionEstimate->estimated_electricity_cost ?? 0) * $this->productionScale($productionEstimate, $quantity), 2);
            $overheadCost = round((float) ($productionEstimate->estimated_overhead_cost ?? 0) * $this->productionScale($productionEstimate, $quantity), 2);

            $labourBuffer = (float) config('printing_intelligence.default_labour_buffer_percent', 0);
            if ($labourBuffer > 0) {
                $labourCost = round($labourCost * (1 + ($labourBuffer / 100)), 2);
            }
        } else {
            $warnings[] = __('Machine/production estimate missing; process costs unavailable.');
            $manualReview = true;
        }

        $materialItemId = isset($params['material_inventory_item_id']) ? (int) $params['material_inventory_item_id'] : null;
        $materialUnitCost = isset($params['material_unit_cost_override']) ? (float) $params['material_unit_cost_override'] : null;
        $materialQuantity = isset($params['material_quantity_override']) ? (float) $params['material_quantity_override'] : null;
        $materialName = null;
        $materialCost = 0.0;

        if ($materialItemId) {
            $item = InventoryItem::query()->find($materialItemId);
            if ($item === null) {
                $warnings[] = __('Material inventory item not found.');
                $manualReview = true;
            } else {
                $materialName = $item->item_name;
                if ($materialUnitCost === null || $materialUnitCost <= 0) {
                    $materialUnitCost = $this->materialCostContext->currentCost($materialItemId);
                }
            }
        }

        if ($materialQuantity === null || $materialQuantity <= 0) {
            $materialQuantity = $this->resolveMaterialQuantity($analysis, $productionEstimate, $quantity);
        }

        if ($materialUnitCost !== null && $materialUnitCost > 0 && $materialQuantity > 0) {
            $materialCost = round($materialUnitCost * $materialQuantity, 2);
        } elseif ($materialItemId || ($params['material_unit_cost_override'] ?? null) !== null) {
            $warnings[] = __('Material cost could not be resolved from inventory context.');
            $manualReview = true;
        } else {
            $warnings[] = __('Material not selected; material cost unavailable.');
            $manualReview = true;
        }

        $wastageBase = $materialCost + $inkCost;
        $wastageCost = round($wastageBase * ($wastagePercent / 100), 2);

        return [
            'material_cost' => $materialCost,
            'ink_cost' => $inkCost,
            'machine_cost' => $machineCost,
            'labour_cost' => $labourCost,
            'electricity_cost' => $electricityCost,
            'overhead_cost' => $overheadCost,
            'wastage_cost' => $wastageCost,
            'material_unit_cost' => $materialUnitCost,
            'material_quantity' => $materialQuantity,
            'material_name' => $materialName,
            'material_inventory_item_id' => $materialItemId,
            'print_artwork_ink_estimate_id' => $inkEstimate?->id,
            'print_machine_estimate_id' => $productionEstimate?->id,
            'warnings' => $warnings,
            'status' => $manualReview ? 'manual_review' : 'completed',
            'breakdown' => [
                'quantity' => $quantity,
                'wastage_percent' => $wastagePercent,
                'labour_buffer_percent' => (float) config('printing_intelligence.default_labour_buffer_percent', 0),
                'production_scale' => $productionEstimate ? $this->productionScale($productionEstimate, $quantity) : null,
            ],
        ];
    }

    protected function productionScale(PrintArtworkProductionEstimate $estimate, int $quantity): float
    {
        $baseQty = max(1, (int) $estimate->quantity);

        return $quantity / $baseQty;
    }

    protected function resolveMaterialQuantity(
        PrintArtworkAnalysis $analysis,
        ?PrintArtworkProductionEstimate $productionEstimate,
        int $quantity,
    ): float {
        if ($productionEstimate?->total_area_sq_m !== null && (float) $productionEstimate->total_area_sq_m > 0) {
            return round((float) $productionEstimate->total_area_sq_m * ($quantity / max(1, (int) $productionEstimate->quantity)), 6);
        }

        $pageCount = max(1, (int) ($analysis->page_count ?? $analysis->pages->count() ?: 1));
        $pageArea = (float) ($analysis->area_square_m ?? 0);

        if ($pageArea > 0) {
            return round($pageArea * $pageCount * $quantity, 6);
        }

        return (float) $quantity;
    }
}
