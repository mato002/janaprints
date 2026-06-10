<?php

namespace App\Services\PrintingIntelligence;

use App\Models\Assets\MachineProfile;
use App\Models\PrintingIntelligence\PrintArtworkAnalysis;
use App\Models\PrintingIntelligence\PrintArtworkInkEstimate;

class ProductionEstimationConfidenceService
{
    public function __construct(
        protected MachineCostProfileService $machineCostProfile,
    ) {}

    /**
     * @param  list<string>  $warnings
     * @return array{score: float, level: string, factors: list<string>}
     */
    public function score(
        PrintArtworkAnalysis $analysis,
        MachineProfile $machine,
        ?PrintArtworkInkEstimate $inkEstimate,
        ?float $runHours,
        array $warnings = [],
    ): array {
        $score = 100.0;
        $factors = [];

        if ($runHours === null || $runHours <= 0) {
            $score -= 35;
            $factors[] = 'missing_run_hours';
        }

        if ($analysis->area_square_m === null && $analysis->pages->every(fn ($p) => $p->area_square_m === null)) {
            $score -= 20;
            $factors[] = 'missing_area';
        }

        if ($this->machineCostProfile->costPerHour($machine) <= 0) {
            $score -= 15;
            $factors[] = 'missing_machine_rate';
        }

        if (($machine->target_output_per_hour ?? 0) <= 0 && ($machine->capacity_per_hour ?? 0) <= 0) {
            $score -= 15;
            $factors[] = 'missing_machine_output';
        }

        if ($inkEstimate === null) {
            $score -= 10;
            $factors[] = 'missing_ink_estimate';
        } elseif ($inkEstimate->estimated_ink_cost === null) {
            $score -= 5;
            $factors[] = 'incomplete_ink_cost';
        }

        if ($analysis->colour_analysis_status === null) {
            $score -= 10;
            $factors[] = 'missing_colour_analysis';
        }

        foreach ($warnings as $warning) {
            if (is_string($warning) && str_contains(strtolower($warning), 'manual')) {
                $score -= 5;
                $factors[] = 'manual_review_warning';
                break;
            }
        }

        $score = round(max(0, min(100, $score)), 2);

        $high = (float) config('printing_intelligence.high_confidence_score', 80);
        $minimum = (float) config('printing_intelligence.minimum_confidence_score', 40);

        return [
            'score' => $score,
            'level' => match (true) {
                $score >= $high => 'high',
                $score >= $minimum => 'medium',
                default => 'low',
            },
            'factors' => array_values(array_unique($factors)),
        ];
    }
}
