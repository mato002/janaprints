<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\ArtworkAnalysisStatus;
use App\Enums\ColourAnalysisStatus;
use App\Models\Assets\AssetCategory;
use App\Models\Assets\FixedAsset;
use App\Models\Assets\MachineProfile;
use App\Models\Branch;
use App\Models\Company;
use App\Models\PrintingIntelligence\PrintArtworkAnalysis;
use App\Enums\AssetType;
use App\Enums\FixedAssetStatus;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArtworkProductionEstimateCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        config(['printing_intelligence.production_costing_enabled' => true]);
    }

    public function test_command_processes_analyses(): void
    {
        $this->createAnalysis();

        $this->artisan('printing:artwork:estimate-production', ['--limit' => 5])
            ->assertSuccessful();

        $this->assertDatabaseHas('print_artwork_production_estimates', [
            'print_artwork_analysis_id' => PrintArtworkAnalysis::query()->value('id'),
        ]);
    }

    public function test_dry_run_does_not_mutate(): void
    {
        $this->createAnalysis();

        $this->artisan('printing:artwork:estimate-production', ['--dry-run' => true, '--limit' => 5])
            ->assertSuccessful();

        $this->assertDatabaseCount('print_artwork_production_estimates', 0);
    }

    protected function createAnalysis(): PrintArtworkAnalysis
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();

        $asset = FixedAsset::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'asset_category_id' => AssetCategory::query()->firstOrCreate(
                ['company_id' => $company->id, 'code' => 'MCH'],
                ['name' => 'Machines', 'asset_type' => AssetType::Machine->value, 'useful_life_months' => 84, 'is_active' => true],
            )->id,
            'asset_number' => 'MA-'.uniqid(),
            'asset_name' => 'Cmd Press',
            'acquisition_cost' => 100000,
            'acquisition_date' => now()->toDateString(),
            'status' => FixedAssetStatus::Active,
        ]);

        MachineProfile::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'fixed_asset_id' => $asset->id,
            'machine_code' => 'MC-CMD',
            'machine_type' => 'digital_press',
            'target_output_per_hour' => 1,
            'cost_per_hour' => 900,
        ]);

        return PrintArtworkAnalysis::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'original_filename' => 'cmd-prod.png',
            'stored_filename' => 'cmd-prod.png',
            'file_path' => 'printing-intelligence/artwork/cmd-prod.png',
            'disk' => 'local',
            'mime_type' => 'image/png',
            'file_extension' => 'png',
            'file_size_bytes' => 256,
            'file_hash' => hash('sha256', 'cmd-prod'),
            'analysis_status' => ArtworkAnalysisStatus::Completed,
            'analysis_source' => 'upload',
            'colour_analysis_status' => ColourAnalysisStatus::Completed,
            'page_count' => 1,
            'area_square_m' => 0.05,
            'rgb_coverage_percent' => 45,
        ]);
    }
}
