<?php

namespace App\Support\Assets;

use App\Enums\FixedAssetStatus;
use App\Models\Assets\AssetDepreciationEntry;
use App\Models\Assets\FixedAsset;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssetDepreciationService
{
    public static function runPeriod(FixedAsset $asset, string $periodDate): AssetDepreciationEntry
    {
        if ($asset->status === FixedAssetStatus::Disposed) {
            throw ValidationException::withMessages([
                'asset' => __('Disposed assets cannot be depreciated.'),
            ]);
        }

        $asset->load('category');

        $months = max(1, (int) ($asset->category?->useful_life_months ?? 60));
        $depreciable = max(0, (float) $asset->acquisition_cost - (float) $asset->residual_value);
        $monthly = round($depreciable / $months, 2);

        return DB::transaction(function () use ($asset, $periodDate, $monthly) {
            $newAccumulated = min(
                (float) $asset->acquisition_cost - (float) $asset->residual_value,
                round((float) $asset->accumulated_depreciation + $monthly, 2),
            );

            $nbv = max(0, (float) $asset->acquisition_cost - $newAccumulated);

            $entry = AssetDepreciationEntry::query()->create([
                'fixed_asset_id' => $asset->id,
                'period_date' => $periodDate,
                'depreciation_amount' => $monthly,
                'accumulated_after' => $newAccumulated,
                'net_book_value_after' => $nbv,
            ]);

            $asset->update(['accumulated_depreciation' => $newAccumulated]);

            return $entry;
        });
    }

    /**
     * @return array{asset_value: float, accumulated: float, net_book_value: float}
     */
    public static function companyTotals(int $companyId): array
    {
        $assets = FixedAsset::query()
            ->where('company_id', $companyId)
            ->where('status', '!=', FixedAssetStatus::Disposed->value)
            ->get();

        $cost = $assets->sum('acquisition_cost');
        $accumulated = $assets->sum('accumulated_depreciation');

        return [
            'asset_value' => round((float) $cost, 2),
            'accumulated' => round((float) $accumulated, 2),
            'net_book_value' => round((float) $cost - (float) $accumulated, 2),
        ];
    }
}
