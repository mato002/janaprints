<?php

namespace App\Services\PrintingIntelligence;

use App\Enums\ProductionEstimationStatus;
use App\Models\Assets\MachineProfile;
use App\Models\PrintingIntelligence\PrintArtworkAnalysis;
use App\Models\PrintingIntelligence\PrintArtworkProductionEstimate;
use Throwable;

class ProductionEstimationService
{
    public function __construct(
        protected MachineSelectionService $machineSelection,
        protected ProductionRunTimeEstimationService $runTimeEstimator,
        protected ProductionCostCalculator $costCalculator,
        protected ProductionEstimationConfidenceService $confidenceService,
    ) {}

    public function estimate(
        PrintArtworkAnalysis $analysis,
        ?MachineProfile $machineProfile = null,
        int $quantity = 1,
        bool $dryRun = false,
    ): PrintArtworkProductionEstimate {
        if (! config('printing_intelligence.production_costing_enabled', true)) {
            abort(503, __('Production costing is disabled.'));
        }

        $existing = PrintArtworkProductionEstimate::query()->firstOrNew([
            'print_artwork_analysis_id' => $analysis->id,
        ]);

        if ($dryRun) {
            return $existing->exists ? $existing : new PrintArtworkProductionEstimate([
                'company_id' => $analysis->company_id,
                'print_artwork_analysis_id' => $analysis->id,
                'estimation_status' => ProductionEstimationStatus::Pending,
                'quantity' => max(1, $quantity),
            ]);
        }

        PrintArtworkProductionEstimate::query()->updateOrCreate(
            ['print_artwork_analysis_id' => $analysis->id],
            [
                'company_id' => $analysis->company_id,
                'estimation_status' => ProductionEstimationStatus::Processing,
                'quantity' => max(1, $quantity),
            ],
        );

        try {
            $selection = $this->machineSelection->select($analysis, max(1, $quantity), $machineProfile);

            if ($selection['selected'] === null) {
                return $this->persistManualReview($analysis, max(1, $quantity), $selection['warnings']);
            }

            $machine = MachineProfile::query()->findOrFail($selection['selected']['machine_profile_id']);
            $runTime = $this->runTimeEstimator->estimate($analysis, $machine, max(1, $quantity));
            $runHours = (float) ($runTime['estimated_run_hours'] ?? 0);

            $analysis->loadMissing('inkEstimates');
            $inkEstimate = $analysis->inkEstimates->first();
            $inkCost = $inkEstimate?->estimated_ink_cost !== null ? (float) $inkEstimate->estimated_ink_cost : 0.0;

            $costs = $this->costCalculator->calculate($machine, $runHours, $inkCost, 0.0);
            $confidence = $this->confidenceService->score(
                $analysis,
                $machine,
                $inkEstimate,
                $runHours,
                array_merge($selection['warnings'], $runTime['warnings']),
            );

            $warnings = array_values(array_unique(array_merge(
                $selection['warnings'],
                $runTime['warnings'],
            )));

            $status = ProductionEstimationStatus::Completed;
            if ($warnings !== [] || $runHours <= 0) {
                $status = ProductionEstimationStatus::ManualReview;
            }

            return PrintArtworkProductionEstimate::query()->updateOrCreate(
                ['print_artwork_analysis_id' => $analysis->id],
                [
                    'company_id' => $analysis->company_id,
                    'machine_profile_id' => $machine->id,
                    'estimation_status' => $status,
                    'quantity' => max(1, $quantity),
                    'total_area_sq_m' => $runTime['total_area_sq_m'],
                    'estimated_run_hours' => $runHours > 0 ? $runHours : null,
                    'estimated_setup_cost' => $costs['estimated_setup_cost'],
                    'estimated_electricity_cost' => $costs['estimated_electricity_cost'],
                    'estimated_machine_cost' => $costs['estimated_machine_cost'],
                    'estimated_labour_cost' => $costs['estimated_labour_cost'],
                    'estimated_ink_cost' => $inkCost > 0 ? $inkCost : ($inkEstimate?->estimated_ink_cost),
                    'estimated_material_cost' => null,
                    'estimated_overhead_cost' => $costs['estimated_overhead_cost'],
                    'estimated_total_production_cost' => $costs['estimated_total_production_cost'],
                    'selection_score' => $selection['selected']['selection_score'],
                    'confidence_score' => $confidence['score'],
                    'formula_version' => $runTime['formula_version'],
                    'machine_alternatives' => $selection['alternatives'],
                    'metadata' => array_merge($runTime['metadata'], $costs['metadata'], [
                        'confidence_level' => $confidence['level'],
                        'confidence_factors' => $confidence['factors'],
                        'selected_machine' => $selection['selected'],
                    ]),
                    'warnings' => $warnings,
                    'estimated_at' => now(),
                ],
            );
        } catch (Throwable $exception) {
            return $this->persistFailed($analysis, max(1, $quantity), $exception->getMessage());
        }
    }

    /**
     * @param  list<string>  $warnings
     */
    protected function persistManualReview(
        PrintArtworkAnalysis $analysis,
        int $quantity,
        array $warnings,
    ): PrintArtworkProductionEstimate {
        return PrintArtworkProductionEstimate::query()->updateOrCreate(
            ['print_artwork_analysis_id' => $analysis->id],
            [
                'company_id' => $analysis->company_id,
                'estimation_status' => ProductionEstimationStatus::ManualReview,
                'quantity' => $quantity,
                'warnings' => $warnings,
                'formula_version' => config('printing_intelligence.production_formula_version', 'PI4-V1'),
                'estimated_at' => now(),
            ],
        );
    }

    protected function persistFailed(
        PrintArtworkAnalysis $analysis,
        int $quantity,
        string $reason,
    ): PrintArtworkProductionEstimate {
        return PrintArtworkProductionEstimate::query()->updateOrCreate(
            ['print_artwork_analysis_id' => $analysis->id],
            [
                'company_id' => $analysis->company_id,
                'estimation_status' => ProductionEstimationStatus::Failed,
                'quantity' => $quantity,
                'warnings' => [$reason],
                'formula_version' => config('printing_intelligence.production_formula_version', 'PI4-V1'),
                'estimated_at' => now(),
            ],
        );
    }
}
