<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\EstimateActualComparisonStatus;
use App\Enums\EstimateVarianceClass;
use App\Enums\QuotationEstimationStatus;
use App\Models\PrintingIntelligence\PrintEstimateActualComparison;
use App\Models\PrintingIntelligence\PrintQuotationEstimate;
use App\Models\Sales\Quotation;
use App\Services\PrintingIntelligence\CostAccuracyRecommendationService;
use Tests\TestCase;

class CostAccuracyRecommendationServiceTest extends TestCase
{
    public function test_recommends_ink_profile_review_when_ink_underestimated(): void
    {
        $text = app(CostAccuracyRecommendationService::class)->recommend([
            'variance_class' => EstimateVarianceClass::ModerateVariance,
            'ink' => ['variance_percent' => -18],
            'machine' => ['variance_percent' => 2],
            'material' => ['variance_percent' => 3],
            'overhead' => ['variance_percent' => 1],
        ], []);

        $this->assertStringContainsString('ink profile yield', strtolower($text));
    }

    public function test_recommends_machine_profile_review_when_machine_variance_high(): void
    {
        $text = app(CostAccuracyRecommendationService::class)->recommend([
            'variance_class' => EstimateVarianceClass::MajorVariance,
            'ink' => ['variance_percent' => 2],
            'machine' => ['variance_percent' => 25],
            'material' => ['variance_percent' => 3],
            'overhead' => ['variance_percent' => 1],
        ], []);

        $this->assertStringContainsString('machine', strtolower($text));
    }

    public function test_no_action_when_accurate(): void
    {
        $text = app(CostAccuracyRecommendationService::class)->recommend([
            'variance_class' => EstimateVarianceClass::Accurate,
            'ink' => ['variance_percent' => 2],
            'machine' => ['variance_percent' => 1],
            'material' => ['variance_percent' => 3],
            'overhead' => ['variance_percent' => 1],
        ], []);

        $this->assertStringContainsString('no immediate action', strtolower($text));
    }
}
