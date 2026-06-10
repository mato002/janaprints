<?php

namespace App\Services\PrintingIntelligence;

use App\Models\PrintingIntelligence\PrintInkProfile;

class InkCostCalculator
{
    public function __construct(
        protected InkCostProfileService $inkCostProfile,
    ) {}

    /**
     * @return array{
     *     estimated_ink_cost: float|null,
     *     cost_per_ml: float|null,
     *     warnings: list<string>
     * }
     */
    public function calculate(PrintInkProfile $profile, float $estimatedTotalMl, ?int $companyId = null): array
    {
        $warnings = [];
        $companyId ??= (int) $profile->company_id;

        if ($estimatedTotalMl <= 0) {
            return [
                'estimated_ink_cost' => 0.0,
                'cost_per_ml' => $this->inkCostProfile->costPerMl($profile, $companyId),
                'warnings' => $warnings,
            ];
        }

        $costPerMl = $this->inkCostProfile->costPerMl($profile, $companyId);

        if ($costPerMl === null || $costPerMl <= 0) {
            $warnings[] = __('Ink profile missing cost_per_ml and cartridge yield; cost estimate unavailable.');

            return [
                'estimated_ink_cost' => null,
                'cost_per_ml' => null,
                'warnings' => $warnings,
            ];
        }

        return [
            'estimated_ink_cost' => round($estimatedTotalMl * $costPerMl, 2),
            'cost_per_ml' => $costPerMl,
            'warnings' => $warnings,
        ];
    }
}
