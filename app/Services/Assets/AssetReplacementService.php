<?php

namespace App\Services\Assets;

use App\Enums\FixedAssetStatus;
use App\Enums\MaintenanceType;
use App\Models\Assets\FixedAsset;
use Illuminate\Support\Collection;

class AssetReplacementService
{
    public function __construct(
        protected AssetHealthScoreService $health,
        protected DepreciationCalculationService $depreciation,
    ) {}

    /**
     * @return Collection<int, array{asset: FixedAsset, reasons: list<string>, priority: string}>
     */
    public function candidates(int $companyId, ?int $branchId = null, int $limit = 25): Collection
    {
        return FixedAsset::query()
            ->where('company_id', $companyId)
            ->whereNull('archived_at')
            ->where('status', '!=', FixedAssetStatus::Disposed->value)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->with(['category', 'branch'])
            ->get()
            ->map(function (FixedAsset $asset) {
                $reasons = [];
                $priority = 'low';

                if ($asset->is_fully_depreciated) {
                    $reasons[] = __('Fully depreciated');
                    $priority = 'medium';
                }

                $profile = $this->depreciation->financialProfile($asset);
                if (($profile['remaining_months'] ?? 0) <= 12 && ($profile['remaining_months'] ?? 0) > 0) {
                    $reasons[] = __('Near end of useful life');
                    $priority = 'medium';
                }

                $health = $this->health->score($asset);
                if ($health['band']->value === 'critical') {
                    $reasons[] = __('Critical health score');
                    $priority = 'high';
                } elseif ($health['band']->value === 'poor') {
                    $reasons[] = __('Poor health score');
                    if ($priority === 'low') {
                        $priority = 'medium';
                    }
                }

                $failureCount = $asset->maintenanceWorkOrders()
                    ->whereIn('maintenance_type', [MaintenanceType::Corrective->value, MaintenanceType::Emergency->value])
                    ->where('opened_at', '>=', now()->subYear())
                    ->count();
                if ($failureCount >= 3) {
                    $reasons[] = __('High maintenance burden');
                    $priority = 'high';
                }

                if ($reasons === []) {
                    return null;
                }

                return [
                    'asset' => $asset,
                    'reasons' => $reasons,
                    'priority' => $priority,
                    'health_score' => $health['score'],
                ];
            })
            ->filter()
            ->sortByDesc(fn ($row) => match ($row['priority']) {
                'high' => 3,
                'medium' => 2,
                default => 1,
            })
            ->take($limit)
            ->values();
    }
}
