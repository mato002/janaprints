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
use App\Models\User;
use App\Enums\AssetType;
use App\Enums\FixedAssetStatus;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ArtworkProductionEstimateWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        config(['printing_intelligence.production_costing_enabled' => true]);
    }

    public function test_production_section_visible_on_detail(): void
    {
        [$company, $branch, $user, $analysis] = $this->setupWithEstimate();

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->get(route('admin.printing-intelligence.artwork-analysis.show', $analysis))
            ->assertOk()
            ->assertSee(__('Production estimate'))
            ->assertSee(__('Total production cost'))
            ->assertSee('MC-UI');
    }

    public function test_run_production_estimation_action_works(): void
    {
        [$company, $branch, $user, $analysis] = $this->setupAnalysisOnly();

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->post(route('admin.printing-intelligence.artwork-analysis.estimate-production', $analysis))
            ->assertRedirect();

        $this->assertNotNull(
            PrintArtworkProductionEstimate::query()->where('print_artwork_analysis_id', $analysis->id)->value('estimated_at'),
        );
    }

    public function test_permissions_enforced(): void
    {
        [$company, $branch, $user, $analysis] = $this->setupAnalysisOnly([
            'printing.intelligence.view',
            'printing.artwork.analyze',
        ]);

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->post(route('admin.printing-intelligence.artwork-analysis.estimate-production', $analysis))
            ->assertForbidden();
    }

    /**
     * @param  list<string>|null  $permissions
     * @return array{0: Company, 1: Branch, 2: User, 3: PrintArtworkAnalysis}
     */
    protected function setupAnalysisOnly(?array $permissions = null): array
    {
        $permissions ??= [
            'printing.intelligence.view',
            'printing.artwork.analyze',
            'printing.artwork.estimate-production',
        ];

        [$company, $branch, $user] = $this->userWith($permissions);

        $asset = FixedAsset::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'asset_category_id' => AssetCategory::query()->firstOrCreate(
                ['company_id' => $company->id, 'code' => 'MCH'],
                ['name' => 'Machines', 'asset_type' => AssetType::Machine->value, 'useful_life_months' => 84, 'is_active' => true],
            )->id,
            'asset_number' => 'MA-'.uniqid(),
            'asset_name' => 'UI Press',
            'acquisition_cost' => 100000,
            'acquisition_date' => now()->toDateString(),
            'status' => FixedAssetStatus::Active,
        ]);

        MachineProfile::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'fixed_asset_id' => $asset->id,
            'machine_code' => 'MC-UI',
            'machine_type' => 'digital_press',
            'target_output_per_hour' => 1,
            'cost_per_hour' => 1000,
        ]);

        $analysis = PrintArtworkAnalysis::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'original_filename' => 'ui-prod.png',
            'stored_filename' => 'ui-prod.png',
            'file_path' => 'printing-intelligence/artwork/ui-prod.png',
            'disk' => 'local',
            'mime_type' => 'image/png',
            'file_extension' => 'png',
            'file_size_bytes' => 256,
            'file_hash' => hash('sha256', 'ui-prod'),
            'analysis_status' => ArtworkAnalysisStatus::Completed,
            'analysis_source' => 'upload',
            'colour_analysis_status' => ColourAnalysisStatus::Completed,
            'page_count' => 1,
            'area_square_m' => 0.04,
            'rgb_coverage_percent' => 50,
        ]);

        return [$company, $branch, $user, $analysis];
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User, 3: PrintArtworkAnalysis}
     */
    protected function setupWithEstimate(): array
    {
        [$company, $branch, $user, $analysis] = $this->setupAnalysisOnly();
        $machine = MachineProfile::query()->where('company_id', $company->id)->firstOrFail();

        PrintArtworkProductionEstimate::query()->create([
            'company_id' => $company->id,
            'print_artwork_analysis_id' => $analysis->id,
            'machine_profile_id' => $machine->id,
            'estimation_status' => ProductionEstimationStatus::Completed,
            'estimated_run_hours' => 1.5,
            'estimated_machine_cost' => 1800,
            'estimated_labour_cost' => 900,
            'estimated_total_production_cost' => 3200,
            'confidence_score' => 78,
            'formula_version' => 'PI4-V1',
            'estimated_at' => now(),
        ]);

        return [$company, $branch, $user, $analysis];
    }

    /**
     * @param  list<string>  $permissions
     * @return array{0: Company, 1: Branch, 2: User}
     */
    protected function userWith(array $permissions): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        Role::findByName('Storekeeper', 'web')->syncPermissions($permissions);
        $user->assignRole('Storekeeper');

        return [$company, $branch, $user];
    }
}
