<?php

namespace App\Services\PrintingIntelligence;

use App\Enums\CalibrationRuleType;
use App\Models\Assets\MachineProfile;

class MachineCostProfileService
{
    public function __construct(
        protected ActiveCostingProfileService $activeProfile,
    ) {}

    public function costPerHour(MachineProfile $machine, ?int $companyId = null): float
    {
        $companyId ??= (int) $machine->company_id;
        $calibrated = $this->activeProfile->value(CalibrationRuleType::MachineRate, null, $companyId, null);

        if ($calibrated !== null && (float) $calibrated > 0) {
            return (float) $calibrated;
        }

        if ($machine->cost_per_hour !== null && (float) $machine->cost_per_hour > 0) {
            return (float) $machine->cost_per_hour;
        }

        return 0.0;
    }

    public function estimatedElectricityCost(MachineProfile $machine, float $hours, ?int $companyId = null): float
    {
        $companyId ??= (int) $machine->company_id;
        $kw = (float) ($machine->power_rating_kw ?? 0);
        $rate = (float) $this->activeProfile->value(
            CalibrationRuleType::ElectricityRate,
            'electricity_rate_per_kwh',
            $companyId,
            0,
        );

        if ($kw <= 0 || $hours <= 0 || $rate <= 0) {
            return 0.0;
        }

        return round($kw * $hours * $rate, 2);
    }

    public function estimatedSetupCost(MachineProfile $machine, ?int $companyId = null): float
    {
        $setupMinutes = (int) ($machine->average_setup_minutes ?? 0);

        if ($setupMinutes <= 0) {
            return 0.0;
        }

        $hourly = $this->costPerHour($machine, $companyId);
        $factor = (float) ($machine->maintenance_cost_factor ?? 1);

        return round(($setupMinutes / 60) * $hourly * max($factor, 0), 2);
    }

    /**
     * Machine run + setup cost only. Electricity is tracked separately (PI9.7).
     */
    public function estimatedMachineCost(MachineProfile $machine, float $runHours, ?int $companyId = null): float
    {
        $runCost = $this->costPerHour($machine, $companyId) * max($runHours, 0);
        $setup = $this->estimatedSetupCost($machine, $companyId);
        $factor = (float) ($machine->maintenance_cost_factor ?? 1);

        return round(($runCost * max($factor, 1)) + $setup, 2);
    }
}
