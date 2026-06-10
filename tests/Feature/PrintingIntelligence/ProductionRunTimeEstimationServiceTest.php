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
use App\Services\PrintingIntelligence\ProductionRunTimeEstimationService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionRunTimeEstimationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        config(['printing_intelligence.production_formula_version' => 'PI4-V1']);
    }

    public function test_estimates_run_hours_from_area_and_output_rate(): void
    {
        [$analysis, $machine] = $this->fixtures();

        $result = app(ProductionRunTimeEstimationService::class)->estimate($analysis, $machine, 1);

        $this->assertSame('PI4-V1', $result['formula_version']);
        $this->assertGreaterThan(0, $result['estimated_run_hours']);
        $this->assertGreaterThan(0, $result['total_area_sq_m']);
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
            'asset_name' => 'Run Time Press',
            'acquisition_cost' => 100000,
            'acquisition_date' => now()->toDateString(),
            'status' => FixedAssetStatus::Active,
        ]);

        $machine = MachineProfile::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'fixed_asset_id' => $asset->id,
            'machine_code' => 'MC-RUN-'.uniqid(),
            'machine_type' => 'digital_press',
            'target_output_per_hour' => 0.5,
            'cost_per_hour' => 1000,
        ]);

        $analysis = PrintArtworkAnalysis::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'original_filename' => 'runtime.png',
            'stored_filename' => 'runtime.png',
            'file_path' => 'printing-intelligence/artwork/runtime.png',
            'disk' => 'local',
            'mime_type' => 'image/png',
            'file_extension' => 'png',
            'file_size_bytes' => 512,
            'file_hash' => hash('sha256', 'runtime'),
            'analysis_status' => ArtworkAnalysisStatus::Completed,
            'analysis_source' => 'upload',
            'colour_analysis_status' => ColourAnalysisStatus::Completed,
            'page_count' => 1,
            'area_square_m' => 0.1,
            'rgb_coverage_percent' => 40,
        ]);

        return [$analysis, $machine];
    }
}
