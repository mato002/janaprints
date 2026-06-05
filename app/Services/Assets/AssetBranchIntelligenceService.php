<?php

namespace App\Services\Assets;

use App\Enums\AssetType;
use App\Enums\FixedAssetStatus;
use App\Models\Assets\FixedAsset;
use App\Models\Branch;
use App\Support\Platform\PlatformCacheService;

class AssetBranchIntelligenceService
{
    public function __construct(
        protected AssetReplacementService $replacement,
        protected MaintenanceDashboardService $maintenance,
        protected PlatformCacheService $cache,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(int $companyId, int $branchId): array
    {
        return $this->cache->remember('asset_branch_intelligence', "{$companyId}:{$branchId}", function () use ($companyId, $branchId) {
            $branch = Branch::query()->findOrFail($branchId);
            $assets = FixedAsset::query()
                ->where('company_id', $companyId)
                ->where('branch_id', $branchId)
                ->whereNull('archived_at')
                ->where('status', '!=', FixedAssetStatus::Disposed->value)
                ->with('category')
                ->get();

            $byType = fn (AssetType $type) => $assets->filter(fn ($a) => $a->category?->asset_type === $type)->count();

            $maint = $this->maintenance->build($companyId, $branchId);

            return [
                'branch' => $branch,
                'asset_count' => $assets->count(),
                'asset_value' => round($assets->sum('acquisition_cost'), 2),
                'net_book_value' => round($assets->sum(fn ($a) => $a->netBookValue()), 2),
                'machines' => $byType(AssetType::Machine),
                'vehicles' => $byType(AssetType::Vehicle),
                'computers' => $byType(AssetType::Computer),
                'maintenance' => $maint,
                'unassigned' => $assets->filter(fn ($a) => ! $a->assigned_to_user_id && ! $a->assigned_to_employee_id)->count(),
                'replacement_candidates' => $this->replacement->candidates($companyId, $branchId, 5),
            ];
        }, config('platform.cache.asset_branch_intelligence', 120));
    }
}
