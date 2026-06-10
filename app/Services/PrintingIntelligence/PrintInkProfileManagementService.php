<?php

namespace App\Services\PrintingIntelligence;

use App\Models\PrintingIntelligence\PrintArtworkInkEstimate;
use App\Models\PrintingIntelligence\PrintInkProfile;

class PrintInkProfileManagementService
{
    public function __construct(
        protected InkCostProfileService $inkCostProfile,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(int $companyId, array $data): PrintInkProfile
    {
        return PrintInkProfile::query()->create([
            ...$data,
            'company_id' => $companyId,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(PrintInkProfile $profile, array $data): PrintInkProfile
    {
        $profile->update($data);

        return $profile->fresh(['inventoryItem']);
    }

    public function deactivate(PrintInkProfile $profile): PrintInkProfile
    {
        $profile->update(['active' => false]);

        return $profile->fresh(['inventoryItem']);
    }

    public function isUsedByEstimates(PrintInkProfile $profile): bool
    {
        return PrintArtworkInkEstimate::query()
            ->where('ink_profile_id', $profile->id)
            ->exists();
    }

    /**
     * @return array<string, mixed>
     */
    public function displayRow(PrintInkProfile $profile): array
    {
        return [
            'id' => $profile->id,
            'name' => $profile->name,
            'ink_type' => $profile->ink_type?->label() ?? (string) $profile->ink_type,
            'ink_type_value' => $profile->ink_type?->value ?? (string) $profile->ink_type,
            'inventory_item' => $profile->inventoryItem?->item_name,
            'inventory_item_id' => $profile->inventory_item_id,
            'cartridge_cost' => (float) $profile->cartridge_cost,
            'estimated_ml' => $profile->estimated_ml !== null ? (float) $profile->estimated_ml : null,
            'cost_per_ml' => $this->previewCostPerMl($profile),
            'cost_per_ml_override' => $profile->cost_per_ml !== null ? (float) $profile->cost_per_ml : null,
            'yield_per_page' => $this->inkCostProfile->yieldPerPage($profile),
            'yield_per_sq_m' => $this->inkCostProfile->yieldPerSquareMeter($profile),
            'estimated_yield_pages' => $profile->estimated_yield_pages,
            'estimated_yield_sq_m' => $profile->estimated_yield_sq_m !== null ? (float) $profile->estimated_yield_sq_m : null,
            'active' => (bool) $profile->active,
            'used_by_estimates' => $this->isUsedByEstimates($profile),
        ];
    }

    public function previewCostPerMl(PrintInkProfile $profile): ?float
    {
        return $this->inkCostProfile->costPerMl($profile);
    }
}
