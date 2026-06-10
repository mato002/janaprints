<?php

namespace App\Services\PrintingIntelligence\Advisor;

use App\Enums\AdvisorRecommendationType;
use App\Enums\AdvisorSeverity;
use App\Services\PrintingIntelligence\MarginLeakageAnalysisService;
use App\Services\PrintingIntelligence\ProductProfitabilityService;
use App\Services\PrintingIntelligence\ProfitabilityAnalyticsService;

class ProfitabilityAdvisorService
{
    public function __construct(
        protected AdvisorConfidenceService $confidence,
    ) {}

    /**
     * @param  array{company_id?: int, branch_id?: int|null, days?: int}  $filters
     * @return list<array<string, mixed>>
     */
    public function recommend(array $filters = []): array
    {
        $companyId = (int) ($filters['company_id'] ?? tenant()->companyId() ?? auth()->user()?->company_id);
        $filters['company_id'] = $companyId;
        $filters['days'] = (int) ($filters['days'] ?? 90);

        $products = app(ProductProfitabilityService::class)->analyze($filters);
        $leakage = app(MarginLeakageAnalysisService::class)->analyze($filters);
        $analytics = app(\App\Services\PrintingIntelligence\ProfitabilityAnalyticsService::class)->summarize($filters);

        $recommendations = [];
        $portfolioMargin = (float) ($analytics['average_margin'] ?? 0);

        $lossJobs = \App\Models\PrintingIntelligence\PrintProfitabilitySnapshot::query()
            ->where('company_id', $companyId)
            ->where('snapshot_type', \App\Enums\ProfitabilitySnapshotType::Job)
            ->where('profitability_class', \App\Enums\ProfitabilityClass::LossMaking)
            ->latest('snapshot_date')
            ->limit(5)
            ->get();

        foreach ($lossJobs as $job) {
            $recommendations[] = AdvisorRecommendationWriter::payload(
                AdvisorRecommendationType::Profitability,
                AdvisorSeverity::Critical,
                'profitability:loss_job:'.$job->production_job_card_id,
                __('Loss-making job'),
                __('Job card #:id margin :pct%.', ['id' => $job->production_job_card_id, 'pct' => $job->gross_margin_percent ?? 0]),
                __('This job completed at a loss — investigate estimate vs actual variance.'),
                'PI8',
                $this->confidence->score(['data_points' => 4, 'signal_strength' => 90]),
                __('Review PI6 comparison for root cause.'),
                'production_job_card',
                $job->production_job_card_id,
                ['margin_percent' => $job->gross_margin_percent],
            );
        }

        $lowest = $products['lowest_margin'] ?? null;
        $highest = $products['highest_margin'] ?? null;
        if ($lowest && $highest && $portfolioMargin > 0) {
            $gap = round($portfolioMargin - (float) ($lowest['margin_percent'] ?? 0), 1);
            if ($gap > 15) {
                $recommendations[] = AdvisorRecommendationWriter::payload(
                    AdvisorRecommendationType::Profitability,
                    AdvisorSeverity::Medium,
                    'profitability:weak_product:'.($lowest['product_key'] ?? 'weak'),
                    __('Weak margin product'),
                    __(':label generates :gap% lower margin than portfolio average.', [
                        'label' => $lowest['product_label'] ?? __('Product'),
                        'gap' => $gap,
                    ]),
                    __('Posters generate materially lower margin than portfolio average — review pricing templates.'),
                    'PI8',
                    $this->confidence->score(['data_points' => 3, 'historical_periods' => 3]),
                    __('Adjust price book or production routing for this product line.'),
                    null,
                    null,
                    ['product' => $lowest, 'portfolio_margin' => $portfolioMargin],
                );
            }
        }

        foreach ($leakage['top_profit_erosion_drivers'] ?? [] as $index => $driver) {
            if ($index >= 3) {
                break;
            }
            $recommendations[] = AdvisorRecommendationWriter::payload(
                AdvisorRecommendationType::Profitability,
                AdvisorSeverity::High,
                'profitability:leakage:'.($driver['category'] ?? $index),
                __('Profit leakage driver'),
                __('Largest variance in :category.', ['category' => $driver['label'] ?? $driver['category'] ?? 'cost']),
                __('Margin leakage detected in :category — calibration or pricing review advised.', ['category' => $driver['label'] ?? '—']),
                'PI8',
                $this->confidence->score(['estimate_accuracy' => 100 - abs($driver['average_variance_percent'] ?? 0)]),
                __('Review PI7 calibration recommendations for this cost category.'),
                null,
                null,
                $driver,
            );
        }

        return $recommendations;
    }
}
