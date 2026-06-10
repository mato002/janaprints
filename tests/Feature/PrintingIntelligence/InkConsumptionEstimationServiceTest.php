<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\ArtworkAnalysisStatus;
use App\Enums\ColourAnalysisStatus;
use App\Enums\PrintInkType;
use App\Models\Branch;
use App\Models\Company;
use App\Models\PrintingIntelligence\PrintArtworkAnalysis;
use App\Models\PrintingIntelligence\PrintInkProfile;
use App\Services\PrintingIntelligence\InkConsumptionEstimationService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InkConsumptionEstimationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        config([
            'printing_intelligence.default_formula_version' => 'PI3-V1',
            'printing_intelligence.default_cmyk_coverage_factor' => 1.0,
        ]);
    }

    public function test_estimates_cmyk_ml_and_total_ml(): void
    {
        [$company, $branch, $analysis, $profile] = $this->fixtures();

        $result = app(InkConsumptionEstimationService::class)->estimate($analysis, $profile);

        $this->assertGreaterThan(0, $result['estimated_cyan_ml']);
        $this->assertGreaterThan(0, $result['estimated_magenta_ml']);
        $this->assertGreaterThan(0, $result['estimated_yellow_ml']);
        $this->assertGreaterThan(0, $result['estimated_black_ml']);
        $this->assertGreaterThan(0, $result['estimated_total_ml']);
        $this->assertSame('PI3-V1', $result['formula_version']);
    }

    public function test_stores_formula_version_in_metadata(): void
    {
        [$company, $branch, $analysis, $profile] = $this->fixtures();

        $result = app(InkConsumptionEstimationService::class)->estimate($analysis, $profile);

        $this->assertArrayHasKey('ml_per_sq_m_at_full_coverage', $result['metadata']);
        $this->assertArrayHasKey('coverage_factor', $result['metadata']);
    }

    /**
     * @return array{0: Company, 1: Branch, 2: PrintArtworkAnalysis, 3: PrintInkProfile}
     */
    protected function fixtures(): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();

        $profile = PrintInkProfile::query()->create([
            'company_id' => $company->id,
            'name' => 'CMYK Set',
            'ink_type' => PrintInkType::Cmyk,
            'cartridge_cost' => 8000,
            'estimated_ml' => 2000,
            'estimated_yield_sq_m' => 100,
            'active' => true,
        ]);

        $analysis = PrintArtworkAnalysis::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'original_filename' => 'ink-estimate.png',
            'stored_filename' => 'ink-estimate.png',
            'file_path' => 'printing-intelligence/artwork/ink-estimate.png',
            'disk' => 'local',
            'mime_type' => 'image/png',
            'file_extension' => 'png',
            'file_size_bytes' => 1024,
            'file_hash' => hash('sha256', 'ink-estimate'),
            'analysis_status' => ArtworkAnalysisStatus::Completed,
            'analysis_source' => 'upload',
            'colour_analysis_status' => ColourAnalysisStatus::Completed,
            'page_count' => 1,
            'width_mm' => 210,
            'height_mm' => 297,
            'area_square_m' => 0.06237,
            'resolution_dpi' => 300,
            'rgb_coverage_percent' => 50,
            'cmyk_coverage_percent' => 40,
            'cyan_coverage_percent' => 20,
            'magenta_coverage_percent' => 30,
            'yellow_coverage_percent' => 25,
            'black_coverage_percent' => 15,
        ]);

        return [$company, $branch, $analysis, $profile];
    }
}
