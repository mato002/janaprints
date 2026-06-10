<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\QuotationEstimationStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\PrintingIntelligence\PrintArtworkAnalysis;
use App\Models\PrintingIntelligence\PrintQuotationEstimate;
use App\Services\PrintingIntelligence\PrintingIntelligenceGateway;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GatewayQuotationEstimateContextTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        config(['printing_intelligence.quotation_estimation_enabled' => true]);
    }

    public function test_gateway_returns_quotation_estimate_context(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();

        $analysis = PrintArtworkAnalysis::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'original_filename' => 'gw-quote.png',
            'stored_filename' => 'gw-quote.png',
            'file_path' => 'printing-intelligence/artwork/gw-quote.png',
            'disk' => 'local',
            'mime_type' => 'image/png',
            'file_extension' => 'png',
            'file_size_bytes' => 512,
            'file_hash' => hash('sha256', 'gw-quote'),
            'analysis_status' => \App\Enums\ArtworkAnalysisStatus::Completed,
            'analysis_source' => 'upload',
        ]);

        PrintQuotationEstimate::query()->create([
            'company_id' => $company->id,
            'print_artwork_analysis_id' => $analysis->id,
            'estimation_status' => QuotationEstimationStatus::Completed,
            'quantity' => 1,
            'estimated_total_cost' => 650,
            'recommended_selling_price' => 900,
            'confidence_score' => 82,
            'formula_version' => 'PI5-V1',
        ]);

        $context = app(PrintingIntelligenceGateway::class)->quotationEstimateContext($analysis, 1);

        $this->assertTrue($context['quotation_estimation_enabled']);
        $this->assertEqualsWithDelta(900, $context['quotation_estimate']['recommended_selling_price'], 0.01);
        $this->assertArrayNotHasKey('file_path', $context);
    }
}
