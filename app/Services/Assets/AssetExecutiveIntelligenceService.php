<?php

namespace App\Services\Assets;

use App\Enums\AssetHealthBand;
use App\Enums\AssetWarrantyStatus;
use App\Enums\FixedAssetStatus;
use App\Enums\MaintenanceWorkOrderStatus;
use App\Models\Assets\AssetWarranty;
use App\Models\Assets\FixedAsset;
use App\Support\Assets\AssetSchema;
use App\Support\Platform\PlatformCacheService;

class AssetExecutiveIntelligenceService
{
    public function __construct(
        protected DepreciationCalculationService $depreciation,
        protected AssetReplacementService $replacement,
        protected AssetHealthScoreService $health,
        protected PlatformCacheService $cache,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(int $companyId, ?int $branchId = null): array
    {
        $key = $branchId ? "{$companyId}:{$branchId}" : "{$companyId}:all";

        return $this->cache->remember('asset_executive_dashboard', $key, function () use ($companyId, $branchId) {
            $base = FixedAsset::query()
                ->where('company_id', $companyId)
                ->whereNull('archived_at')
                ->where('status', '!=', FixedAssetStatus::Disposed->value)
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));

            $totals = (clone $base)->selectRaw('COUNT(*) as cnt')
                ->selectRaw('COALESCE(SUM(acquisition_cost),0) as cost')
                ->selectRaw('COALESCE(SUM(accumulated_depreciation),0) as accum')
                ->first();

            $monthlyDep = 0;
            (clone $base)->with('category')->each(function (FixedAsset $asset) use (&$monthlyDep) {
                $monthlyDep += $this->depreciation->financialProfile($asset)['monthly_depreciation'];
            });

            $nearEol = (clone $base)->get()->filter(function (FixedAsset $asset) {
                return ($this->depreciation->financialProfile($asset)['remaining_months'] ?? 999) <= 12;
            })->count();

            $criticalHealth = (clone $base)->get()->filter(fn (FixedAsset $a) => $this->health->score($a)['band'] === AssetHealthBand::Critical)->count();

            return [
                'total_asset_value' => round((float) $totals->cost, 2),
                'net_book_value' => round((float) $totals->cost - (float) $totals->accum, 2),
                'depreciation_this_month' => round($monthlyDep, 2),
                'assets_near_end_of_life' => $nearEol,
                'assets_under_maintenance' => AssetSchema::count('maintenance_work_orders', fn () => \App\Models\Assets\MaintenanceWorkOrder::query()
                    ->where('company_id', $companyId)
                    ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                    ->whereIn('status', [MaintenanceWorkOrderStatus::Open, MaintenanceWorkOrderStatus::InProgress, MaintenanceWorkOrderStatus::Assigned])
                    ->distinct('fixed_asset_id')
                    ->count('fixed_asset_id')),
                'critical_assets' => $criticalHealth,
                'warranty_expiring' => AssetSchema::count('asset_warranties', fn () => AssetWarranty::query()
                    ->where('company_id', $companyId)
                    ->where('status', AssetWarrantyStatus::Active)
                    ->whereBetween('warranty_end', [now()->toDateString(), now()->addDays(90)->toDateString()])
                    ->count()),
                'replacement_candidates' => $this->replacement->candidates($companyId, $branchId)->count(),
                'by_branch' => $this->groupByBranch($companyId),
                'by_category' => $this->groupByCategory($companyId, $branchId),
                'replacement_list' => $this->replacement->candidates($companyId, $branchId, 8),
            ];
        }, config('platform.cache.asset_executive_dashboard', 120));
    }

    /** @return list<array<string, mixed>> */
    protected function groupByBranch(int $companyId): array
    {
        return FixedAsset::query()
            ->where('company_id', $companyId)
            ->whereNull('archived_at')
            ->with('branch:id,name')
            ->get()
            ->groupBy(fn ($a) => $a->branch?->name ?? __('Unassigned'))
            ->map(fn ($g, $name) => ['branch' => $name, 'count' => $g->count(), 'value' => round($g->sum('acquisition_cost'), 2)])
            ->values()
            ->all();
    }

    /** @return list<array<string, mixed>> */
    protected function groupByCategory(int $companyId, ?int $branchId): array
    {
        return FixedAsset::query()
            ->where('company_id', $companyId)
            ->whereNull('archived_at')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->with('category:id,name')
            ->get()
            ->groupBy(fn ($a) => $a->category?->name ?? __('Uncategorized'))
            ->map(fn ($g, $name) => ['category' => $name, 'count' => $g->count(), 'nbv' => round($g->sum(fn ($a) => $a->netBookValue()), 2)])
            ->values()
            ->all();
    }
}
