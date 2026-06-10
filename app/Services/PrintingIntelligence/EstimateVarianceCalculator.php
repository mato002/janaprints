<?php

namespace App\Services\PrintingIntelligence;

use App\Enums\EstimateVarianceClass;

class EstimateVarianceCalculator
{
    /**
     * @param  array<string, float>  $estimated
     * @param  array<string, float>  $actual
     * @return array{
     *     material: array<string, float|null>,
     *     ink: array<string, float|null>,
     *     machine: array<string, float|null>,
     *     labour: array<string, float|null>,
     *     overhead: array<string, float|null>,
     *     total: array<string, float|null>,
     *     margin_variance_percent: float|null,
     *     accuracy_score: float|null,
     *     variance_class: EstimateVarianceClass,
     *     warnings: list<string>
     * }
     */
    public function calculate(
        array $estimated,
        array $actual,
        ?float $estimatedMarginPercent = null,
        ?float $actualMarginPercent = null,
    ): array {
        $warnings = [];

        $material = $this->categoryVariance(
            (float) ($estimated['material'] ?? 0),
            (float) ($actual['material'] ?? 0),
            $warnings,
        );
        $ink = $this->categoryVariance(
            (float) ($estimated['ink'] ?? 0),
            (float) ($actual['ink'] ?? 0),
            $warnings,
        );
        $machine = $this->categoryVariance(
            (float) ($estimated['machine'] ?? 0),
            (float) ($actual['machine'] ?? 0),
            $warnings,
        );
        $labour = $this->categoryVariance(
            (float) ($estimated['labour'] ?? 0),
            (float) ($actual['labour'] ?? 0),
            $warnings,
        );
        $overhead = $this->categoryVariance(
            (float) ($estimated['overhead'] ?? 0),
            (float) ($actual['overhead'] ?? 0),
            $warnings,
        );
        $total = $this->categoryVariance(
            (float) ($estimated['total'] ?? 0),
            (float) ($actual['total'] ?? 0),
            $warnings,
        );

        $marginVariance = null;
        if ($estimatedMarginPercent !== null && $actualMarginPercent !== null) {
            $marginVariance = round($actualMarginPercent - $estimatedMarginPercent, 3);
        }

        $accuracyScore = $this->accuracyScore($total['variance_percent']);
        $varianceClass = $this->classify($total['variance_percent'], (float) ($estimated['total'] ?? 0), (float) ($actual['total'] ?? 0));

        return [
            'material' => $material,
            'ink' => $ink,
            'machine' => $machine,
            'labour' => $labour,
            'overhead' => $overhead,
            'total' => $total,
            'margin_variance_percent' => $marginVariance,
            'accuracy_score' => $accuracyScore,
            'variance_class' => $varianceClass,
            'warnings' => $warnings,
        ];
    }

    /**
     * @param  list<string>  $warnings
     * @return array{variance: float, variance_percent: float|null}
     */
    protected function categoryVariance(float $estimated, float $actual, array &$warnings): array
    {
        $variance = round($actual - $estimated, 2);

        if ($estimated == 0.0) {
            if ($actual != 0.0) {
                $warnings[] = __('Estimated cost was zero; variance percent unavailable for this category.');
            }

            return [
                'variance' => $variance,
                'variance_percent' => null,
            ];
        }

        return [
            'variance' => $variance,
            'variance_percent' => round(($variance / $estimated) * 100, 3),
        ];
    }

    protected function accuracyScore(?float $totalVariancePercent): ?float
    {
        if ($totalVariancePercent === null) {
            return null;
        }

        return round(max(0, min(100, 100 - abs($totalVariancePercent))), 2);
    }

    protected function classify(?float $totalVariancePercent, float $estimatedTotal, float $actualTotal): EstimateVarianceClass
    {
        if ($estimatedTotal <= 0 && $actualTotal <= 0) {
            return EstimateVarianceClass::Unknown;
        }

        if ($totalVariancePercent === null) {
            return EstimateVarianceClass::Unknown;
        }

        $abs = abs($totalVariancePercent);

        return match (true) {
            $abs <= 5 => EstimateVarianceClass::Accurate,
            $abs <= 10 => EstimateVarianceClass::MinorVariance,
            $abs <= 20 => EstimateVarianceClass::ModerateVariance,
            $abs <= 35 => EstimateVarianceClass::MajorVariance,
            default => EstimateVarianceClass::Unreliable,
        };
    }
}
