<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\ArtworkAnalysisStatus;
use App\Enums\ColourAnalysisStatus;
use App\Enums\InkEstimationStatus;
use App\Enums\PrintInkType;
use App\Models\Branch;
use App\Models\Company;
use App\Models\PrintingIntelligence\PrintArtworkAnalysis;
use App\Models\PrintingIntelligence\PrintInkProfile;
use App\Services\PrintingIntelligence\InkEstimationService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InkEstimationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        config(['printing_intelligence.ink_costing_enabled' => true]);
    }

    public function test_persists_completed_estimate(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();

        $profile = PrintInkProfile::query()->create([
            'company_id' => $company->id,
            'name' => 'Service Ink',
            'ink_type' => PrintInkType::Cmyk,
            'cartridge_cost' => 5000,
            'estimated_ml' => 1000,
            'estimated_yield_sq_m' => 50,
            'active' => true,
        ]);

        $analysis = PrintArtworkAnalysis::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'original_filename' => 'service-ink.png',
            'stored_filename' => 'service-ink.png',
            'file_path' => 'printing-intelligence/artwork/service-ink.png',
            'disk' => 'local',
            'mime_type' => 'image/png',
            'file_extension' => 'png',
            'file_size_bytes' => 512,
            'file_hash' => hash('sha256', 'service-ink'),
            'analysis_status' => ArtworkAnalysisStatus::Completed,
            'analysis_source' => 'upload',
            'colour_analysis_status' => ColourAnalysisStatus::Completed,
            'page_count' => 1,
            'area_square_m' => 0.05,
            'resolution_dpi' => 300,
            'rgb_coverage_percent' => 50,
            'cyan_coverage_percent' => 12,
            'magenta_coverage_percent' => 12,
            'yellow_coverage_percent' => 12,
            'black_coverage_percent' => 14,
        ]);

        $result = app(InkEstimationService::class)->estimate($analysis, $profile);

        $this->assertContains($result->estimation_status, [
            InkEstimationStatus::Completed,
            InkEstimationStatus::ManualReview,
        ]);
        $this->assertNotNull($result->estimated_total_ml);
        $this->assertSame('PI3-V1', $result->formula_version);
        $this->assertDatabaseHas('print_artwork_ink_estimates', [
            'print_artwork_analysis_id' => $analysis->id,
            'ink_profile_id' => $profile->id,
        ]);
    }
}
