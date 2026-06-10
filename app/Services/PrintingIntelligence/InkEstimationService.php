<?php

namespace App\Services\PrintingIntelligence;

use App\Enums\ColourAnalysisStatus;
use App\Enums\InkEstimationStatus;
use App\Models\PrintingIntelligence\PrintArtworkAnalysis;
use App\Models\PrintingIntelligence\PrintArtworkInkEstimate;
use App\Models\PrintingIntelligence\PrintInkProfile;
use Throwable;

class InkEstimationService
{
    public function __construct(
        protected InkConsumptionEstimationService $consumptionEstimator,
        protected InkCostCalculator $costCalculator,
        protected InkEstimationConfidenceService $confidenceService,
    ) {}

    public function estimate(
        PrintArtworkAnalysis $analysis,
        ?PrintInkProfile $inkProfile = null,
        bool $dryRun = false,
    ): PrintArtworkInkEstimate {
        if (! config('printing_intelligence.ink_costing_enabled', true)) {
            abort(503, __('Ink costing is disabled.'));
        }

        $profile = $inkProfile ?? $this->resolveInkProfile($analysis);

        $existing = PrintArtworkInkEstimate::query()->firstOrNew([
            'print_artwork_analysis_id' => $analysis->id,
            'ink_profile_id' => $profile?->id,
        ]);

        if ($dryRun) {
            return $existing->exists ? $existing : new PrintArtworkInkEstimate([
                'company_id' => $analysis->company_id,
                'print_artwork_analysis_id' => $analysis->id,
                'ink_profile_id' => $profile?->id,
                'estimation_status' => InkEstimationStatus::Pending,
            ]);
        }

        if ($profile === null) {
            return $this->persistManualReview($analysis, null, [__('No active ink profile available for estimation.')]);
        }

        if (! in_array($analysis->colour_analysis_status, [
            ColourAnalysisStatus::Completed,
            ColourAnalysisStatus::ManualReview,
        ], true)) {
            return $this->persistManualReview($analysis, $profile, [__('Colour analysis must be completed before ink estimation.')]);
        }

        PrintArtworkInkEstimate::query()->updateOrCreate(
            [
                'print_artwork_analysis_id' => $analysis->id,
                'ink_profile_id' => $profile->id,
            ],
            [
                'company_id' => $analysis->company_id,
                'estimation_status' => InkEstimationStatus::Processing,
            ],
        );

        try {
            $consumption = $this->consumptionEstimator->estimate($analysis, $profile);
            $cost = $this->costCalculator->calculate($profile, (float) $consumption['estimated_total_ml'], (int) $analysis->company_id);
            $confidence = $this->confidenceService->score(
                $analysis,
                $profile,
                $cost['cost_per_ml'],
                $consumption['warnings'],
            );

            $warnings = array_values(array_unique(array_merge(
                $consumption['warnings'],
                $cost['warnings'],
            )));

            $status = InkEstimationStatus::Completed;
            if ($warnings !== [] || $consumption['coverage_area_sq_m'] === null || $cost['estimated_ink_cost'] === null) {
                $status = InkEstimationStatus::ManualReview;
            }

            if ($consumption['estimated_total_ml'] <= 0 && $consumption['coverage_area_sq_m'] === null) {
                $status = InkEstimationStatus::ManualReview;
            }

            return PrintArtworkInkEstimate::query()->updateOrCreate(
                [
                    'print_artwork_analysis_id' => $analysis->id,
                    'ink_profile_id' => $profile->id,
                ],
                [
                    'company_id' => $analysis->company_id,
                    'estimation_status' => $status,
                    'coverage_percent' => $consumption['coverage_percent'],
                    'coverage_area_sq_m' => $consumption['coverage_area_sq_m'],
                    'estimated_cyan_ml' => $consumption['estimated_cyan_ml'],
                    'estimated_magenta_ml' => $consumption['estimated_magenta_ml'],
                    'estimated_yellow_ml' => $consumption['estimated_yellow_ml'],
                    'estimated_black_ml' => $consumption['estimated_black_ml'],
                    'estimated_total_ml' => $consumption['estimated_total_ml'],
                    'estimated_cartridge_percent' => $consumption['estimated_cartridge_percent'],
                    'estimated_ink_cost' => $cost['estimated_ink_cost'],
                    'confidence_score' => $confidence['score'],
                    'formula_version' => $consumption['formula_version'],
                    'metadata' => array_merge($consumption['metadata'], [
                        'confidence_level' => $confidence['level'],
                        'confidence_factors' => $confidence['factors'],
                        'cost_per_ml' => $cost['cost_per_ml'],
                    ]),
                    'warnings' => $warnings,
                    'estimated_at' => now(),
                ],
            );
        } catch (Throwable $exception) {
            return $this->persistFailed($analysis, $profile, $exception->getMessage());
        }
    }

    protected function resolveInkProfile(PrintArtworkAnalysis $analysis): ?PrintInkProfile
    {
        return PrintInkProfile::query()
            ->where('company_id', $analysis->company_id)
            ->where('active', true)
            ->orderBy('id')
            ->first();
    }

    /**
     * @param  list<string>  $warnings
     */
    protected function persistManualReview(
        PrintArtworkAnalysis $analysis,
        ?PrintInkProfile $profile,
        array $warnings,
    ): PrintArtworkInkEstimate {
        return PrintArtworkInkEstimate::query()->updateOrCreate(
            [
                'print_artwork_analysis_id' => $analysis->id,
                'ink_profile_id' => $profile?->id,
            ],
            [
                'company_id' => $analysis->company_id,
                'estimation_status' => InkEstimationStatus::ManualReview,
                'warnings' => $warnings,
                'formula_version' => config('printing_intelligence.default_formula_version', 'PI3-V1'),
                'estimated_at' => now(),
            ],
        );
    }

    protected function persistFailed(
        PrintArtworkAnalysis $analysis,
        PrintInkProfile $profile,
        string $reason,
    ): PrintArtworkInkEstimate {
        return PrintArtworkInkEstimate::query()->updateOrCreate(
            [
                'print_artwork_analysis_id' => $analysis->id,
                'ink_profile_id' => $profile->id,
            ],
            [
                'company_id' => $analysis->company_id,
                'estimation_status' => InkEstimationStatus::Failed,
                'warnings' => [$reason],
                'formula_version' => config('printing_intelligence.default_formula_version', 'PI3-V1'),
                'estimated_at' => now(),
            ],
        );
    }
}
