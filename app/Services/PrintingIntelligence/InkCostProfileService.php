<?php

namespace App\Services\PrintingIntelligence;

use App\Models\PrintingIntelligence\PrintInkProfile;

class InkCostProfileService
{
    public function __construct(
        protected ActiveCostingProfileService $activeProfile,
    ) {}

    public function costPerMl(PrintInkProfile $profile, ?int $companyId = null): ?float
    {
        if ($profile->cost_per_ml !== null && (float) $profile->cost_per_ml > 0) {
            return (float) $profile->cost_per_ml;
        }

        $companyId ??= (int) $profile->company_id;
        $calibrated = $this->activeProfile->profile($companyId)['ink_cost_per_ml'] ?? null;

        if ($calibrated !== null && (float) $calibrated > 0) {
            return (float) $calibrated;
        }

        $ml = (float) ($profile->estimated_ml ?? 0);
        $cost = (float) $profile->cartridge_cost;

        if ($ml > 0 && $cost > 0) {
            return round($cost / $ml, 4);
        }

        return null;
    }

    public function yieldPerPage(PrintInkProfile $profile): ?float
    {
        $pages = (int) ($profile->estimated_yield_pages ?? 0);

        if ($pages <= 0) {
            return null;
        }

        return round($this->currentCartridgeCost($profile) / $pages, 4);
    }

    public function yieldPerSquareMeter(PrintInkProfile $profile): ?float
    {
        $sqm = (float) ($profile->estimated_yield_sq_m ?? 0);

        if ($sqm <= 0) {
            return null;
        }

        return round($this->currentCartridgeCost($profile) / $sqm, 4);
    }

    public function currentCartridgeCost(PrintInkProfile $profile): float
    {
        return (float) $profile->cartridge_cost;
    }
}
