<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\EstimateActualComparisonStatus;
use App\Enums\EstimateVarianceClass;
use App\Models\PrintingIntelligence\PrintEstimateActualComparison;
use App\Services\PrintingIntelligence\EstimateAccuracyAnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstimateAccuracyAnalyticsServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\OrganizationFoundationSeeder::class);
    }

    public function test_aggregates_average_accuracy_and_variance_classes(): void
    {
        $company = \App\Models\Company::query()->where('code', 'JANA')->firstOrFail();

        PrintEstimateActualComparison::query()->create([
            'company_id' => $company->id,
            'comparison_status' => EstimateActualComparisonStatus::Completed,
            'estimated_total_cost' => 100,
            'actual_total_cost' => 105,
            'total_cost_variance' => 5,
            'total_cost_variance_percent' => 5,
            'accuracy_score' => 95,
            'variance_class' => EstimateVarianceClass::Accurate,
            'compared_at' => now(),
        ]);

        PrintEstimateActualComparison::query()->create([
            'company_id' => $company->id,
            'comparison_status' => EstimateActualComparisonStatus::Completed,
            'estimated_total_cost' => 100,
            'actual_total_cost' => 150,
            'total_cost_variance' => 50,
            'total_cost_variance_percent' => 50,
            'accuracy_score' => 50,
            'variance_class' => EstimateVarianceClass::Unreliable,
            'compared_at' => now(),
        ]);

        $summary = app(EstimateAccuracyAnalyticsService::class)->aggregate(['company_id' => $company->id]);

        $this->assertSame(2, $summary['comparison_count']);
        $this->assertEqualsWithDelta(72.5, $summary['average_accuracy_score'], 0.01);
        $this->assertSame(1, $summary['accurate_estimates_count']);
        $this->assertSame(1, $summary['unreliable_estimates_count']);
        $this->assertNotEmpty($summary['top_variance_drivers']);
    }
}
