<?php

namespace App\Services\Assets;

use App\Enums\FixedAssetStatus;
use App\Models\Assets\FixedAsset;
use App\Support\Platform\PlatformCacheService;
use Illuminate\Support\Collection;

class AssetFinanceDashboardService
{
    public function __construct(
        protected PlatformCacheService $cache,
        protected DepreciationCalculationService $calculator,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(int $companyId, ?int $branchId = null): array
    {
        $cacheKey = $branchId ? "{$companyId}:{$branchId}" : "{$companyId}:all";

        return $this->cache->remember('asset_finance_dashboard', $cacheKey, function () use ($companyId, $branchId) {
            $base = FixedAsset::query()
                ->where('company_id', $companyId)
                ->whereNull('archived_at')
                ->where('status', '!=', FixedAssetStatus::Disposed->value)
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->with('category:id,name,asset_type');

            $assets = (clone $base)->get();

            $monthlyTotal = 0.0;
            $nearEndOfLife = 0;

            foreach ($assets as $asset) {
                $profile = $this->calculator->financialProfile($asset);
                $monthlyTotal += $profile['monthly_depreciation'];

                if ($profile['remaining_months'] > 0 && $profile['remaining_months'] <= 6) {
                    $nearEndOfLife++;
                }
            }

            return [
                'total_asset_cost' => round($assets->sum('acquisition_cost'), 2),
                'net_book_value' => round($assets->sum(fn ($a) => $a->netBookValue()), 2),
                'accumulated_depreciation' => round($assets->sum('accumulated_depreciation'), 2),
                'monthly_depreciation' => round($monthlyTotal, 2),
                'annual_depreciation' => round($monthlyTotal * 12, 2),
                'fully_depreciated_assets' => (clone $base)->where('is_fully_depreciated', true)->count(),
                'near_end_of_life' => $nearEndOfLife,
                'by_category' => $this->byCategory($assets),
                'by_branch' => $this->byBranch($companyId, $branchId),
            ];
        }, config('platform.cache.asset_finance_dashboard', 60));
    }

    protected function byCategory(Collection $assets): Collection
    {
        return $assets->groupBy('asset_category_id')->map(fn ($group) => [
            'category' => $group->first()->category?->name,
            'count' => $group->count(),
            'cost' => round($group->sum('acquisition_cost'), 2),
            'nbv' => round($group->sum(fn ($a) => $a->netBookValue()), 2),
        ])->values();
    }

    protected function byBranch(int $companyId, ?int $branchId): Collection
    {
        if ($branchId) {
            return collect();
        }

        return FixedAsset::query()
            ->where('company_id', $companyId)
            ->whereNull('archived_at')
            ->where('status', '!=', FixedAssetStatus::Disposed->value)
            ->selectRaw('branch_id, COUNT(*) as count, SUM(acquisition_cost) as cost, SUM(accumulated_depreciation) as accumulated')
            ->groupBy('branch_id')
            ->get();
    }
}
