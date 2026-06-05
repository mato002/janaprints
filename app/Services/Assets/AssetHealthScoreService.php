<?php

namespace App\Services\Assets;

use App\Enums\AssetHealthBand;
use App\Enums\AssetPhysicalCondition;
use App\Enums\AssetWarrantyStatus;
use App\Enums\MaintenanceType;
use App\Models\Assets\FixedAsset;

class AssetHealthScoreService
{
    public function __construct(
        protected MaintenanceDowntimeService $downtime,
    ) {}

    /**
     * @return array{score: int, band: AssetHealthBand, factors: list<array{label: string, impact: int, detail: string}>}
     */
    public function score(FixedAsset $asset): array
    {
        $factors = [];
        $score = 100;

        $downtimeMinutes = (int) $asset->downtimeRecords()
            ->where('start_time', '>=', now()->subYear())
            ->sum('duration_minutes');

        $downtimePenalty = min(30, (int) floor($downtimeMinutes / 120));
        if ($downtimePenalty > 0) {
            $score -= $downtimePenalty;
            $factors[] = [
                'label' => __('Downtime'),
                'impact' => -$downtimePenalty,
                'detail' => __(':hours hours in last 12 months', ['hours' => round($downtimeMinutes / 60, 1)]),
            ];
        }

        $woCount = $asset->maintenanceWorkOrders()
            ->where('opened_at', '>=', now()->subYear())
            ->count();
        $maintPenalty = min(20, $woCount * 2);
        if ($maintPenalty > 0) {
            $score -= $maintPenalty;
            $factors[] = [
                'label' => __('Maintenance frequency'),
                'impact' => -$maintPenalty,
                'detail' => __(':count work orders in last 12 months', ['count' => $woCount]),
            ];
        }

        $failureCount = $asset->maintenanceWorkOrders()
            ->whereIn('maintenance_type', [MaintenanceType::Corrective->value, MaintenanceType::Emergency->value])
            ->where('opened_at', '>=', now()->subYear())
            ->count();
        $failurePenalty = min(15, $failureCount * 3);
        if ($failurePenalty > 0) {
            $score -= $failurePenalty;
            $factors[] = [
                'label' => __('Failures'),
                'impact' => -$failurePenalty,
                'detail' => __(':count corrective/emergency events', ['count' => $failureCount]),
            ];
        }

        $conditionImpact = match ($asset->current_condition) {
            AssetPhysicalCondition::Excellent => 0,
            AssetPhysicalCondition::Good => -5,
            AssetPhysicalCondition::Fair => -12,
            AssetPhysicalCondition::Poor => -20,
            AssetPhysicalCondition::Damaged => -35,
            default => -8,
        };
        if ($conditionImpact < 0) {
            $score += $conditionImpact;
            $factors[] = [
                'label' => __('Condition'),
                'impact' => $conditionImpact,
                'detail' => $asset->current_condition?->label() ?? __('Unknown'),
            ];
        }

        $agePercent = $this->agePercentOfLife($asset);
        $agePenalty = min(20, (int) floor($agePercent / 5));
        if ($agePenalty > 0) {
            $score -= $agePenalty;
            $factors[] = [
                'label' => __('Age'),
                'impact' => -$agePenalty,
                'detail' => __(':pct% of useful life elapsed', ['pct' => round($agePercent, 0)]),
            ];
        }

        $activeWarranty = $asset->warranties()->where('status', AssetWarrantyStatus::Active)->exists();
        if ($activeWarranty) {
            $score = min(100, $score + 5);
            $factors[] = [
                'label' => __('Warranty'),
                'impact' => 5,
                'detail' => __('Active warranty coverage'),
            ];
        } elseif ($asset->warranties()->exists()) {
            $score -= 5;
            $factors[] = [
                'label' => __('Warranty'),
                'impact' => -5,
                'detail' => __('No active warranty'),
            ];
        }

        $score = max(0, min(100, $score));

        return [
            'score' => $score,
            'band' => AssetHealthBand::fromScore($score),
            'factors' => $factors,
        ];
    }

    public function agePercentOfLife(FixedAsset $asset): float
    {
        $years = (int) ($asset->useful_life_years ?? $asset->category?->useful_life_years ?? 0);
        if ($years <= 0) {
            return 0;
        }

        $start = $asset->capitalization_date ?? $asset->acquisition_date;
        if (! $start) {
            return 0;
        }

        $ageYears = $start->diffInDays(now()) / 365.25;

        return min(100, ($ageYears / $years) * 100);
    }
}
