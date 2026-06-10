<?php

namespace App\Services\PrintingIntelligence;

use App\Enums\CalibrationRuleType;
use App\Models\PrintingIntelligence\PrintArtworkAnalysis;
use App\Models\PrintingIntelligence\PrintInkProfile;
use App\Support\PrintingIntelligence\CoverageClassifier;

class InkConsumptionEstimationService
{
    public function __construct(
        protected CoverageClassifier $coverageClassifier,
        protected ActiveCostingProfileService $activeProfile,
    ) {}

    /**
     * PI3-V1 deterministic ink consumption formula.
     *
     * total_area_sq_m = Σ(page.area_square_m) or analysis.area_square_m × page_count
     * coverage_percent = resolved total ink coverage from colour analysis
     * coverage_area_sq_m = total_area_sq_m × (coverage_percent / 100)
     * ml_per_sq_m_full = profile.estimated_ml / profile.estimated_yield_sq_m
     *   (fallback: estimated_ml / (estimated_yield_pages × page_area_sq_m))
     * channel_ml = coverage_area_sq_m × (channel_coverage_percent / 100)
     *              × ml_per_sq_m_full × coverage_factor
     * estimated_total_ml = C + M + Y + K
     *
     * @return array{
     *     coverage_percent: float|null,
     *     coverage_area_sq_m: float|null,
     *     estimated_cyan_ml: float,
     *     estimated_magenta_ml: float,
     *     estimated_yellow_ml: float,
     *     estimated_black_ml: float,
     *     estimated_total_ml: float,
     *     estimated_cartridge_percent: float|null,
     *     formula_version: string,
     *     metadata: array<string, mixed>,
     *     warnings: list<string>
     * }
     */
    public function estimate(PrintArtworkAnalysis $analysis, PrintInkProfile $profile): array
    {
        $analysis->loadMissing('pages');
        $formulaVersion = (string) config('printing_intelligence.default_formula_version', 'PI3-V1');
        $coverageFactor = (float) $this->activeProfile->value(
            CalibrationRuleType::InkYield,
            'default_cmyk_coverage_factor',
            (int) $analysis->company_id,
            1.0,
        );
        $warnings = [];

        $totalAreaSqM = $this->resolveTotalAreaSquareMeters($analysis);
        if ($totalAreaSqM === null || $totalAreaSqM <= 0) {
            $warnings[] = __('Artwork area unavailable; cannot estimate ink consumption.');

            return $this->emptyResult($formulaVersion, $warnings, [
                'total_area_sq_m' => $totalAreaSqM,
            ]);
        }

        $coveragePercent = $this->resolveCoveragePercent($analysis);
        if ($coveragePercent === null) {
            $warnings[] = __('Colour coverage unavailable; run colour analysis first.');

            return $this->emptyResult($formulaVersion, $warnings, [
                'total_area_sq_m' => $totalAreaSqM,
            ]);
        }

        $mlPerSqMFull = $this->resolveMlPerSquareMeterAtFullCoverage($analysis, $profile, $warnings);
        if ($mlPerSqMFull === null) {
            return $this->emptyResult($formulaVersion, $warnings, [
                'total_area_sq_m' => $totalAreaSqM,
                'coverage_percent' => $coveragePercent,
            ]);
        }

        $coverageAreaSqM = round($totalAreaSqM * ($coveragePercent / 100), 6);

        $cyanPercent = (float) ($analysis->cyan_coverage_percent ?? 0);
        $magentaPercent = (float) ($analysis->magenta_coverage_percent ?? 0);
        $yellowPercent = (float) ($analysis->yellow_coverage_percent ?? 0);
        $blackPercent = (float) ($analysis->black_coverage_percent ?? 0);

        if ($cyanPercent + $magentaPercent + $yellowPercent + $blackPercent <= 0) {
            $rgbCoverage = (float) ($analysis->rgb_coverage_percent ?? $coveragePercent);
            $cyanPercent = $magentaPercent = $yellowPercent = $blackPercent = $rgbCoverage / 4;
            $warnings[] = __('CMYK channel split unavailable; using equal RGB approximation.');
        }

        $estimatedCyan = $this->channelMl($coverageAreaSqM, $cyanPercent, $mlPerSqMFull, $coverageFactor);
        $estimatedMagenta = $this->channelMl($coverageAreaSqM, $magentaPercent, $mlPerSqMFull, $coverageFactor);
        $estimatedYellow = $this->channelMl($coverageAreaSqM, $yellowPercent, $mlPerSqMFull, $coverageFactor);
        $estimatedBlack = $this->channelMl($coverageAreaSqM, $blackPercent, $mlPerSqMFull, $coverageFactor);
        $estimatedTotal = round($estimatedCyan + $estimatedMagenta + $estimatedYellow + $estimatedBlack, 4);

        $cartridgePercent = null;
        $profileMl = (float) ($profile->estimated_ml ?? 0);
        if ($profileMl > 0) {
            $cartridgePercent = round(min(999.999, ($estimatedTotal / $profileMl) * 100), 3);
        } else {
            $warnings[] = __('Ink profile missing estimated_ml; cartridge consumption not calculated.');
        }

        return [
            'coverage_percent' => round($coveragePercent, 3),
            'coverage_area_sq_m' => $coverageAreaSqM,
            'estimated_cyan_ml' => $estimatedCyan,
            'estimated_magenta_ml' => $estimatedMagenta,
            'estimated_yellow_ml' => $estimatedYellow,
            'estimated_black_ml' => $estimatedBlack,
            'estimated_total_ml' => $estimatedTotal,
            'estimated_cartridge_percent' => $cartridgePercent,
            'formula_version' => $formulaVersion,
            'metadata' => [
                'total_area_sq_m' => round($totalAreaSqM, 6),
                'ml_per_sq_m_at_full_coverage' => round($mlPerSqMFull, 6),
                'coverage_factor' => $coverageFactor,
                'page_count' => (int) ($analysis->page_count ?? max(1, $analysis->pages->count())),
                'channel_coverage_percent' => [
                    'cyan' => round($cyanPercent, 3),
                    'magenta' => round($magentaPercent, 3),
                    'yellow' => round($yellowPercent, 3),
                    'black' => round($blackPercent, 3),
                ],
            ],
            'warnings' => $warnings,
        ];
    }

    protected function channelMl(float $coverageAreaSqM, float $channelPercent, float $mlPerSqMFull, float $coverageFactor): float
    {
        return round($coverageAreaSqM * ($channelPercent / 100) * $mlPerSqMFull * $coverageFactor, 4);
    }

    protected function resolveCoveragePercent(PrintArtworkAnalysis $analysis): ?float
    {
        return $this->coverageClassifier->resolveTotalCoverage([
            'rgb_coverage_percent' => $analysis->rgb_coverage_percent !== null ? (float) $analysis->rgb_coverage_percent : null,
            'cmyk_coverage_percent' => $analysis->cmyk_coverage_percent !== null ? (float) $analysis->cmyk_coverage_percent : null,
            'cyan_coverage_percent' => $analysis->cyan_coverage_percent !== null ? (float) $analysis->cyan_coverage_percent : null,
            'magenta_coverage_percent' => $analysis->magenta_coverage_percent !== null ? (float) $analysis->magenta_coverage_percent : null,
            'yellow_coverage_percent' => $analysis->yellow_coverage_percent !== null ? (float) $analysis->yellow_coverage_percent : null,
            'black_coverage_percent' => $analysis->black_coverage_percent !== null ? (float) $analysis->black_coverage_percent : null,
        ]);
    }

    protected function resolveTotalAreaSquareMeters(PrintArtworkAnalysis $analysis): ?float
    {
        if ($analysis->pages->isNotEmpty()) {
            $sum = $analysis->pages->sum(fn ($page) => (float) ($page->area_square_m ?? 0));

            if ($sum > 0) {
                return (float) $sum;
            }
        }

        $pageArea = (float) ($analysis->area_square_m ?? 0);
        if ($pageArea <= 0) {
            return null;
        }

        $pageCount = max(1, (int) ($analysis->page_count ?? $analysis->pages->count() ?: 1));

        return $pageArea * $pageCount;
    }

    /**
     * @param  list<string>  $warnings
     */
    protected function resolveMlPerSquareMeterAtFullCoverage(
        PrintArtworkAnalysis $analysis,
        PrintInkProfile $profile,
        array &$warnings,
    ): ?float {
        $profileMl = (float) ($profile->estimated_ml ?? 0);
        $yieldSqM = (float) ($profile->estimated_yield_sq_m ?? 0);

        if ($profileMl > 0 && $yieldSqM > 0) {
            return $profileMl / $yieldSqM;
        }

        $yieldPages = (int) ($profile->estimated_yield_pages ?? 0);
        $pageArea = (float) ($analysis->area_square_m ?? 0);

        if ($profileMl > 0 && $yieldPages > 0 && $pageArea > 0) {
            return $profileMl / ($yieldPages * $pageArea);
        }

        if (config('printing_intelligence.allow_estimation_without_yield', false)) {
            $fallbackMlPerSqM = (float) config('printing_intelligence.default_ml_per_sq_m_fallback', 0.5);
            $warnings[] = __('Ink profile yield missing; using configured fallback ml/m².');

            return $fallbackMlPerSqM;
        }

        $warnings[] = __('Ink profile missing yield data (m² or pages); manual review required.');

        return null;
    }

    /**
     * @param  list<string>  $warnings
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>
     */
    protected function emptyResult(string $formulaVersion, array $warnings, array $metadata = []): array
    {
        return [
            'coverage_percent' => $metadata['coverage_percent'] ?? null,
            'coverage_area_sq_m' => null,
            'estimated_cyan_ml' => 0.0,
            'estimated_magenta_ml' => 0.0,
            'estimated_yellow_ml' => 0.0,
            'estimated_black_ml' => 0.0,
            'estimated_total_ml' => 0.0,
            'estimated_cartridge_percent' => null,
            'formula_version' => $formulaVersion,
            'metadata' => $metadata,
            'warnings' => $warnings,
        ];
    }
}
