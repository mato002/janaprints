<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\ArtworkAnalysisStatus;
use App\Enums\ColourAnalysisStatus;
use App\Enums\InkEstimationStatus;
use App\Enums\PrintInkType;
use App\Models\Branch;
use App\Models\Company;
use App\Models\PrintingIntelligence\PrintArtworkAnalysis;
use App\Models\PrintingIntelligence\PrintArtworkInkEstimate;
use App\Models\PrintingIntelligence\PrintInkProfile;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArtworkInkEstimateCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        config(['printing_intelligence.ink_costing_enabled' => true]);
    }

    public function test_command_processes_pending_analyses(): void
    {
        $this->createAnalysis();

        $this->artisan('printing:artwork:estimate-ink', ['--limit' => 5])
            ->assertSuccessful();

        $this->assertDatabaseHas('print_artwork_ink_estimates', [
            'print_artwork_analysis_id' => PrintArtworkAnalysis::query()->value('id'),
        ]);
    }

    public function test_dry_run_does_not_mutate(): void
    {
        $this->createAnalysis();

        $this->artisan('printing:artwork:estimate-ink', ['--dry-run' => true, '--limit' => 5])
            ->assertSuccessful();

        $this->assertDatabaseCount('print_artwork_ink_estimates', 0);
    }

    protected function createAnalysis(): PrintArtworkAnalysis
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();

        PrintInkProfile::query()->create([
            'company_id' => $company->id,
            'name' => 'Command Ink',
            'ink_type' => PrintInkType::Cmyk,
            'cartridge_cost' => 4000,
            'estimated_ml' => 1000,
            'estimated_yield_sq_m' => 50,
            'active' => true,
        ]);

        return PrintArtworkAnalysis::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'original_filename' => 'cmd-ink.png',
            'stored_filename' => 'cmd-ink.png',
            'file_path' => 'printing-intelligence/artwork/cmd-ink.png',
            'disk' => 'local',
            'mime_type' => 'image/png',
            'file_extension' => 'png',
            'file_size_bytes' => 256,
            'file_hash' => hash('sha256', 'cmd-ink'),
            'analysis_status' => ArtworkAnalysisStatus::Completed,
            'analysis_source' => 'upload',
            'colour_analysis_status' => ColourAnalysisStatus::Completed,
            'page_count' => 1,
            'area_square_m' => 0.05,
            'resolution_dpi' => 300,
            'rgb_coverage_percent' => 60,
            'cyan_coverage_percent' => 15,
            'magenta_coverage_percent' => 15,
            'yellow_coverage_percent' => 15,
            'black_coverage_percent' => 15,
        ]);
    }
}
