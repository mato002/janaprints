<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\CalibrationRuleStatus;
use App\Enums\CalibrationRuleType;
use App\Enums\EstimateActualComparisonStatus;
use App\Enums\EstimateVarianceClass;
use App\Models\PrintingIntelligence\PrintEstimateActualComparison;
use App\Services\PrintingIntelligence\CalibrationRecommendationService;
use Database\Seeders\OrganizationFoundationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalibrationRecommendationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(OrganizationFoundationSeeder::class);
        config([
            'printing_intelligence.calibration_recommendation_enabled' => true,
            'printing_intelligence.calibration_min_sample_size' => 3,
            'printing_intelligence.calibration_ink_variance_trigger_percent' => 10,
        ]);
    }

    public function test_generates_ink_recommendation_from_pi6_comparisons(): void
    {
        $company = \App\Models\Company::query()->where('code', 'JANA')->firstOrFail();

        for ($i = 0; $i < 5; $i++) {
            PrintEstimateActualComparison::query()->create([
                'company_id' => $company->id,
                'comparison_status' => EstimateActualComparisonStatus::Completed,
                'estimated_ink_cost' => 100,
                'actual_ink_cost' => 130,
                'ink_cost_variance' => 30,
                'ink_cost_variance_percent' => 30,
                'estimated_total_cost' => 500,
                'actual_total_cost' => 530,
                'accuracy_score' => 70,
                'variance_class' => EstimateVarianceClass::MajorVariance,
                'compared_at' => now(),
            ]);
        }

        $rules = app(CalibrationRecommendationService::class)->generate($company->id, 90, true);

        $this->assertNotEmpty($rules);
        $this->assertSame(CalibrationRuleType::InkYield, $rules[0]->rule_type);
        $this->assertSame(CalibrationRuleStatus::Draft, $rules[0]->status);
    }
}
