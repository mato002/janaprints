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
use App\Models\PrintingIntelligence\PrintArtworkProductionEstimate;
use App\Enums\AssetType;
use App\Enums\FixedAssetStatus;
use App\Services\PrintingIntelligence\PrintingIntelligenceGateway;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GatewayProductionCostContextTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        config(['printing_intelligence.production_costing_enabled' => true]);
    }

    public function test_gateway_returns_production_context_without_file_path(): void
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
            'asset_name' => 'Gateway Press',
            'acquisition_cost' => 100000,
            'acquisition_date' => now()->toDateString(),
            'status' => FixedAssetStatus::Active,
        ]);

        $machine = MachineProfile::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'fixed_asset_id' => $asset->id,
            'machine_code' => 'MC-GW',
            'machine_type' => 'digital_press',
            'cost_per_hour' => 1100,
            'target_output_per_hour' => 1,
        ]);

        $analysis = PrintArtworkAnalysis::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'original_filename' => 'gw-prod.png',
            'stored_filename' => 'gw-prod.png',
            'file_path' => 'printing-intelligence/artwork/gw-prod.png',
            'disk' => 'local',
            'mime_type' => 'image/png',
            'file_extension' => 'png',
            'file_size_bytes' => 512,
            'file_hash' => hash('sha256', 'gw-prod'),
            'analysis_status' => ArtworkAnalysisStatus::Completed,
            'analysis_source' => 'upload',
            'colour_analysis_status' => ColourAnalysisStatus::Completed,
            'area_square_m' => 0.05,
            'page_count' => 1,
            'rgb_coverage_percent' => 30,
        ]);

        PrintArtworkProductionEstimate::query()->create([
            'company_id' => $company->id,
            'print_artwork_analysis_id' => $analysis->id,
            'machine_profile_id' => $machine->id,
            'estimation_status' => ProductionEstimationStatus::Completed,
            'estimated_run_hours' => 2,
            'estimated_total_production_cost' => 4500,
            'confidence_score' => 82,
            'formula_version' => 'PI4-V1',
            'estimated_at' => now(),
        ]);

        $context = app(PrintingIntelligenceGateway::class)->productionCostEstimationContext($analysis);

        $this->assertTrue($context['production_costing_enabled']);
        $this->assertEqualsWithDelta(4500, $context['production_estimate']['estimated_total_production_cost'], 0.01);
        $this->assertSame('MC-GW', $context['machine']['machine_code']);
        $this->assertArrayNotHasKey('file_path', $context);
    }
}
