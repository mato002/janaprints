<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Models\Branch;
use App\Models\Company;
use App\Models\PrintingIntelligence\PrintArtworkAnalysis;
use App\Models\PrintingIntelligence\PrintQuotationEstimate;
use App\Models\User;
use App\Services\PrintingIntelligence\Advisor\QuotationAdvisorService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class QuotationAdvisorServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_low_margin_quotation_generates_recommendation(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $analysis = PrintArtworkAnalysis::query()->create([
            'company_id' => $company->id,
            'original_filename' => 'test.pdf',
            'stored_filename' => 'test.pdf',
            'file_path' => 'printing-intelligence/artwork/test.pdf',
            'disk' => 'local',
            'mime_type' => 'application/pdf',
            'file_extension' => 'pdf',
            'file_size_bytes' => 1000,
            'file_hash' => hash('sha256', 'test-advisor'),
            'analysis_status' => \App\Enums\ArtworkAnalysisStatus::Completed,
            'analysis_source' => 'upload',
            'cmyk_coverage_percent' => 70,
        ]);

        PrintQuotationEstimate::query()->create([
            'company_id' => $company->id,
            'print_artwork_analysis_id' => $analysis->id,
            'estimation_status' => \App\Enums\QuotationEstimationStatus::Completed,
            'quantity' => 1,
            'expected_margin_percent' => 10,
            'target_margin_percent' => 35,
            'recommended_selling_price' => 5000,
            'confidence_score' => 55,
            'formula_version' => 'PI5-V1',
        ]);

        $recs = app(QuotationAdvisorService::class)->recommend(['company_id' => $company->id]);

        $this->assertNotEmpty($recs);
        $this->assertTrue(collect($recs)->contains(fn ($r) => str_starts_with($r['rule_code'], 'quotation:low_margin:')));
    }
}
