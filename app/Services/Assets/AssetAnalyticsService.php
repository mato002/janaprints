<?php

namespace App\Services\Assets;

use App\Enums\AssetWarrantyStatus;
use App\Enums\FixedAssetStatus;
use App\Models\Assets\AssetDowntimeRecord;
use App\Models\Assets\AssetWarranty;
use App\Models\Assets\FixedAsset;
use App\Models\Assets\MaintenanceWorkOrder;
use App\Support\Assets\AssetSchema;
use App\Support\Platform\PlatformCacheService;

class AssetAnalyticsService
{
    public function __construct(
        protected AssetReplacementService $replacement,
        protected PlatformCacheService $cache,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(int $companyId, ?int $branchId = null): array
    {
        $key = $branchId ? "{$companyId}:{$branchId}" : "{$companyId}:all";

        return $this->cache->remember('asset_analytics', $key, function () use ($companyId, $branchId) {
            $assets = FixedAsset::query()
                ->where('company_id', $companyId)
                ->whereNull('archived_at')
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->with(['category', 'branch'])
                ->get();

            return [
                'by_category' => $this->countValueGroup($assets, fn ($a) => $a->category?->name ?? __('Uncategorized')),
                'by_branch' => $this->countValueGroup($assets, fn ($a) => $a->branch?->name ?? __('Unassigned')),
                'age_distribution' => $this->ageBuckets($assets),
                'maintenance_trend' => $this->monthlyCount(MaintenanceWorkOrder::class, $companyId, $branchId, 'opened_at'),
                'downtime_trend' => $this->monthlySum(AssetDowntimeRecord::class, $companyId, $branchId, 'duration_minutes', 'start_time'),
                'warranty_expiry_trend' => $this->warrantyExpiryMonths($companyId),
                'depreciation_trend' => $this->depreciationByMonth($assets),
                'replacement_trend' => ['candidates' => $this->replacement->candidates($companyId, $branchId)->count()],
            ];
        }, config('platform.cache.asset_analytics', 180));
    }

    /**
     * @param  \Illuminate\Support\Collection<int, FixedAsset>  $assets
     * @return list<array<string, mixed>>
     */
    protected function countValueGroup($assets, callable $grouper): array
    {
        return $assets->groupBy($grouper)
            ->map(fn ($g, $label) => [
                'label' => $label,
                'count' => $g->count(),
                'value' => round($g->sum('acquisition_cost'), 2),
            ])
            ->sortByDesc('value')
            ->values()
            ->all();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, FixedAsset>  $assets
     * @return list<array{label: string, count: int}>
     */
    protected function ageBuckets($assets): array
    {
        $buckets = ['0-1 yr' => 0, '1-3 yr' => 0, '3-5 yr' => 0, '5+ yr' => 0];

        foreach ($assets as $asset) {
            $years = ($asset->capitalization_date ?? $asset->acquisition_date)?->diffInYears(now()) ?? 0;
            $key = match (true) {
                $years < 1 => '0-1 yr',
                $years < 3 => '1-3 yr',
                $years < 5 => '3-5 yr',
                default => '5+ yr',
            };
            $buckets[$key]++;
        }

        return collect($buckets)->map(fn ($count, $label) => ['label' => $label, 'count' => $count])->values()->all();
    }

    /** @return list<array{month: string, value: float|int}> */
    protected function monthlyCount(string $model, int $companyId, ?int $branchId, string $dateCol): array
    {
        $rows = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $count = $model::query()
                ->where('company_id', $companyId)
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->whereYear($dateCol, $month->year)
                ->whereMonth($dateCol, $month->month)
                ->count();
            $rows[] = ['month' => $month->format('Y-m'), 'value' => $count];
        }

        return $rows;
    }

    /** @return list<array{month: string, value: float|int}> */
    protected function monthlySum(string $model, int $companyId, ?int $branchId, string $sumCol, string $dateCol): array
    {
        $rows = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $sum = (int) $model::query()
                ->where('company_id', $companyId)
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->whereYear($dateCol, $month->year)
                ->whereMonth($dateCol, $month->month)
                ->sum($sumCol);
            $rows[] = ['month' => $month->format('Y-m'), 'value' => round($sum / 60, 1)];
        }

        return $rows;
    }

    /** @return list<array{month: string, value: int}> */
    protected function warrantyExpiryMonths(int $companyId): array
    {
        $rows = [];
        for ($i = 0; $i < 6; $i++) {
            $start = now()->addMonths($i)->startOfMonth();
            $end = now()->addMonths($i)->endOfMonth();
            $count = AssetSchema::count('asset_warranties', fn () => AssetWarranty::query()
                ->where('company_id', $companyId)
                ->where('status', AssetWarrantyStatus::Active)
                ->whereBetween('warranty_end', [$start, $end])
                ->count());
            $rows[] = ['month' => $start->format('Y-m'), 'value' => $count];
        }

        return $rows;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, FixedAsset>  $assets
     * @return list<array{month: string, value: float}>
     */
    protected function depreciationByMonth($assets): array
    {
        $monthly = round($assets->sum(fn ($a) => max(0, (float) $a->acquisition_cost - (float) $a->residual_value) / max(1, (int) ($a->useful_life_years ?? 5) * 12)), 2);

        return [['month' => now()->format('Y-m'), 'value' => $monthly]];
    }
}
