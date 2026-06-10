<?php

namespace App\Services\PrintingIntelligence;

use App\Models\Assets\MachineProfile;
use App\Models\PrintingIntelligence\PrintArtworkAnalysis;
use App\Support\PrintingIntelligence\CoverageClassifier;

class ProductionRunTimeEstimationService
{
    public function __construct(
        protected CoverageClassifier $coverageClassifier,
    ) {}

    /**
     * PI4-V1 run-time formula.
     *
     * workload_units = max(1, quantity) × max(1, page_count)
     * total_area_sq_m = Σ(page.area) or analysis.area × page_count
     *
     * If machine target_output_per_hour > 0 and total_area_sq_m > 0:
     *   base_run_hours = total_area_sq_m / target_output_per_hour
     * Else if target_output_per_hour > 0:
     *   base_run_hours = workload_units / target_output_per_hour
     * Else if capacity_per_hour > 0:
     *   base_run_hours = workload_units / capacity_per_hour
     *
     * coverage_factor = 1 + (coverage_percent/100 × ink_run_time_factor)
     * estimated_run_hours = max(minimum_run_hours, base_run_hours × coverage_factor)
     *
     * @return array{
     *     total_area_sq_m: float|null,
     *     quantity: int,
     *     base_run_hours: float|null,
     *     estimated_run_hours: float|null,
     *     coverage_factor: float,
     *     formula_version: string,
     *     warnings: list<string>,
     *     metadata: array<string, mixed>
     * }
     */
    public function estimate(PrintArtworkAnalysis $analysis, MachineProfile $machine, int $quantity = 1): array
    {
        $analysis->loadMissing('pages');
        $formulaVersion = (string) config('printing_intelligence.production_formula_version', 'PI4-V1');
        $warnings = [];
        $quantity = max(1, $quantity);
        $pageCount = max(1, (int) ($analysis->page_count ?? $analysis->pages->count() ?: 1));
        $workloadUnits = $quantity * $pageCount;

        $totalAreaSqM = $this->resolveTotalAreaSquareMeters($analysis, $quantity);
        if ($totalAreaSqM === null || $totalAreaSqM <= 0) {
            $warnings[] = __('Artwork area unavailable; run time estimation requires dimensions.');
        }

        $targetOutput = (float) ($machine->target_output_per_hour ?? 0);
        if ($targetOutput <= 0) {
            $targetOutput = (float) ($machine->capacity_per_hour ?? 0);
        }

        $baseRunHours = null;
        if ($targetOutput > 0) {
            if ($totalAreaSqM !== null && $totalAreaSqM > 0) {
                $baseRunHours = $totalAreaSqM / $targetOutput;
            } else {
                $baseRunHours = $workloadUnits / $targetOutput;
            }
        } else {
            $warnings[] = __('Machine missing output rate; run time cannot be calculated.');
        }

        $coveragePercent = $this->coverageClassifier->resolveTotalCoverage([
            'rgb_coverage_percent' => $analysis->rgb_coverage_percent !== null ? (float) $analysis->rgb_coverage_percent : null,
            'cmyk_coverage_percent' => $analysis->cmyk_coverage_percent !== null ? (float) $analysis->cmyk_coverage_percent : null,
            'cyan_coverage_percent' => $analysis->cyan_coverage_percent !== null ? (float) $analysis->cyan_coverage_percent : null,
            'magenta_coverage_percent' => $analysis->magenta_coverage_percent !== null ? (float) $analysis->magenta_coverage_percent : null,
            'yellow_coverage_percent' => $analysis->yellow_coverage_percent !== null ? (float) $analysis->yellow_coverage_percent : null,
            'black_coverage_percent' => $analysis->black_coverage_percent !== null ? (float) $analysis->black_coverage_percent : null,
        ]) ?? 0.0;

        $inkRunFactor = (float) config('printing_intelligence.ink_run_time_factor', 0.15);
        $coverageFactor = round(1 + (($coveragePercent / 100) * $inkRunFactor), 4);

        $estimatedRunHours = null;
        if ($baseRunHours !== null) {
            $minimum = (float) config('printing_intelligence.default_minimum_run_hours', 0.25);
            $estimatedRunHours = round(max($minimum, $baseRunHours * $coverageFactor), 4);
        }

        return [
            'total_area_sq_m' => $totalAreaSqM !== null ? round($totalAreaSqM, 6) : null,
            'quantity' => $quantity,
            'base_run_hours' => $baseRunHours !== null ? round($baseRunHours, 4) : null,
            'estimated_run_hours' => $estimatedRunHours,
            'coverage_factor' => $coverageFactor,
            'formula_version' => $formulaVersion,
            'warnings' => $warnings,
            'metadata' => [
                'page_count' => $pageCount,
                'workload_units' => $workloadUnits,
                'target_output_per_hour' => $targetOutput > 0 ? $targetOutput : null,
                'coverage_percent' => round($coveragePercent, 3),
                'machine_code' => $machine->machine_code,
            ],
        ];
    }

    protected function resolveTotalAreaSquareMeters(PrintArtworkAnalysis $analysis, int $quantity): ?float
    {
        if ($analysis->pages->isNotEmpty()) {
            $sum = $analysis->pages->sum(fn ($page) => (float) ($page->area_square_m ?? 0));
            if ($sum > 0) {
                return $sum * $quantity;
            }
        }

        $pageArea = (float) ($analysis->area_square_m ?? 0);
        if ($pageArea <= 0) {
            return null;
        }

        $pageCount = max(1, (int) ($analysis->page_count ?? $analysis->pages->count() ?: 1));

        return $pageArea * $pageCount * $quantity;
    }
}
