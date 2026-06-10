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
use App\Services\PrintingIntelligence\MachineSelectionService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MachineSelectionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        config(['printing_intelligence.labour_hourly_rate' => 500]);
    }

    public function test_selects_machine_with_highest_score(): void
    {
        [$analysis, $primary, $secondary] = $this->fixtures();

        $result = app(MachineSelectionService::class)->select($analysis, 1);

        $this->assertNotNull($result['selected']);
        $this->assertSame($primary->id, $result['selected']['machine_profile_id']);
    }

    /**
     * @return array{0: PrintArtworkAnalysis, 1: MachineProfile, 2: MachineProfile}
     */
    protected function fixtures(): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();

        $primary = $this->makeMachine($company, $branch, [
            'machine_code' => 'MC-PRIMARY',
            'is_primary_production_machine' => true,
            'target_output_per_hour' => 1,
            'cost_per_hour' => 800,
        ]);

        $secondary = $this->makeMachine($company, $branch, [
            'machine_code' => 'MC-SECONDARY',
            'is_primary_production_machine' => false,
            'target_output_per_hour' => 0.5,
            'cost_per_hour' => 1500,
        ]);

        $analysis = PrintArtworkAnalysis::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'original_filename' => 'select.png',
            'stored_filename' => 'select.png',
            'file_path' => 'printing-intelligence/artwork/select.png',
            'disk' => 'local',
            'mime_type' => 'image/png',
            'file_extension' => 'png',
            'file_size_bytes' => 512,
            'file_hash' => hash('sha256', 'select'),
            'analysis_status' => ArtworkAnalysisStatus::Completed,
            'analysis_source' => 'upload',
            'colour_analysis_status' => ColourAnalysisStatus::Completed,
            'page_count' => 1,
            'area_square_m' => 0.08,
            'rgb_coverage_percent' => 30,
        ]);

        return [$analysis, $primary, $secondary];
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function makeMachine(Company $company, Branch $branch, array $overrides = []): MachineProfile
    {
        $asset = FixedAsset::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'asset_category_id' => AssetCategory::query()->firstOrCreate(
                ['company_id' => $company->id, 'code' => 'MCH'],
                ['name' => 'Machines', 'asset_type' => AssetType::Machine->value, 'useful_life_months' => 84, 'is_active' => true],
            )->id,
            'asset_number' => 'MA-'.uniqid(),
            'asset_name' => 'Select Press',
            'acquisition_cost' => 100000,
            'acquisition_date' => now()->toDateString(),
            'status' => FixedAssetStatus::Active,
        ]);

        return MachineProfile::query()->create(array_merge([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'fixed_asset_id' => $asset->id,
            'machine_type' => 'digital_press',
        ], $overrides));
    }
}
