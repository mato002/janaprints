<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\ArtworkAnalysisStatus;
use App\Enums\ColourAnalysisStatus;
use App\Enums\ProductionEstimationStatus;
use App\Models\Assets\AssetCategory;
use App\Models\Assets\FixedAsset;
use App\Models\Assets\MachineProfile;
use App\Models\Branch;
use App\Models\Company;
use App\Models\PrintingIntelligence\PrintArtworkAnalysis;
use App\Enums\AssetType;
use App\Enums\FixedAssetStatus;
use App\Services\PrintingIntelligence\ProductionEstimationService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionEstimationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        config(['printing_intelligence.production_costing_enabled' => true]);
    }

    public function test_persists_production_estimate(): void
    {
        [$analysis, $machine] = $this->fixtures();

        $result = app(ProductionEstimationService::class)->estimate($analysis, $machine);

        $this->assertContains($result->estimation_status, [
            ProductionEstimationStatus::Completed,
            ProductionEstimationStatus::ManualReview,
        ]);
        $this->assertSame($machine->id, $result->machine_profile_id);
        $this->assertSame('PI4-V1', $result->formula_version);
        $this->assertDatabaseHas('print_artwork_production_estimates', [
            'print_artwork_analysis_id' => $analysis->id,
        ]);
    }

    /**
     * @return array{0: PrintArtworkAnalysis, 1: MachineProfile}
     */
    protected function fixtures(): array
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
            'asset_name' => 'Prod Press',
            'acquisition_cost' => 100000,
            'acquisition_date' => now()->toDateString(),
            'status' => FixedAssetStatus::Active,
        ]);

        $machine = MachineProfile::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'fixed_asset_id' => $asset->id,
            'machine_code' => 'MC-PROD',
            'machine_type' => 'digital_press',
            'target_output_per_hour' => 0.8,
            'cost_per_hour' => 1200,
            'power_rating_kw' => 2,
            'average_setup_minutes' => 20,
        ]);

        $analysis = PrintArtworkAnalysis::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'original_filename' => 'prod-est.png',
            'stored_filename' => 'prod-est.png',
            'file_path' => 'printing-intelligence/artwork/prod-est.png',
            'disk' => 'local',
            'mime_type' => 'image/png',
            'file_extension' => 'png',
            'file_size_bytes' => 512,
            'file_hash' => hash('sha256', 'prod-est'),
            'analysis_status' => ArtworkAnalysisStatus::Completed,
            'analysis_source' => 'upload',
            'colour_analysis_status' => ColourAnalysisStatus::Completed,
            'page_count' => 1,
            'area_square_m' => 0.06,
            'rgb_coverage_percent' => 35,
        ]);

        return [$analysis, $machine];
    }
}
