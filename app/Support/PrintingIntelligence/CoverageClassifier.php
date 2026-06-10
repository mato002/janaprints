<?php

namespace App\Support\PrintingIntelligence;

use App\Enums\CoverageClass;

class CoverageClassifier
{
    /**
     * @param  array<string, mixed>  $metrics
     * @return array{coverage_class: CoverageClass, heavy_coverage_score: float, warnings: list<string>}
     */
    public function classify(array $metrics): array
    {
        $totalCoverage = $this->resolveTotalCoverageValue($metrics);
        $black = (float) ($metrics['black_coverage_percent'] ?? 0);
        $cyan = (float) ($metrics['cyan_coverage_percent'] ?? 0);
        $magenta = (float) ($metrics['magenta_coverage_percent'] ?? 0);
        $yellow = (float) ($metrics['yellow_coverage_percent'] ?? 0);
        $white = (float) ($metrics['white_area_percent'] ?? 0);
        $transparent = (float) ($metrics['transparent_area_percent'] ?? 0);
        $dpi = isset($metrics['resolution_dpi']) ? (float) $metrics['resolution_dpi'] : null;
        $hasTransparency = (bool) ($metrics['has_transparency'] ?? false);
        $manualPdf = (bool) ($metrics['manual_pdf_review'] ?? false);

        $coverageClass = $this->coverageClassForTotal($this->resolveTotalCoverageValue($metrics));
        $cmykSum = $cyan + $magenta + $yellow + $black;
        $heavyScore = round(min(100, ($totalCoverage * 0.45) + ($black * 0.35) + ($cmykSum * 0.05)), 3);

        $warnings = [];

        if ($manualPdf) {
            $warnings[] = __('manual_pdf_review');
        }

        if ($hasTransparency || $transparent > 0) {
            $warnings[] = __('transparent_background');
        }

        if ($dpi !== null && $dpi > 0 && $dpi < (float) config('printing_intelligence.low_resolution_dpi_threshold', 150)) {
            $warnings[] = __('low_resolution');
        }

        if ($totalCoverage > (float) config('printing_intelligence.heavy_coverage_warning_percent', 80)) {
            $warnings[] = __('high_ink_coverage');
        }

        if ($black > (float) config('printing_intelligence.black_heavy_warning_percent', 70)) {
            $warnings[] = __('mostly_black');
        }

        if ($white > 95 && $totalCoverage < 5) {
            $warnings[] = __('mostly_white');
        }

        return [
            'coverage_class' => $coverageClass,
            'heavy_coverage_score' => $heavyScore,
            'warnings' => array_values(array_unique($warnings)),
        ];
    }

    public function coverageClassForTotal(?float $totalCoverage): CoverageClass
    {
        if ($totalCoverage === null) {
            return CoverageClass::Unknown;
        }

        $low = (float) config('printing_intelligence.coverage_low_percent', 20);
        $medium = (float) config('printing_intelligence.coverage_medium_percent', 50);
        $high = (float) config('printing_intelligence.coverage_high_percent', 80);

        return match (true) {
            $totalCoverage <= $low => CoverageClass::Low,
            $totalCoverage <= $medium => CoverageClass::Medium,
            $totalCoverage <= $high => CoverageClass::High,
            default => CoverageClass::Full,
        };
    }

    /**
     * @param  array<string, mixed>  $metrics
     */
    public function resolveTotalCoverage(array $metrics): ?float
    {
        return $this->resolveTotalCoverageValue($metrics);
    }

    /**
     * Resolve page-level ink coverage for classification and warnings.
     *
     * Image analysis stores RGB as inked area % and CMYK as average density among
     * inked pixels. PDF inkcov stores CMYK plate coverage; RGB mirrors CMYK average.
     *
     * @param  array<string, mixed>  $metrics
     */
    protected function resolveTotalCoverageValue(array $metrics): ?float
    {
        $rgb = isset($metrics['rgb_coverage_percent']) && $metrics['rgb_coverage_percent'] !== null
            ? (float) $metrics['rgb_coverage_percent']
            : null;
        $cmyk = isset($metrics['cmyk_coverage_percent']) && $metrics['cmyk_coverage_percent'] !== null
            ? (float) $metrics['cmyk_coverage_percent']
            : null;

        $maxChannel = max(
            (float) ($metrics['cyan_coverage_percent'] ?? 0),
            (float) ($metrics['magenta_coverage_percent'] ?? 0),
            (float) ($metrics['yellow_coverage_percent'] ?? 0),
            (float) ($metrics['black_coverage_percent'] ?? 0),
        );

        if ($rgb !== null && $cmyk !== null && $rgb > $cmyk + 0.001) {
            return $rgb;
        }

        if ($rgb !== null && $cmyk !== null && abs($rgb - $cmyk) < 0.001 && $maxChannel > 0) {
            return max($rgb, $maxChannel);
        }

        if ($rgb !== null) {
            return max($rgb, $maxChannel);
        }

        if ($cmyk !== null) {
            return max($cmyk, $maxChannel);
        }

        return $maxChannel > 0 ? $maxChannel : null;
    }
}
