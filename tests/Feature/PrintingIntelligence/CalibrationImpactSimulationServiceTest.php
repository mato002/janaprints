<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\CalibrationRuleStatus;
use App\Enums\CalibrationRuleType;
use App\Enums\EstimateActualComparisonStatus;
use App\Models\PrintingIntelligence\PrintCalibrationRule;
use App\Models\PrintingIntelligence\PrintEstimateActualComparison;
use App\Models\PrintingIntelligence\PrintQuotationEstimate;
use App\Services\PrintingIntelligence\CalibrationImpactSimulationService;
use Database\Seeders\OrganizationFoundationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalibrationImpactSimulationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_simulates_accuracy_improvement(): void
    {
        $company = \App\Models\Company::query()->where('code', 'JANA')->firstOrFail();

        $estimate = PrintQuotationEstimate::query()->create([
            'company_id' => $company->id,
            'print_artwork_analysis_id' => \App\Models\PrintingIntelligence\PrintArtworkAnalysis::query()->create([
                'company_id' => $company->id,
                'original_filename' => 'sim.png',
                'stored_filename' => 'sim.png',
                'file_path' => 'printing-intelligence/artwork/sim.png',
                'disk' => 'local',
                'mime_type' => 'image/png',
                'file_extension' => 'png',
                'file_size_bytes' => 100,
                'file_hash' => hash('sha256', 'sim'),
                'analysis_status' => \App\Enums\ArtworkAnalysisStatus::Completed,
                'analysis_source' => 'upload',
            ])->id,
            'estimation_status' => \App\Enums\QuotationEstimationStatus::Completed,
            'quantity' => 1,
            'estimated_ink_cost' => 100,
            'estimated_total_cost' => 500,
            'formula_version' => 'PI5-V1',
        ]);

        PrintEstimateActualComparison::query()->create([
            'company_id' => $company->id,
            'print_quotation_estimate_id' => $estimate->id,
            'comparison_status' => EstimateActualComparisonStatus::Completed,
            'estimated_total_cost' => 500,
            'actual_total_cost' => 550,
            'compared_at' => now(),
        ]);

        $rule = PrintCalibrationRule::query()->create([
            'company_id' => $company->id,
            'rule_type' => CalibrationRuleType::InkYield,
            'rule_key' => 'default_cmyk_coverage_factor',
            'current_value' => 1.0,
            'proposed_value' => 1.2,
            'status' => CalibrationRuleStatus::Draft,
        ]);

        $result = app(CalibrationImpactSimulationService::class)->simulate($rule, 90);

        $this->assertSame(1, $result['sample_size']);
        $this->assertNotNull($result['average_accuracy_before']);
        $this->assertTrue($result['advisory']);
    }
}
