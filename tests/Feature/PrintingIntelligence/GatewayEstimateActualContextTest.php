<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\EstimateActualComparisonStatus;
use App\Enums\EstimateVarianceClass;
use App\Enums\QuotationEstimationStatus;
use App\Models\PrintingIntelligence\PrintEstimateActualComparison;
use App\Models\PrintingIntelligence\PrintQuotationEstimate;
use App\Services\PrintingIntelligence\PrintingIntelligenceGateway;
use Database\Seeders\OrganizationFoundationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GatewayEstimateActualContextTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_gateway_returns_comparison_context(): void
    {
        $company = \App\Models\Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = \App\Models\Branch::query()->where('company_id', $company->id)->firstOrFail();

        $analysis = \App\Models\PrintingIntelligence\PrintArtworkAnalysis::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'original_filename' => 'gw.png',
            'stored_filename' => 'gw.png',
            'file_path' => 'printing-intelligence/artwork/gw.png',
            'disk' => 'local',
            'mime_type' => 'image/png',
            'file_extension' => 'png',
            'file_size_bytes' => 100,
            'file_hash' => hash('sha256', 'gw'),
            'analysis_status' => \App\Enums\ArtworkAnalysisStatus::Completed,
            'analysis_source' => 'upload',
        ]);

        $estimate = PrintQuotationEstimate::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'print_artwork_analysis_id' => $analysis->id,
            'estimation_status' => QuotationEstimationStatus::Completed,
            'quantity' => 1,
            'estimated_total_cost' => 500,
            'recommended_selling_price' => 750,
            'confidence_score' => 80,
            'formula_version' => 'PI5-V1',
        ]);

        PrintEstimateActualComparison::query()->create([
            'company_id' => $company->id,
            'print_quotation_estimate_id' => $estimate->id,
            'comparison_status' => EstimateActualComparisonStatus::Completed,
            'estimated_total_cost' => 500,
            'actual_total_cost' => 520,
            'total_cost_variance' => 20,
            'total_cost_variance_percent' => 4,
            'accuracy_score' => 96,
            'variance_class' => EstimateVarianceClass::Accurate,
            'recommendation' => __('No action needed.'),
            'compared_at' => now(),
        ]);

        $context = app(PrintingIntelligenceGateway::class)->estimateActualContext($estimate->fresh());

        $this->assertSame($estimate->id, $context['estimate_id']);
        $this->assertNotNull($context['comparison']);
        $this->assertEqualsWithDelta(96, $context['comparison']['accuracy_score'], 0.01);
        $this->assertSame('accurate', $context['comparison']['variance_class']);
    }

    public function test_analytics_context_returns_summary(): void
    {
        $company = \App\Models\Company::query()->where('code', 'JANA')->firstOrFail();

        PrintEstimateActualComparison::query()->create([
            'company_id' => $company->id,
            'comparison_status' => EstimateActualComparisonStatus::Completed,
            'estimated_total_cost' => 100,
            'actual_total_cost' => 102,
            'total_cost_variance_percent' => 2,
            'accuracy_score' => 98,
            'variance_class' => EstimateVarianceClass::Accurate,
            'compared_at' => now(),
        ]);

        $context = app(PrintingIntelligenceGateway::class)->accuracyAnalyticsContext(['company_id' => $company->id]);

        $this->assertSame('PI6-V1', $context['formula_version']);
        $this->assertSame(1, $context['summary']['comparison_count']);
    }
}
