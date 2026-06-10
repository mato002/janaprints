<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\ArtworkAnalysisStatus;
use App\Enums\ColourAnalysisStatus;
use App\Enums\CoverageClass;
use App\Models\Branch;
use App\Models\Company;
use App\Models\PrintingIntelligence\PrintArtworkAnalysis;
use App\Services\PrintingIntelligence\ColourAnalysisService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ColourAnalysisServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        Storage::fake('local');
        config(['printing_intelligence.storage_disk' => 'local', 'printing_intelligence.colour_analysis_enabled' => true]);
    }

    public function test_transitions_status_correctly_and_stores_values(): void
    {
        [$company, $branch] = $this->tenant();
        $file = UploadedFile::fake()->image('colour-test.png', 80, 80);
        $stored = Storage::disk('local')->putFile('artwork', $file);

        $analysis = PrintArtworkAnalysis::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'original_filename' => 'colour-test.png',
            'stored_filename' => basename((string) $stored),
            'file_path' => (string) $stored,
            'disk' => 'local',
            'mime_type' => 'image/png',
            'file_extension' => 'png',
            'file_size_bytes' => 1000,
            'file_hash' => hash('sha256', 'colour-test'),
            'analysis_status' => ArtworkAnalysisStatus::Completed,
            'analysis_source' => 'upload',
            'colour_analysis_status' => ColourAnalysisStatus::Pending,
            'page_count' => 1,
        ]);

        $result = app(ColourAnalysisService::class)->analyze($analysis);

        $this->assertContains($result->colour_analysis_status, [
            ColourAnalysisStatus::Completed,
            ColourAnalysisStatus::ManualReview,
        ]);
        $this->assertNotNull($result->colour_analyzed_at);
        $this->assertNotNull($result->cmyk_coverage_percent);
        $this->assertDatabaseHas('print_artwork_pages', [
            'print_artwork_analysis_id' => $analysis->id,
            'page_number' => 1,
        ]);
    }

    public function test_handles_failure_safely_when_file_missing(): void
    {
        [$company, $branch] = $this->tenant();

        $analysis = PrintArtworkAnalysis::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'original_filename' => 'missing.png',
            'stored_filename' => 'missing.png',
            'file_path' => 'missing/path.png',
            'disk' => 'local',
            'mime_type' => 'image/png',
            'file_extension' => 'png',
            'file_size_bytes' => 100,
            'file_hash' => hash('sha256', 'missing'),
            'analysis_status' => ArtworkAnalysisStatus::Completed,
            'analysis_source' => 'upload',
            'colour_analysis_status' => ColourAnalysisStatus::Pending,
        ]);

        $result = app(ColourAnalysisService::class)->analyze($analysis);

        $this->assertSame(ColourAnalysisStatus::Failed, $result->colour_analysis_status);
    }

    /**
     * @return array{0: Company, 1: Branch}
     */
    protected function tenant(): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();

        return [$company, $branch];
    }
}
