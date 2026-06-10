<?php

namespace App\Services\PrintingIntelligence;

use App\Enums\EstimateActualComparisonStatus;
use App\Models\PrintingIntelligence\PrintEstimateActualComparison;
use App\Models\PrintingIntelligence\PrintQuotationEstimate;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\Quotation;
use Throwable;

class EstimateActualComparisonService
{
    public function __construct(
        protected ActualProductionCostResolver $actualResolver,
        protected EstimateVarianceCalculator $varianceCalculator,
        protected CostAccuracyRecommendationService $recommendations,
    ) {}

    public function compareEstimate(PrintQuotationEstimate $estimate): PrintEstimateActualComparison
    {
        if (! config('printing_intelligence.estimate_actual_learning_enabled', true)) {
            abort(503, __('Estimate vs actual learning is disabled.'));
        }

        $estimate->loadMissing(['analysis']);

        $jobCard = $this->resolveJobForEstimate($estimate);
        $quotation = $estimate->quotation_id
            ? Quotation::query()->find($estimate->quotation_id)
            : null;

        return $this->persistComparison(
            lookup: ['print_quotation_estimate_id' => $estimate->id],
            companyId: (int) $estimate->company_id,
            branchId: $estimate->branch_id,
            estimate: $estimate,
            quotation: $quotation,
            jobCard: $jobCard,
        );
    }

    public function compareJob(ProductionJobCard $jobCard): PrintEstimateActualComparison
    {
        if (! config('printing_intelligence.estimate_actual_learning_enabled', true)) {
            abort(503, __('Estimate vs actual learning is disabled.'));
        }

        $jobCard->loadMissing(['quotation']);

        $estimate = $this->resolveEstimateForJob($jobCard);

        return $this->persistComparison(
            lookup: ['production_job_card_id' => $jobCard->id],
            companyId: (int) $jobCard->company_id,
            branchId: $jobCard->branch_id,
            estimate: $estimate,
            quotation: $jobCard->quotation,
            jobCard: $jobCard,
        );
    }

    public function compareQuotation(Quotation $quotation): PrintEstimateActualComparison
    {
        if (! config('printing_intelligence.estimate_actual_learning_enabled', true)) {
            abort(503, __('Estimate vs actual learning is disabled.'));
        }

        $estimate = PrintQuotationEstimate::query()
            ->where('quotation_id', $quotation->id)
            ->latest('id')
            ->first();

        if ($estimate !== null) {
            return $this->compareEstimate($estimate);
        }

        $jobCard = ProductionJobCard::query()
            ->where('quotation_id', $quotation->id)
            ->latest('id')
            ->first();

        if ($jobCard !== null) {
            return $this->compareJob($jobCard);
        }

        $lookup = [
            'quotation_id' => $quotation->id,
            'print_quotation_estimate_id' => null,
            'production_job_card_id' => null,
        ];

        return $this->persistComparison(
            lookup: $lookup,
            companyId: (int) $quotation->company_id,
            branchId: $quotation->branch_id,
            estimate: null,
            quotation: $quotation,
            jobCard: null,
        );
    }

    /**
     * @param  array<string, int|null>  $lookup
     */
    protected function persistComparison(
        array $lookup,
        int $companyId,
        ?int $branchId,
        ?PrintQuotationEstimate $estimate,
        ?Quotation $quotation,
        ?ProductionJobCard $jobCard,
    ): PrintEstimateActualComparison {
        $base = array_merge($lookup, [
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'comparison_status' => EstimateActualComparisonStatus::Processing,
        ]);

        PrintEstimateActualComparison::query()->updateOrCreate($lookup, $base);

        try {
            $estimated = $this->estimatedCosts($estimate, $quotation);
            $warnings = [];

            if ($jobCard === null) {
                $warnings[] = __('Actual production data unavailable; job not linked to estimate or quotation.');

                return PrintEstimateActualComparison::query()->updateOrCreate($lookup, array_merge($base, [
                    'quotation_id' => $quotation?->id ?? $estimate?->quotation_id,
                    'print_quotation_estimate_id' => $estimate?->id,
                    'comparison_status' => EstimateActualComparisonStatus::ManualReview,
                    'estimated_material_cost' => $estimated['material'],
                    'estimated_ink_cost' => $estimated['ink'],
                    'estimated_machine_cost' => $estimated['machine'],
                    'estimated_labour_cost' => $estimated['labour'],
                    'estimated_overhead_cost' => $estimated['overhead'],
                    'estimated_total_cost' => $estimated['total'],
                    'recommended_price' => $estimated['recommended_price'],
                    'estimated_margin_percent' => $estimated['margin_percent'],
                    'confidence_score' => $estimated['confidence_score'],
                    'warnings' => $warnings,
                    'recommendation' => $this->recommendations->recommend([], $warnings, EstimateActualComparisonStatus::ManualReview->value),
                    'compared_at' => now(),
                ]));
            }

            $actuals = $this->actualResolver->resolve($jobCard, $quotation);
            $warnings = array_merge($warnings, $actuals['warnings']);

            if ($actuals['job_cost_sheet_id'] === null || $actuals['actual_total_cost'] <= 0) {
                $warnings[] = __('Actual production data unavailable; job costing not complete.');

                return PrintEstimateActualComparison::query()->updateOrCreate($lookup, array_merge($base, [
                    'quotation_id' => $quotation?->id ?? $jobCard->quotation_id ?? $estimate?->quotation_id,
                    'print_quotation_estimate_id' => $estimate?->id,
                    'production_job_card_id' => $jobCard->id,
                    'comparison_status' => EstimateActualComparisonStatus::ManualReview,
                    'estimated_material_cost' => $estimated['material'],
                    'estimated_ink_cost' => $estimated['ink'],
                    'estimated_machine_cost' => $estimated['machine'],
                    'estimated_labour_cost' => $estimated['labour'],
                    'estimated_overhead_cost' => $estimated['overhead'],
                    'estimated_total_cost' => $estimated['total'],
                    'recommended_price' => $estimated['recommended_price'],
                    'estimated_margin_percent' => $estimated['margin_percent'],
                    'confidence_score' => $estimated['confidence_score'],
                    'warnings' => array_values(array_unique($warnings)),
                    'recommendation' => $this->recommendations->recommend([], $warnings, EstimateActualComparisonStatus::ManualReview->value),
                    'compared_at' => now(),
                ]));
            }

            $variance = $this->varianceCalculator->calculate(
                [
                    'material' => $estimated['material'],
                    'ink' => $estimated['ink'],
                    'machine' => $estimated['machine'],
                    'labour' => $estimated['labour'],
                    'overhead' => $estimated['overhead'],
                    'total' => $estimated['total'],
                ],
                [
                    'material' => $actuals['actual_material_cost'],
                    'ink' => $actuals['actual_ink_cost'],
                    'machine' => $actuals['actual_machine_cost'],
                    'labour' => $actuals['actual_labour_cost'],
                    'overhead' => $actuals['actual_overhead_cost'],
                    'total' => $actuals['actual_total_cost'],
                ],
                $estimated['margin_percent'],
                $actuals['actual_margin_percent'],
            );

            $allWarnings = array_values(array_unique(array_merge($warnings, $variance['warnings'])));
            $status = $allWarnings !== [] && $variance['variance_class']->value === 'unknown'
                ? EstimateActualComparisonStatus::ManualReview
                : EstimateActualComparisonStatus::Completed;

            return PrintEstimateActualComparison::query()->updateOrCreate($lookup, [
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'print_quotation_estimate_id' => $estimate?->id,
                'quotation_id' => $quotation?->id ?? $jobCard->quotation_id ?? $estimate?->quotation_id,
                'production_job_card_id' => $jobCard->id,
                'job_cost_sheet_id' => $actuals['job_cost_sheet_id'],
                'production_output_id' => $actuals['production_output_id'],
                'comparison_status' => $status,
                'estimated_material_cost' => $estimated['material'],
                'actual_material_cost' => $actuals['actual_material_cost'],
                'material_cost_variance' => $variance['material']['variance'],
                'material_cost_variance_percent' => $variance['material']['variance_percent'],
                'estimated_ink_cost' => $estimated['ink'],
                'actual_ink_cost' => $actuals['actual_ink_cost'],
                'ink_cost_variance' => $variance['ink']['variance'],
                'ink_cost_variance_percent' => $variance['ink']['variance_percent'],
                'estimated_machine_cost' => $estimated['machine'],
                'actual_machine_cost' => $actuals['actual_machine_cost'],
                'machine_cost_variance' => $variance['machine']['variance'],
                'machine_cost_variance_percent' => $variance['machine']['variance_percent'],
                'estimated_labour_cost' => $estimated['labour'],
                'actual_labour_cost' => $actuals['actual_labour_cost'],
                'labour_cost_variance' => $variance['labour']['variance'],
                'labour_cost_variance_percent' => $variance['labour']['variance_percent'],
                'estimated_overhead_cost' => $estimated['overhead'],
                'actual_overhead_cost' => $actuals['actual_overhead_cost'],
                'overhead_cost_variance' => $variance['overhead']['variance'],
                'overhead_cost_variance_percent' => $variance['overhead']['variance_percent'],
                'estimated_total_cost' => $estimated['total'],
                'actual_total_cost' => $actuals['actual_total_cost'],
                'total_cost_variance' => $variance['total']['variance'],
                'total_cost_variance_percent' => $variance['total']['variance_percent'],
                'recommended_price' => $estimated['recommended_price'],
                'actual_selling_price' => $actuals['actual_selling_price'],
                'estimated_margin_percent' => $estimated['margin_percent'],
                'actual_margin_percent' => $actuals['actual_margin_percent'],
                'margin_variance_percent' => $variance['margin_variance_percent'],
                'accuracy_score' => $variance['accuracy_score'],
                'confidence_score' => $estimated['confidence_score'],
                'variance_class' => $variance['variance_class'],
                'recommendation' => $this->recommendations->recommend($variance, $allWarnings, $status->value),
                'calculation_breakdown' => [
                    'formula_version' => config('printing_intelligence.estimate_actual_formula_version', 'PI6-V1'),
                    'estimated' => $estimated,
                    'actual' => $actuals,
                    'variance' => $variance,
                ],
                'warnings' => $allWarnings,
                'metadata' => [
                    'compared_at' => now()->toIso8601String(),
                ],
                'compared_at' => now(),
            ]);
        } catch (Throwable $exception) {
            return PrintEstimateActualComparison::query()->updateOrCreate($lookup, array_merge($base, [
                'comparison_status' => EstimateActualComparisonStatus::Failed,
                'warnings' => [$exception->getMessage()],
                'compared_at' => now(),
            ]));
        }
    }

    /**
     * @return array{
     *     material: float,
     *     ink: float,
     *     machine: float,
     *     labour: float,
     *     overhead: float,
     *     total: float,
     *     recommended_price: float|null,
     *     margin_percent: float|null,
     *     confidence_score: float|null
     * }
     */
    protected function estimatedCosts(?PrintQuotationEstimate $estimate, ?Quotation $quotation): array
    {
        if ($estimate !== null) {
            return [
                'material' => (float) $estimate->estimated_material_cost,
                'ink' => (float) $estimate->estimated_ink_cost,
                'machine' => (float) $estimate->estimated_machine_cost,
                'labour' => (float) $estimate->estimated_labour_cost,
                'overhead' => (float) $estimate->estimated_overhead_cost,
                'total' => (float) $estimate->estimated_total_cost,
                'recommended_price' => $estimate->recommended_selling_price !== null ? (float) $estimate->recommended_selling_price : null,
                'margin_percent' => $estimate->expected_margin_percent !== null ? (float) $estimate->expected_margin_percent : null,
                'confidence_score' => $estimate->confidence_score !== null ? (float) $estimate->confidence_score : null,
            ];
        }

        if ($quotation !== null && (float) $quotation->estimated_total_cost > 0) {
            return [
                'material' => (float) ($quotation->estimated_material_cost ?? 0),
                'ink' => (float) ($quotation->estimated_ink_cost ?? 0),
                'machine' => (float) ($quotation->estimated_machine_cost ?? 0),
                'labour' => (float) ($quotation->estimated_labour_cost ?? 0),
                'overhead' => (float) ($quotation->estimated_overhead_cost ?? 0),
                'total' => (float) $quotation->estimated_total_cost,
                'recommended_price' => $quotation->recommended_price !== null ? (float) $quotation->recommended_price : null,
                'margin_percent' => $quotation->estimated_margin_percent !== null ? (float) $quotation->estimated_margin_percent : null,
                'confidence_score' => $quotation->confidence_score !== null ? (float) $quotation->confidence_score : null,
            ];
        }

        return [
            'material' => 0,
            'ink' => 0,
            'machine' => 0,
            'labour' => 0,
            'overhead' => 0,
            'total' => 0,
            'recommended_price' => null,
            'margin_percent' => null,
            'confidence_score' => null,
        ];
    }

    protected function resolveJobForEstimate(PrintQuotationEstimate $estimate): ?ProductionJobCard
    {
        if ($estimate->quotation_id) {
            $job = ProductionJobCard::query()
                ->where('quotation_id', $estimate->quotation_id)
                ->latest('id')
                ->first();

            if ($job !== null) {
                return $job;
            }
        }

        $analysis = $estimate->analysis;
        if ($analysis?->production_job_card_id) {
            return ProductionJobCard::query()->find($analysis->production_job_card_id);
        }

        return null;
    }

    protected function resolveEstimateForJob(ProductionJobCard $jobCard): ?PrintQuotationEstimate
    {
        if ($jobCard->quotation_id) {
            $estimate = PrintQuotationEstimate::query()
                ->where('quotation_id', $jobCard->quotation_id)
                ->latest('id')
                ->first();

            if ($estimate !== null) {
                return $estimate;
            }
        }

        return PrintQuotationEstimate::query()
            ->whereHas('analysis', fn ($q) => $q->where('production_job_card_id', $jobCard->id))
            ->latest('id')
            ->first();
    }
}
