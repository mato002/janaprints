<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\ArtworkAnalysisStatus;
use App\Enums\ColourAnalysisStatus;
use App\Enums\CoverageClass;
use App\Enums\InkEstimationStatus;
use App\Enums\PrintInkType;
use App\Models\Branch;
use App\Models\Company;
use App\Models\PrintingIntelligence\PrintArtworkAnalysis;
use App\Models\PrintingIntelligence\PrintArtworkInkEstimate;
use App\Models\PrintingIntelligence\PrintInkProfile;
use App\Services\PrintingIntelligence\PrintingIntelligenceGateway;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GatewayInkEstimateContextTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        config(['printing_intelligence.ink_costing_enabled' => true]);
    }

    public function test_gateway_returns_ink_estimate_context_without_file_path(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();

        $profile = PrintInkProfile::query()->create([
            'company_id' => $company->id,
            'name' => 'Gateway Ink',
            'ink_type' => PrintInkType::Cmyk,
            'cartridge_cost' => 2000,
            'estimated_ml' => 500,
            'estimated_yield_sq_m' => 25,
            'active' => true,
        ]);

        $analysis = PrintArtworkAnalysis::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'original_filename' => 'gateway-ink.png',
            'stored_filename' => 'gateway-ink.png',
            'file_path' => 'printing-intelligence/artwork/gateway-ink.png',
            'disk' => 'local',
            'mime_type' => 'image/png',
            'file_extension' => 'png',
            'file_size_bytes' => 512,
            'file_hash' => hash('sha256', 'gateway-ink'),
            'analysis_status' => ArtworkAnalysisStatus::Completed,
            'analysis_source' => 'upload',
            'colour_analysis_status' => ColourAnalysisStatus::Completed,
            'coverage_class' => CoverageClass::Medium,
            'cmyk_coverage_percent' => 35,
            'rgb_coverage_percent' => 40,
            'area_square_m' => 0.05,
            'page_count' => 1,
        ]);

        PrintArtworkInkEstimate::query()->create([
            'company_id' => $company->id,
            'print_artwork_analysis_id' => $analysis->id,
            'ink_profile_id' => $profile->id,
            'estimation_status' => InkEstimationStatus::Completed,
            'estimated_total_ml' => 8.25,
            'estimated_ink_cost' => 33.0,
            'confidence_score' => 85,
            'formula_version' => 'PI3-V1',
            'warnings' => [],
            'estimated_at' => now(),
        ]);

        $context = app(PrintingIntelligenceGateway::class)->inkEstimationContext($analysis);

        $this->assertTrue($context['ink_costing_enabled']);
        $this->assertSame('medium', $context['coverage']['coverage_class']);
        $this->assertEqualsWithDelta(8.25, $context['ink_estimate']['estimated_total_ml'], 0.01);
        $this->assertEqualsWithDelta(33.0, $context['ink_estimate']['estimated_ink_cost'], 0.01);
        $this->assertSame('Gateway Ink', $context['ink_profile']['name']);
        $this->assertArrayNotHasKey('file_path', $context);
    }
}
