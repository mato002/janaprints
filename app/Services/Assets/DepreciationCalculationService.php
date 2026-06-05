<?php

namespace App\Services\Assets;

use App\Enums\DepreciationMethod;
use App\Enums\FixedAssetStatus;
use App\Models\Assets\AssetDepreciationEntry;
use App\Models\Assets\FixedAsset;
use Illuminate\Validation\ValidationException;

class DepreciationCalculationService
{
    /**
     * @return array{
     *     depreciation_amount: float,
     *     accumulated_after: float,
     *     net_book_value_after: float,
     *     monthly_depreciation: float,
     *     annual_depreciation: float,
     *     remaining_months: int,
     *     is_fully_depreciated: bool,
     *     method: DepreciationMethod,
     * }
     */
    public function calculateForPeriod(FixedAsset $asset, string $periodDate): array
    {
        $asset->loadMissing('category');

        if ($asset->status === FixedAssetStatus::Disposed || $asset->is_fully_depreciated) {
            return $this->zeroResult($asset);
        }

        $method = $this->resolveMethod($asset);

        if (! $method->isImplemented()) {
            throw ValidationException::withMessages([
                'depreciation_method' => __('Depreciation method :method is not yet implemented. Straight line is used.', [
                    'method' => $method->label(),
                ]),
            ]);
        }

        $depreciable = $this->depreciableAmount($asset);
        $months = $this->usefulLifeMonths($asset);

        if ($depreciable <= 0 || $months <= 0) {
            return $this->zeroResult($asset, $method);
        }

        $monthly = round($depreciable / $months, 2);
        $annual = round($monthly * 12, 2);
        $currentAccumulated = (float) $asset->accumulated_depreciation;
        $remainingDepreciable = max(0, $depreciable - $currentAccumulated);
        $amount = min($monthly, $remainingDepreciable);
        $newAccumulated = round($currentAccumulated + $amount, 2);
        $nbv = max((float) $asset->residual_value, round((float) $asset->acquisition_cost - $newAccumulated, 2));
        $isFullyDepreciated = $newAccumulated >= $depreciable || $nbv <= (float) $asset->residual_value;

        return [
            'depreciation_amount' => $amount,
            'accumulated_after' => $newAccumulated,
            'net_book_value_after' => $nbv,
            'monthly_depreciation' => $monthly,
            'annual_depreciation' => $annual,
            'remaining_months' => max(0, (int) ceil($remainingDepreciable / max($monthly, 0.01))),
            'is_fully_depreciated' => $isFullyDepreciated,
            'method' => $method,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function financialProfile(FixedAsset $asset): array
    {
        $asset->loadMissing('category');
        $projection = $this->calculateForPeriod($asset, now()->toDateString());

        return [
            'acquisition_cost' => (float) $asset->acquisition_cost,
            'capitalization_date' => $asset->capitalization_date ?? $asset->acquisition_date,
            'residual_value' => (float) $asset->residual_value,
            'useful_life_years' => $this->usefulLifeYears($asset),
            'useful_life_months' => $this->usefulLifeMonths($asset),
            'depreciation_method' => $this->resolveMethod($asset),
            'depreciation_start_date' => $asset->depreciation_start_date ?? $asset->capitalization_date ?? $asset->acquisition_date,
            'accumulated_depreciation' => (float) $asset->accumulated_depreciation,
            'net_book_value' => $asset->netBookValue(),
            'last_depreciation_date' => $asset->last_depreciation_date,
            'is_fully_depreciated' => (bool) $asset->is_fully_depreciated,
            'monthly_depreciation' => $projection['monthly_depreciation'],
            'annual_depreciation' => $projection['annual_depreciation'],
            'remaining_months' => $projection['remaining_months'],
            'depreciable_amount' => $this->depreciableAmount($asset),
        ];
    }

    public function hasEntryForPeriod(FixedAsset $asset, string $periodDate): bool
    {
        $period = $this->normalizePeriod($periodDate);

        return AssetDepreciationEntry::query()
            ->where('fixed_asset_id', $asset->id)
            ->whereDate('period_date', $period)
            ->exists();
    }

    public function normalizePeriod(string $date): string
    {
        return date('Y-m-01', strtotime($date));
    }

    public function resolveMethod(FixedAsset $asset): DepreciationMethod
    {
        if ($asset->depreciation_method instanceof DepreciationMethod) {
            return $asset->depreciation_method;
        }

        $value = $asset->depreciation_method
            ?? $asset->category?->depreciation_method
            ?? DepreciationMethod::StraightLine->value;

        return DepreciationMethod::tryFrom((string) $value) ?? DepreciationMethod::StraightLine;
    }

    public function usefulLifeMonths(FixedAsset $asset): int
    {
        if ($asset->useful_life_years) {
            return max(1, (int) $asset->useful_life_years) * 12;
        }

        if ($asset->category) {
            return $asset->category->usefulLifeMonths();
        }

        return 60;
    }

    public function usefulLifeYears(FixedAsset $asset): int
    {
        if ($asset->useful_life_years) {
            return max(1, (int) $asset->useful_life_years);
        }

        return max(1, (int) ceil($this->usefulLifeMonths($asset) / 12));
    }

    public function depreciableAmount(FixedAsset $asset): float
    {
        return max(0, (float) $asset->acquisition_cost - (float) $asset->residual_value);
    }

    public function syncBookValues(FixedAsset $asset): FixedAsset
    {
        $nbv = $asset->netBookValue();
        $asset->update([
            'net_book_value' => $nbv,
            'is_fully_depreciated' => $nbv <= (float) $asset->residual_value
                || (float) $asset->accumulated_depreciation >= $this->depreciableAmount($asset),
        ]);

        return $asset->fresh();
    }

    /**
     * @return array<string, mixed>
     */
    protected function zeroResult(FixedAsset $asset, ?DepreciationMethod $method = null): array
    {
        return [
            'depreciation_amount' => 0.0,
            'accumulated_after' => (float) $asset->accumulated_depreciation,
            'net_book_value_after' => $asset->netBookValue(),
            'monthly_depreciation' => 0.0,
            'annual_depreciation' => 0.0,
            'remaining_months' => 0,
            'is_fully_depreciated' => true,
            'method' => $method ?? $this->resolveMethod($asset),
        ];
    }
}
