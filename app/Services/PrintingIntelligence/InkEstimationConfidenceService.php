<?php

namespace App\Services\PrintingIntelligence;

use App\Enums\ColourAnalysisStatus;
use App\Models\PrintingIntelligence\PrintArtworkAnalysis;
use App\Models\PrintingIntelligence\PrintInkProfile;

class InkEstimationConfidenceService
{
    /**
     * @param  list<string>  $estimationWarnings
     * @return array{score: float, level: string, factors: list<string>}
     */
    public function score(
        PrintArtworkAnalysis $analysis,
        PrintInkProfile $profile,
        ?float $costPerMl,
        array $estimationWarnings = [],
    ): array {
        $score = 100.0;
        $factors = [];

        $colourStatus = $analysis->colour_analysis_status;
        if (! in_array($colourStatus, [ColourAnalysisStatus::Completed, ColourAnalysisStatus::ManualReview], true)) {
            $score -= 40;
            $factors[] = 'colour_analysis_incomplete';
        }

        if ($analysis->width_mm === null || $analysis->height_mm === null || (float) ($analysis->area_square_m ?? 0) <= 0) {
            $score -= 20;
            $factors[] = 'missing_dimensions';
        }

        if ($analysis->resolution_dpi === null || (float) $analysis->resolution_dpi <= 0) {
            $score -= 10;
            $factors[] = 'missing_dpi';
        } elseif ((float) $analysis->resolution_dpi < (float) config('printing_intelligence.low_resolution_dpi_threshold', 150)) {
            $score -= 5;
            $factors[] = 'low_resolution';
        }

        $hasCmyk = collect([
            $analysis->cyan_coverage_percent,
            $analysis->magenta_coverage_percent,
            $analysis->yellow_coverage_percent,
            $analysis->black_coverage_percent,
        ])->filter(fn ($value) => $value !== null && (float) $value > 0)->isNotEmpty();

        if (! $hasCmyk) {
            $score -= 15;
            $factors[] = 'rgb_approximation';
        }

        if ($profile->estimated_yield_sq_m === null && $profile->estimated_yield_pages === null) {
            $score -= 15;
            $factors[] = 'missing_yield_data';
        }

        if ($profile->cost_per_ml === null && ((float) ($profile->estimated_ml ?? 0) <= 0 || (float) $profile->cartridge_cost <= 0)) {
            $score -= 10;
            $factors[] = 'incomplete_cost_profile';
        }

        if ($costPerMl === null) {
            $score -= 10;
            $factors[] = 'cost_per_ml_unavailable';
        }

        if ($analysis->file_extension === 'pdf') {
            $score -= 5;
            $factors[] = 'pdf_coverage_source';
        }

        foreach ($estimationWarnings as $warning) {
            if (is_string($warning) && str_contains(strtolower($warning), 'approximation')) {
                $score -= 5;
                $factors[] = 'estimation_approximation';
                break;
            }
        }

        $score = round(max(0, min(100, $score)), 2);

        $high = (float) config('printing_intelligence.high_confidence_score', 80);
        $minimum = (float) config('printing_intelligence.minimum_confidence_score', 40);

        $level = match (true) {
            $score >= $high => 'high',
            $score >= $minimum => 'medium',
            default => 'low',
        };

        return [
            'score' => $score,
            'level' => $level,
            'factors' => array_values(array_unique($factors)),
        ];
    }
}
