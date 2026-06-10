<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\EstimateActualComparisonStatus;
use App\Models\PrintingIntelligence\PrintEstimateActualComparison;
use App\Services\PrintingIntelligence\MarginLeakageAnalysisService;
use Database\Seeders\OrganizationFoundationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarginLeakageAnalysisServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_identifies_top_profit_erosion_drivers(): void
    {
        $company = \App\Models\Company::query()->where('code', 'JANA')->firstOrFail();

        PrintEstimateActualComparison::query()->create([
            'company_id' => $company->id,
            'comparison_status' => EstimateActualComparisonStatus::Completed,
            'estimated_total_cost' => 500,
            'actual_total_cost' => 650,
            'ink_cost_variance' => 120,
            'ink_cost_variance_percent' => 25,
            'material_cost_variance' => 20,
            'machine_cost_variance' => 10,
            'compared_at' => now(),
        ]);

        PrintEstimateActualComparison::query()->create([
            'company_id' => $company->id,
            'comparison_status' => EstimateActualComparisonStatus::Completed,
            'estimated_total_cost' => 400,
            'actual_total_cost' => 520,
            'ink_cost_variance' => 80,
            'ink_cost_variance_percent' => 20,
            'compared_at' => now(),
        ]);

        $result = app(MarginLeakageAnalysisService::class)->analyze(['company_id' => $company->id]);

        $this->assertSame(2, $result['comparison_count']);
        $this->assertNotEmpty($result['top_profit_erosion_drivers']);
        $this->assertSame('ink', $result['top_profit_erosion_drivers'][0]['category']);
    }
}
