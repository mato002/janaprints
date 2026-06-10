<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\EstimateActualComparisonStatus;
use App\Enums\EstimateVarianceClass;
use App\Models\PrintingIntelligence\PrintCalibrationRule;
use App\Models\PrintingIntelligence\PrintEstimateActualComparison;
use Database\Seeders\OrganizationFoundationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecommendCalibrationCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(OrganizationFoundationSeeder::class);
        config([
            'printing_intelligence.calibration_recommendation_enabled' => true,
            'printing_intelligence.calibration_min_sample_size' => 2,
            'printing_intelligence.calibration_ink_variance_trigger_percent' => 10,
        ]);
    }

    public function test_command_generates_recommendations(): void
    {
        $company = \App\Models\Company::query()->where('code', 'JANA')->firstOrFail();

        for ($i = 0; $i < 3; $i++) {
            PrintEstimateActualComparison::query()->create([
                'company_id' => $company->id,
                'comparison_status' => EstimateActualComparisonStatus::Completed,
                'ink_cost_variance_percent' => 20,
                'estimated_total_cost' => 100,
                'actual_total_cost' => 120,
                'accuracy_score' => 80,
                'variance_class' => EstimateVarianceClass::ModerateVariance,
                'compared_at' => now(),
            ]);
        }

        $this->artisan('printing:calibration:recommend', ['--company' => $company->id])
            ->assertSuccessful();

        $this->assertGreaterThan(0, PrintCalibrationRule::query()->where('company_id', $company->id)->count());
    }

    public function test_dry_run_does_not_persist(): void
    {
        $company = \App\Models\Company::query()->where('code', 'JANA')->firstOrFail();

        for ($i = 0; $i < 3; $i++) {
            PrintEstimateActualComparison::query()->create([
                'company_id' => $company->id,
                'comparison_status' => EstimateActualComparisonStatus::Completed,
                'ink_cost_variance_percent' => 25,
                'estimated_total_cost' => 100,
                'actual_total_cost' => 125,
                'compared_at' => now(),
            ]);
        }

        $this->artisan('printing:calibration:recommend', [
            '--company' => $company->id,
            '--dry-run' => true,
        ])->assertSuccessful();

        $this->assertSame(0, PrintCalibrationRule::query()->count());
    }
}
