<?php

namespace App\Services\Assets;

use App\Enums\FixedAssetStatus;
use App\Models\Assets\AssetAssignmentHistory;
use App\Models\Assets\AssetCategory;
use App\Models\Assets\FixedAsset;
use App\Models\Branch;
use App\Support\Platform\PlatformCacheService;
use Illuminate\Support\Collection;

class AssetDashboardService
{
    public function __construct(
        protected PlatformCacheService $cache,
        protected MaintenanceDashboardService $maintenance,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(int $companyId, ?int $branchId = null): array
    {
        $cacheKey = $branchId ? "{$companyId}:{$branchId}" : "{$companyId}:all";

        return $this->cache->remember('assets_dashboard', $cacheKey, function () use ($companyId, $branchId) {
            $base = FixedAsset::query()
                ->where('company_id', $companyId)
                ->whereNull('archived_at')
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));

            $totals = (clone $base)
                ->selectRaw('COUNT(*) as total_assets')
                ->selectRaw('COALESCE(SUM(acquisition_cost), 0) as total_value')
                ->selectRaw('COALESCE(SUM(accumulated_depreciation), 0) as accumulated')
                ->first();

            $maintStats = $this->maintenance->build($companyId, $branchId);

            return [
                'total_assets' => (int) ($totals->total_assets ?? 0),
                'total_asset_value' => round((float) ($totals->total_value ?? 0), 2),
                'total_book_value' => round((float) ($totals->total_value ?? 0) - (float) ($totals->accumulated ?? 0), 2),
                'by_category' => $this->byCategory($companyId, $branchId),
                'by_branch' => $this->byBranch($companyId, $branchId),
                'by_status' => $this->byStatus($companyId, $branchId),
                'recently_added' => $this->recentlyAdded($companyId, $branchId),
                'recently_assigned' => $this->recentlyAssigned($companyId, $branchId),
                'maintenance' => [
                    'open_work_orders' => $maintStats['open_work_orders'] ?? 0,
                    'critical_failures' => $maintStats['critical_failures'] ?? 0,
                    'downtime_hours' => $maintStats['downtime_hours'] ?? 0,
                ],
            ];
        }, config('platform.cache.dashboard', 60));
    }

    /**
     * @return Collection<int, object>
     */
    protected function byCategory(int $companyId, ?int $branchId): Collection
    {
        return AssetCategory::query()
            ->where('company_id', $companyId)
            ->whereNull('archived_at')
            ->withCount(['assets' => fn ($q) => $q
                ->whereNull('archived_at')
                ->when($branchId, fn ($b) => $b->where('branch_id', $branchId)),
            ])
            ->orderByDesc('assets_count')
            ->limit(12)
            ->get(['id', 'name']);
    }

    /**
     * @return Collection<int, object>
     */
    protected function byBranch(int $companyId, ?int $branchId): Collection
    {
        if ($branchId) {
            return collect();
        }

        return Branch::query()
            ->where('company_id', $companyId)
            ->withCount(['fixedAssets' => fn ($q) => $q->whereNull('archived_at')])
            ->orderByDesc('fixed_assets_count')
            ->limit(12)
            ->get(['id', 'name']);
    }

    /**
     * @return array<string, int>
     */
    protected function byStatus(int $companyId, ?int $branchId): array
    {
        $counts = [];

        foreach (FixedAssetStatus::cases() as $status) {
            $counts[$status->value] = FixedAsset::query()
                ->where('company_id', $companyId)
                ->whereNull('archived_at')
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->where('status', $status)
                ->count();
        }

        return $counts;
    }

    /**
     * @return Collection<int, FixedAsset>
     */
    protected function recentlyAdded(int $companyId, ?int $branchId): Collection
    {
        return FixedAsset::query()
            ->where('company_id', $companyId)
            ->whereNull('archived_at')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->with(['category:id,name'])
            ->latest()
            ->limit(8)
            ->get(['id', 'asset_number', 'asset_name', 'asset_category_id', 'created_at']);
    }

    /**
     * @return Collection<int, AssetAssignmentHistory>
     */
    protected function recentlyAssigned(int $companyId, ?int $branchId): Collection
    {
        return AssetAssignmentHistory::query()
            ->whereHas('asset', fn ($q) => $q
                ->where('company_id', $companyId)
                ->whereNull('archived_at')
                ->when($branchId, fn ($b) => $b->where('branch_id', $branchId)),
            )
            ->with([
                'asset:id,asset_number,asset_name',
                'assignedUser:id,name',
                'assignedBranch:id,name',
                'assigner:id,name',
            ])
            ->latest('assigned_at')
            ->limit(8)
            ->get();
    }
}
