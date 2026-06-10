<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\ArtworkAnalysisStatus;
use App\Enums\ColourAnalysisStatus;
use App\Enums\PrintInkType;
use App\Models\Branch;
use App\Models\Company;
use App\Models\PrintingIntelligence\PrintArtworkAnalysis;
use App\Models\PrintingIntelligence\PrintInkProfile;
use App\Services\PrintingIntelligence\InkEstimationConfidenceService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InkEstimationConfidenceServiceTest extends TestCase
{
    use RefreshDatabase;

    protected InkEstimationConfidenceService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->service = app(InkEstimationConfidenceService::class);
        config([
            'printing_intelligence.high_confidence_score' => 80,
            'printing_intelligence.minimum_confidence_score' => 40,
        ]);
    }

    public function test_high_confidence(): void
    {
        [$analysis, $profile] = $this->fixtures();

        $result = $this->service->score($analysis, $profile, 5.0);

        $this->assertGreaterThanOrEqual(80, $result['score']);
        $this->assertSame('high', $result['level']);
    }

    public function test_medium_confidence_with_rgb_approximation(): void
    {
        [$analysis, $profile] = $this->fixtures();
        $analysis->cyan_coverage_percent = null;
        $analysis->magenta_coverage_percent = null;
        $analysis->yellow_coverage_percent = null;
        $analysis->black_coverage_percent = null;

        $result = $this->service->score($analysis, $profile, 5.0, [__('CMYK channel split unavailable; using equal RGB approximation.')]);

        $this->assertGreaterThanOrEqual(40, $result['score']);
        $this->assertLessThanOrEqual(80, $result['score']);
        $this->assertContains($result['level'], ['medium', 'high']);
    }

    public function test_low_confidence_when_data_missing(): void
    {
        [$analysis, $profile] = $this->fixtures();
        $analysis->colour_analysis_status = ColourAnalysisStatus::Pending;
        $analysis->width_mm = null;
        $analysis->height_mm = null;
        $analysis->area_square_m = null;
        $analysis->resolution_dpi = null;
        $profile->estimated_yield_sq_m = null;
        $profile->estimated_yield_pages = null;

        $result = $this->service->score($analysis, $profile, null);

        $this->assertLessThan(40, $result['score']);
        $this->assertSame('low', $result['level']);
    }

    /**
     * @return array{0: PrintArtworkAnalysis, 1: PrintInkProfile}
     */
    protected function fixtures(): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();

        $profile = PrintInkProfile::query()->create([
            'company_id' => $company->id,
            'name' => 'CMYK',
            'ink_type' => PrintInkType::Cmyk,
            'cartridge_cost' => 6000,
            'estimated_ml' => 1500,
            'estimated_yield_sq_m' => 80,
            'cost_per_ml' => 4,
            'active' => true,
        ]);

        $analysis = PrintArtworkAnalysis::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'original_filename' => 'confidence.png',
            'stored_filename' => 'confidence.png',
            'file_path' => 'printing-intelligence/artwork/confidence.png',
            'disk' => 'local',
            'mime_type' => 'image/png',
            'file_extension' => 'png',
            'file_size_bytes' => 512,
            'file_hash' => hash('sha256', 'confidence'),
            'analysis_status' => ArtworkAnalysisStatus::Completed,
            'analysis_source' => 'upload',
            'colour_analysis_status' => ColourAnalysisStatus::Completed,
            'page_count' => 1,
            'width_mm' => 210,
            'height_mm' => 297,
            'area_square_m' => 0.06237,
            'resolution_dpi' => 300,
            'rgb_coverage_percent' => 40,
            'cyan_coverage_percent' => 10,
            'magenta_coverage_percent' => 10,
            'yellow_coverage_percent' => 10,
            'black_coverage_percent' => 10,
        ]);

        return [$analysis, $profile];
    }
}
