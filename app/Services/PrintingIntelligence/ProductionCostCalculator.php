<?php

namespace App\Services\PrintingIntelligence;

use App\Enums\CalibrationRuleType;
use App\Models\Assets\MachineProfile;

class ProductionCostCalculator
{
    public function __construct(
        protected MachineCostProfileService $machineCostProfile,
        protected ActiveCostingProfileService $activeProfile,
    ) {}

    /**
     * PI4-V1 production cost rollup (estimation only).
     *
     * estimated_setup_cost = MachineCostProfileService::estimatedSetupCost
     * estimated_electricity_cost = estimatedElectricityCost(run_hours)
     * estimated_machine_cost = run + setup (excludes electricity)
     * estimated_labour_cost = (run_hours × labour_hourly_rate) + setup labour
     * estimated_overhead_cost = (machine + labour) × overhead_percent / 100
     * estimated_total = machine + electricity + labour + overhead + ink + material
     *
     * @return array{
     *     estimated_setup_cost: float,
     *     estimated_electricity_cost: float,
     *     estimated_machine_cost: float,
     *     estimated_labour_cost: float,
     *     estimated_overhead_cost: float,
     *     estimated_total_production_cost: float,
     *     metadata: array<string, mixed>
     * }
     */
    public function calculate(
        MachineProfile $machine,
        float $runHours,
        float $estimatedInkCost = 0.0,
        float $estimatedMaterialCost = 0.0,
    ): array {
        $companyId = (int) $machine->company_id;
        $setupCost = $this->machineCostProfile->estimatedSetupCost($machine, $companyId);
        $electricityCost = $this->machineCostProfile->estimatedElectricityCost($machine, $runHours, $companyId);
        $machineCost = $this->machineCostProfile->estimatedMachineCost($machine, $runHours, $companyId);

        $labourRate = (float) $this->activeProfile->value(
            CalibrationRuleType::LabourRate,
            'labour_hourly_rate',
            $companyId,
            0,
        );
        $setupMinutes = (int) ($machine->average_setup_minutes ?? 0);
        $labourRunCost = $labourRate * max(0, $runHours);
        $labourSetupCost = $labourRate * ($setupMinutes / 60);
        $labourCost = round($labourRunCost + $labourSetupCost, 2);

        $overheadPercent = (float) $this->activeProfile->value(
            CalibrationRuleType::OverheadRate,
            'default_overhead_percent',
            $companyId,
            10,
        );
        $overheadBase = $machineCost + $labourCost;
        $overheadCost = round($overheadBase * ($overheadPercent / 100), 2);

        $total = round($machineCost + $electricityCost + $labourCost + $overheadCost + $estimatedInkCost + $estimatedMaterialCost, 2);

        return [
            'estimated_setup_cost' => $setupCost,
            'estimated_electricity_cost' => $electricityCost,
            'estimated_machine_cost' => $machineCost,
            'estimated_labour_cost' => $labourCost,
            'estimated_overhead_cost' => $overheadCost,
            'estimated_total_production_cost' => $total,
            'metadata' => [
                'labour_hourly_rate' => $labourRate,
                'overhead_percent' => $overheadPercent,
                'run_hours' => round($runHours, 4),
                'cost_per_hour' => $this->machineCostProfile->costPerHour($machine, $companyId),
                'costing_profile_source' => 'active_costing_profile',
            ],
        ];
    }
}
