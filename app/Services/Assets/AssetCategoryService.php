<?php

namespace App\Services\Assets;

use App\Models\Assets\AssetCategory;
use App\Support\ActivityLogger;
use Illuminate\Support\Facades\DB;

class AssetCategoryService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, int $companyId, int $userId): AssetCategory
    {
        $years = (int) ($data['useful_life_years'] ?? 5);

        $category = AssetCategory::query()->create([
            ...$data,
            'company_id' => $companyId,
            'useful_life_years' => $years,
            'useful_life_months' => max(1, $years * 12),
            'depreciation_method' => $data['depreciation_method'] ?? 'straight_line',
            'is_active' => true,
        ]);

        ActivityLogger::log('created', $category, $userId);

        return $category;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(AssetCategory $category, array $data, int $userId): AssetCategory
    {
        if (isset($data['useful_life_years'])) {
            $data['useful_life_months'] = max(1, (int) $data['useful_life_years'] * 12);
        }

        $category->update($data);

        ActivityLogger::log('updated', $category->fresh(), $userId);

        return $category->fresh();
    }

    public function archive(AssetCategory $category, int $userId): AssetCategory
    {
        return DB::transaction(function () use ($category, $userId) {
            $category->update([
                'is_active' => false,
                'archived_at' => now(),
            ]);

            ActivityLogger::log('archived', $category->fresh(), $userId);

            return $category->fresh();
        });
    }
}
