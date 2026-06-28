<?php

namespace App\Support\Production;

use App\Models\Inventory\ProductionMaterialConsumption;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionMaterialIssue;

class ProductionMaterialCostVisibilityService
{
    public function __construct(
        protected ProductionWastageService $wastage,
        protected SerialNumberGovernanceService $serials,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function summary(ProductionJobCard $jobCard): array
    {
        $requirements = $jobCard->relationLoaded('materialRequirements')
            ? $jobCard->materialRequirements
            : $jobCard->materialRequirements()->get();

        $estimated = (float) $requirements->sum('estimated_cost');

        $issuedCost = (float) ProductionMaterialIssue::query()
            ->where('production_job_card_id', $jobCard->id)
            ->selectRaw('COALESCE(SUM(quantity * unit_cost), 0) as total')
            ->value('total');

        $consumedCost = (float) ProductionMaterialConsumption::query()
            ->where('production_job_card_id', $jobCard->id)
            ->selectRaw('COALESCE(SUM(quantity * COALESCE(unit_cost, 0)), 0) as total')
            ->value('total');

        $wasteMetrics = $this->wastage->jobMetrics($jobCard);
        $sessionMetrics = app(ProductionSessionService::class)->jobMetrics($jobCard);
        $serialLoss = $this->serials->productionLossMetrics($jobCard);

        return [
            'estimated_material_cost' => round($estimated, 2),
            'issued_material_cost' => round($issuedCost, 2),
            'consumed_material_cost' => round($consumedCost, 2),
            'waste_cost' => round((float) $wasteMetrics['waste_cost'], 2),
            'material_waste_qty' => (float) $wasteMetrics['material_wasted'],
            'session_waste_qty' => (float) $sessionMetrics['total_waste'],
            'serial_spoilage_qty' => (float) ($serialLoss['spoiled_quantity'] ?? 0),
            'returned_qty' => (float) $wasteMetrics['material_returned'],
        ];
    }
}
