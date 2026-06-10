<?php

namespace App\Services\PrintingIntelligence;

use App\Enums\EstimateActualComparisonStatus;
use App\Enums\EstimateVarianceClass;

class CostAccuracyRecommendationService
{
    /**
     * @param  array<string, mixed>  $variance
     * @param  list<string>  $warnings
     */
    public function recommend(array $variance, array $warnings, ?string $comparisonStatus = null): string
    {
        if ($comparisonStatus === EstimateActualComparisonStatus::Pending->value
            || $comparisonStatus === EstimateActualComparisonStatus::ManualReview->value) {
            if ($this->hasActualUnavailableWarning($warnings)) {
                return __('Actual job cost unavailable; wait until production completion before reviewing estimate accuracy.');
            }
        }

        $varianceClass = $variance['variance_class'] ?? EstimateVarianceClass::Unknown;
        if ($varianceClass === EstimateVarianceClass::Unknown) {
            return __('Insufficient comparison data; complete production and job costing before reviewing accuracy.');
        }

        if ($varianceClass === EstimateVarianceClass::Accurate) {
            return __('Estimate accuracy is within target range; no immediate action needed.');
        }

        $recommendations = [];

        $inkPercent = $variance['ink']['variance_percent'] ?? null;
        if ($inkPercent !== null && $inkPercent < -10) {
            $recommendations[] = __('Ink estimate is consistently below actual by :percent%; review ink profile yield.', [
                'percent' => number_format(abs($inkPercent), 1),
            ]);
        } elseif ($inkPercent !== null && $inkPercent > 10) {
            $recommendations[] = __('Ink estimate exceeds actual by :percent%; ink profile may be conservative.', [
                'percent' => number_format($inkPercent, 1),
            ]);
        }

        $machinePercent = $variance['machine']['variance_percent'] ?? null;
        if ($machinePercent !== null && abs($machinePercent) > 15) {
            $recommendations[] = __('Machine/process variance is high; review machine cost_per_hour and run-time assumptions.');
        }

        $materialPercent = $variance['material']['variance_percent'] ?? null;
        if ($materialPercent !== null && abs($materialPercent) <= 5) {
            $recommendations[] = __('Material cost accurate; no action needed for material costing.');
        } elseif ($materialPercent !== null && abs($materialPercent) > 15) {
            $recommendations[] = __('Material cost variance is elevated; review material unit cost and consumption quantity.');
        }

        $overheadPercent = $variance['overhead']['variance_percent'] ?? null;
        if ($overheadPercent !== null && abs($overheadPercent) > 15) {
            $recommendations[] = __('Overhead variance is high; review overhead rates and allocation rules.');
        }

        if ($varianceClass === EstimateVarianceClass::Unreliable) {
            $recommendations[] = __('Total estimate reliability is low; schedule manual review of PI3/PI4/PI5 assumptions.');
        }

        if ($recommendations === []) {
            return __('Monitor ongoing variance; no specific advisory action at this time.');
        }

        return implode(' ', array_unique($recommendations));
    }

    /**
     * @param  list<string>  $warnings
     */
    protected function hasActualUnavailableWarning(array $warnings): bool
    {
        foreach ($warnings as $warning) {
            if (str_contains(strtolower($warning), 'unavailable') || str_contains(strtolower($warning), 'missing')) {
                return true;
            }
        }

        return false;
    }
}
