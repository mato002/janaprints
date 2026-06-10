<?php

namespace App\Services\PrintingIntelligence\Advisor;

use App\Enums\AdvisorRecommendationType;
use App\Enums\AdvisorSeverity;
use App\Models\PrintingIntelligence\PrintQuotationEstimate;
use App\Services\PrintingIntelligence\EstimateAccuracyAnalyticsService;

class QuotationAdvisorService
{
    public function __construct(
        protected AdvisorConfidenceService $confidence,
    ) {}

    /**
     * @param  array{company_id?: int, branch_id?: int|null}  $filters
     * @return list<array<string, mixed>>
     */
    public function recommend(array $filters = []): array
    {
        $companyId = (int) ($filters['company_id'] ?? tenant()->companyId() ?? auth()->user()?->company_id);
        $branchId = $filters['branch_id'] ?? null;
        $accuracy = app(EstimateAccuracyAnalyticsService::class)->aggregate(['company_id' => $companyId]);
        $targetMargin = (float) config('printing_intelligence.default_target_margin_percent', 35);

        $estimates = PrintQuotationEstimate::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->latest('id')
            ->limit(50)
            ->with('analysis')
            ->get();

        $recommendations = [];

        foreach ($estimates as $estimate) {
            $margin = (float) ($estimate->expected_margin_percent ?? 0);
            $target = (float) ($estimate->target_margin_percent ?? $targetMargin);
            $gap = round($target - $margin, 2);

            if ($gap > 5) {
                $recommendations[] = AdvisorRecommendationWriter::payload(
                    AdvisorRecommendationType::Quotation,
                    $gap > 15 ? AdvisorSeverity::High : AdvisorSeverity::Medium,
                    "quotation:low_margin:{$estimate->id}",
                    __('Low margin warning'),
                    __('Estimate #:id margin is :margin% vs target :target%.', ['id' => $estimate->id, 'margin' => $margin, 'target' => $target]),
                    __('Estimated margin is below target by :gap%. Review pricing or production assumptions before sending.', ['gap' => $gap]),
                    'PI5',
                    $this->confidence->score(['data_points' => 4, 'required_points' => 4, 'signal_strength' => min(100, $gap * 4)]),
                    __('Review recommended selling price and wastage assumptions.'),
                    PrintQuotationEstimate::class,
                    $estimate->id,
                    ['expected_margin' => $margin, 'target_margin' => $target, 'gap' => $gap],
                );
            }

            if ($margin < 10 && (float) $estimate->recommended_selling_price > 0) {
                $recommendations[] = AdvisorRecommendationWriter::payload(
                    AdvisorRecommendationType::Quotation,
                    AdvisorSeverity::Critical,
                    "quotation:underpriced:{$estimate->id}",
                    __('Underpriced quotation warning'),
                    __('Estimate #:id expected margin :margin% is critically low.', ['id' => $estimate->id, 'margin' => $margin]),
                    __('This quotation estimate appears underpriced relative to portfolio targets.'),
                    'PI5',
                    $this->confidence->score(['data_points' => 3, 'required_points' => 3, 'signal_strength' => 90]),
                    __('Escalate to sales manager before customer submission.'),
                    PrintQuotationEstimate::class,
                    $estimate->id,
                    ['expected_margin' => $margin],
                );
            }

            if (($estimate->confidence_score ?? 100) < 60) {
                $recommendations[] = AdvisorRecommendationWriter::payload(
                    AdvisorRecommendationType::Quotation,
                    AdvisorSeverity::Medium,
                    "quotation:low_confidence:{$estimate->id}",
                    __('Confidence warning'),
                    __('Estimate #:id confidence :score%.', ['id' => $estimate->id, 'score' => $estimate->confidence_score]),
                    __('Quotation estimate confidence is below acceptable threshold — validate ink and machine inputs.'),
                    'PI5',
                    (float) ($estimate->confidence_score ?? 50),
                    __('Re-run ink and production estimates.'),
                    PrintQuotationEstimate::class,
                    $estimate->id,
                    ['confidence_score' => $estimate->confidence_score],
                );
            }

            $analysis = $estimate->analysis;
            $coverage = (float) ($analysis?->cmyk_coverage_percent ?? $analysis?->rgb_coverage_percent ?? 0);
            if ($coverage > 60) {
                $recommendations[] = AdvisorRecommendationWriter::payload(
                    AdvisorRecommendationType::Quotation,
                    AdvisorSeverity::High,
                    "quotation:high_ink_density:{$estimate->id}",
                    __('High ink density warning'),
                    __('Artwork ink density :pct% on estimate #:id.', ['pct' => $coverage, 'id' => $estimate->id]),
                    __('Artwork ink density places this quote in high-risk category for cost overrun.'),
                    'PI3',
                    $this->confidence->score(['data_points' => 2, 'required_points' => 2, 'signal_strength' => min(100, $coverage)]),
                    __('Review ink estimate and consider premium pricing.'),
                    PrintQuotationEstimate::class,
                    $estimate->id,
                    ['coverage_percent' => $coverage],
                );
            }

            if ($estimate->material_inventory_item_id === null) {
                $recommendations[] = AdvisorRecommendationWriter::payload(
                    AdvisorRecommendationType::Quotation,
                    AdvisorSeverity::Low,
                    "quotation:material_risk:{$estimate->id}",
                    __('High material risk warning'),
                    __('Estimate #:id has no material item selected.', ['id' => $estimate->id]),
                    __('Material cost was not resolved from inventory — quote accuracy may be unreliable.'),
                    'PI5',
                    $this->confidence->score(['data_points' => 1, 'required_points' => 3]),
                    __('Select material from inventory before applying estimate.'),
                    PrintQuotationEstimate::class,
                    $estimate->id,
                );
            }
        }

        if (($accuracy['average_accuracy_score'] ?? 100) < 70) {
            $recommendations[] = AdvisorRecommendationWriter::payload(
                AdvisorRecommendationType::Quotation,
                AdvisorSeverity::Medium,
                'quotation:portfolio_accuracy',
                __('Portfolio quote accuracy warning'),
                __('Average quote accuracy :score%.', ['score' => $accuracy['average_accuracy_score']]),
                __('Historical quotation accuracy is below 70% — treat new quotes with additional scrutiny.'),
                'PI6',
                $this->confidence->score(['estimate_accuracy' => $accuracy['average_accuracy_score'], 'historical_periods' => 3]),
                __('Review PI6 variance drivers before large quotes.'),
            );
        }

        return $recommendations;
    }
}
