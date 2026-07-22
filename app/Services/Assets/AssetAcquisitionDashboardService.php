<?php

namespace App\Services\Assets;

use App\Enums\AssetAcquisitionSource;
use App\Enums\AssetWarrantyStatus;
use App\Enums\CapitalizationCandidateStatus;
use App\Models\Assets\AssetCapitalizationCandidate;
use App\Models\Assets\AssetWarranty;
use App\Models\Assets\FixedAsset;
use App\Support\Assets\AssetSchema;
use App\Support\Platform\PlatformCacheService;

class AssetAcquisitionDashboardService
{
    public function __construct(
        protected PlatformCacheService $cache,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(int $companyId, ?int $branchId = null): array
    {
        $cacheKey = $branchId ? "{$companyId}:{$branchId}" : "{$companyId}:all";

        $cached = $this->cache->remember('asset_acquisition_dashboard', $cacheKey, function () use ($companyId, $branchId) {
            $now = now();
            $monthStart = $now->copy()->startOfMonth();
            $yearStart = $now->copy()->startOfYear();

            $pendingCapitalization = AssetSchema::count('asset_capitalization_candidates', fn () => AssetCapitalizationCandidate::query()
                ->where('company_id', $companyId)
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->whereIn('status', [
                    CapitalizationCandidateStatus::Pending->value,
                    CapitalizationCandidateStatus::Ready->value,
                ])
                ->count());

            $capitalizedMonth = 0.0;
            $capitalizedYear = 0.0;
            $byCategory = [];
            $byVendor = [];
            $byBranch = [];

            if (AssetSchema::supportsProcurementAssets()) {
                $procurementAssets = FixedAsset::query()
                    ->where('company_id', $companyId)
                    ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                    ->where('acquisition_source', AssetAcquisitionSource::Procurement);

                $capitalizedMonth = (clone $procurementAssets)
                    ->where('capitalization_date', '>=', $monthStart)
                    ->sum('acquisition_cost');

                $capitalizedYear = (clone $procurementAssets)
                    ->where('capitalization_date', '>=', $yearStart)
                    ->sum('acquisition_cost');

                $byCategory = FixedAsset::query()
                    ->where('company_id', $companyId)
                    ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                    ->where('acquisition_source', AssetAcquisitionSource::Procurement)
                    ->with('category')
                    ->get()
                    ->groupBy(fn ($a) => $a->category?->name ?? __('Uncategorized'))
                    ->map(fn ($group, $name) => [
                        'category' => $name,
                        'total' => round($group->sum('acquisition_cost'), 2),
                        'count' => $group->count(),
                    ])
                    ->values()
                    ->all();

                $byVendor = FixedAsset::query()
                    ->where('company_id', $companyId)
                    ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                    ->where('acquisition_source', AssetAcquisitionSource::Procurement)
                    ->with('vendor')
                    ->get()
                    ->groupBy(fn ($a) => $a->vendor?->vendor_name ?? __('Unknown'))
                    ->map(fn ($group, $name) => [
                        'vendor' => $name,
                        'total' => round($group->sum('acquisition_cost'), 2),
                        'count' => $group->count(),
                    ])
                    ->sortByDesc('total')
                    ->values()
                    ->take(10)
                    ->all();

                $byBranch = FixedAsset::query()
                    ->where('company_id', $companyId)
                    ->where('acquisition_source', AssetAcquisitionSource::Procurement)
                    ->with('branch')
                    ->get()
                    ->groupBy(fn ($a) => $a->branch?->name ?? __('Unassigned'))
                    ->map(fn ($group, $name) => [
                        'branch' => $name,
                        'total' => round($group->sum('acquisition_cost'), 2),
                        'count' => $group->count(),
                    ])
                    ->values()
                    ->all();
            }

            $warrantyExpiring = AssetSchema::count('asset_warranties', fn () => AssetWarranty::query()
                ->where('company_id', $companyId)
                ->where('status', AssetWarrantyStatus::Active)
                ->whereBetween('warranty_end', [$now->toDateString(), $now->copy()->addDays(90)->toDateString()])
                ->count());

            return [
                'pending_capitalization' => $pendingCapitalization,
                'capitalized_this_month' => round((float) $capitalizedMonth, 2),
                'capitalized_this_year' => round((float) $capitalizedYear, 2),
                'by_category' => $byCategory,
                'by_vendor' => $byVendor,
                'by_branch' => $byBranch,
                'warranty_expiring_soon' => $warrantyExpiring,
            ];
        }, config('platform.cache.asset_acquisition_dashboard', 120));

        return array_merge($cached, [
            'recent_acquisitions' => $this->recentAcquisitions($companyId, $branchId),
        ]);
    }

    /**
     * @return \Illuminate\Support\Collection<int, FixedAsset>
     */
    protected function recentAcquisitions(int $companyId, ?int $branchId): \Illuminate\Support\Collection
    {
        if (! AssetSchema::supportsProcurementAssets()) {
            return collect();
        }

        return FixedAsset::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('acquisition_source', AssetAcquisitionSource::Procurement)
            ->with(['vendor', 'category'])
            ->latest('capitalization_date')
            ->limit(8)
            ->get();
    }
}
