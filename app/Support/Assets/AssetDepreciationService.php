<?php

namespace App\Support\Assets;

use App\Enums\DepreciationPostingStatus;
use App\Enums\FixedAssetStatus;
use App\Models\Assets\AssetDepreciationEntry;
use App\Models\Assets\FixedAsset;
use App\Services\Assets\AssetFinanceTimelineService;
use App\Services\Assets\DepreciationCalculationService;
use App\Support\Accounting\AssetAccountingPostingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssetDepreciationService
{
    public static function runPeriod(FixedAsset $asset, string $periodDate, int $userId): AssetDepreciationEntry
    {
        if ($asset->status === FixedAssetStatus::Disposed) {
            throw ValidationException::withMessages([
                'asset' => __('Disposed assets cannot be depreciated.'),
            ]);
        }

        $calculator = app(DepreciationCalculationService::class);

        if ($calculator->hasEntryForPeriod($asset, $periodDate)) {
            throw ValidationException::withMessages([
                'period_date' => __('Depreciation already recorded for this period.'),
            ]);
        }

        $calc = $calculator->calculateForPeriod($asset, $periodDate);

        return DB::transaction(function () use ($asset, $periodDate, $calc, $userId, $calculator) {
            $entry = AssetDepreciationEntry::query()->create([
                'fixed_asset_id' => $asset->id,
                'period_date' => $calculator->normalizePeriod($periodDate),
                'depreciation_amount' => $calc['depreciation_amount'],
                'accumulated_after' => $calc['accumulated_after'],
                'net_book_value_after' => $calc['net_book_value_after'],
                'posting_status' => DepreciationPostingStatus::Draft,
            ]);

            $asset->update([
                'accumulated_depreciation' => $calc['accumulated_after'],
                'net_book_value' => $calc['net_book_value_after'],
                'last_depreciation_date' => $calculator->normalizePeriod($periodDate),
                'is_fully_depreciated' => $calc['is_fully_depreciated'],
            ]);

            app(AssetAccountingPostingService::class)->postDepreciation($entry, $asset->fresh(), $userId);

            $entry->update([
                'posting_status' => DepreciationPostingStatus::Posted,
                'posted_at' => now(),
                'is_locked' => true,
            ]);

            app(AssetFinanceTimelineService::class)->record(
                $asset,
                'depreciated',
                __('Depreciation posted'),
                null,
                $entry,
                $userId,
                ['amount' => $calc['depreciation_amount']],
            );

            return $entry->fresh();
        });
    }

    public static function runPeriodForCompany(int $companyId, string $periodDate, int $userId): int
    {
        $count = 0;

        FixedAsset::query()
            ->where('company_id', $companyId)
            ->where('status', FixedAssetStatus::Active)
            ->where('is_fully_depreciated', false)
            ->each(function (FixedAsset $asset) use ($periodDate, $userId, &$count) {
                if (! app(DepreciationCalculationService::class)->hasEntryForPeriod($asset, $periodDate)) {
                    self::runPeriod($asset, $periodDate, $userId);
                    $count++;
                }
            });

        return $count;
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
