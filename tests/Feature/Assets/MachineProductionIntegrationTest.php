<?php

namespace Tests\Feature\Assets;

use App\Enums\AssetType;
use App\Enums\FixedAssetStatus;
use App\Enums\ProductionMachineStatus;
use App\Models\Assets\AssetCategory;
use App\Models\Assets\FixedAsset;
use App\Models\Assets\MachineProfile;
use App\Models\Assets\MachineTimelineEntry;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\WorkCenter;
use App\Models\User;
use App\Services\Assets\MachineCapacityService;
use App\Services\Assets\MachineDashboardService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\ProductionFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MachineProductionIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(ProductionFoundationSeeder::class);
    }

    public function test_machines_index_requires_permission(): void
    {
        $user = User::factory()->create([
            'company_id' => Company::query()->first()->id,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('admin.assets.machines.index'))
            ->assertForbidden();
    }

    public function test_machine_creation_and_dashboard_metrics(): void
    {
        $user = $this->productionManager();
        $asset = $this->makeMachineAsset();

        $this->actingAs($user)
            ->post(route('admin.assets.machines.activate', $asset), [
                'machine_code' => 'DIG-01',
                'machine_type' => 'Digital Press',
                'shift_capacity' => 20,
                'hourly_capacity' => 5,
            ])
            ->assertRedirect(route('admin.assets.machines.show', $asset));

        $profile = MachineProfile::query()->where('fixed_asset_id', $asset->id)->first();
        $this->assertNotNull($profile);
        $this->assertSame('DIG-01', $profile->machine_code);
        $this->assertSame(ProductionMachineStatus::Available, $profile->production_status);

        $dashboard = app(MachineDashboardService::class)->build($asset->company_id);
        $this->assertSame(1, $dashboard['total_machines']);
        $this->assertSame(1, $dashboard['available_machines']);

        $this->actingAs($user)
            ->get(route('admin.assets.machines.dashboard'))
            ->assertOk()
            ->assertSee(__('Total Machines'), false);
    }

    public function test_machine_capacity_service_metrics(): void
    {
        $asset = $this->makeMachineAsset();
        $profile = $this->makeMachineProfile($asset, ['shift_capacity' => 10, 'hourly_capacity' => 2]);

        $metrics = app(MachineCapacityService::class)->profileMetrics($profile);

        $this->assertSame(10.0, $metrics['shift_capacity']);
        $this->assertSame(0, $metrics['assigned_jobs']);
        $this->assertSame(10.0, $metrics['capacity_remaining']);
    }

    public function test_work_center_machine_link(): void
    {
        $user = $this->productionManager();
        $asset = $this->makeMachineAsset();
        $profile = $this->makeMachineProfile($asset);
        $workCenter = WorkCenter::query()->where('company_id', $asset->company_id)->firstOrFail();

        $this->actingAs($user)
            ->post(route('admin.assets.machines.work-center', $asset), [
                'work_center_id' => $workCenter->id,
            ])
            ->assertRedirect();

        $this->assertSame($asset->id, $workCenter->fresh()->fixed_asset_id);
        $this->assertDatabaseHas('machine_timeline_entries', [
            'fixed_asset_id' => $asset->id,
            'event_type' => 'work_center_assigned',
        ]);
    }

    public function test_job_card_machine_assignment(): void
    {
        $user = $this->productionManager();
        $asset = $this->makeMachineAsset();
        $this->makeMachineProfile($asset, ['shift_capacity' => 20]);
        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $asset->company_id,
            'branch_id' => $asset->branch_id,
        ]);

        $this->actingAs($user)
            ->post(route('admin.production.job-cards.assign-machine', $jobCard), [
                'assigned_machine_asset_id' => $asset->id,
            ])
            ->assertRedirect();

        $this->assertSame($asset->id, $jobCard->fresh()->assigned_machine_asset_id);
        $this->assertDatabaseHas('machine_job_assignments', [
            'fixed_asset_id' => $asset->id,
            'production_job_card_id' => $jobCard->id,
        ]);
    }

    public function test_machine_status_change_logs_timeline(): void
    {
        $user = $this->productionManager();
        $asset = $this->makeMachineAsset();
        $this->makeMachineProfile($asset);

        $this->actingAs($user)
            ->post(route('admin.assets.machines.status', $asset), [
                'production_status' => ProductionMachineStatus::Running->value,
            ])
            ->assertRedirect();

        $this->assertSame(
            ProductionMachineStatus::Running,
            $asset->machineProfile->fresh()->production_status,
        );
        $this->assertTrue(
            MachineTimelineEntry::query()
                ->where('fixed_asset_id', $asset->id)
                ->where('event_type', 'status_changed')
                ->exists(),
        );
    }

    public function test_tenant_isolation_on_machine_show(): void
    {
        $user = $this->productionManager();
        $foreignCompany = Company::query()->create([
            'name' => 'Foreign Machines Co',
            'code' => 'FMC',
            'is_active' => true,
        ]);
        $foreignCategory = AssetCategory::query()->create([
            'company_id' => $foreignCompany->id,
            'name' => 'Foreign Machines',
            'code' => 'FM',
            'asset_type' => AssetType::Machine->value,
            'useful_life_months' => 60,
            'useful_life_years' => 5,
            'is_active' => true,
        ]);
        $foreignAsset = FixedAsset::query()->create([
            'company_id' => $foreignCompany->id,
            'asset_category_id' => $foreignCategory->id,
            'asset_number' => 'AST-FM-001',
            'asset_name' => 'Foreign Press',
            'acquisition_date' => now(),
            'acquisition_cost' => 1000,
            'status' => FixedAssetStatus::Active,
        ]);
        MachineProfile::query()->create([
            'company_id' => $foreignCompany->id,
            'fixed_asset_id' => $foreignAsset->id,
            'machine_code' => 'FOR-01',
            'machine_type' => 'Offset',
        ]);

        $this->actingAs($user)
            ->get(route('admin.assets.machines.show', $foreignAsset))
            ->assertForbidden();
    }

    public function test_branch_isolation_on_machine_listing(): void
    {
        $user = $this->productionManager();
        $company = Company::query()->first();
        $branchA = Branch::query()->where('company_id', $company->id)->first();
        $branchB = Branch::query()->create([
            'company_id' => $company->id,
            'name' => 'Machine Branch B',
            'code' => 'MBB',
            'is_active' => true,
        ]);

        $assetA = $this->makeMachineAsset(['branch_id' => $branchA->id, 'asset_name' => 'Branch A Press']);
        $assetB = $this->makeMachineAsset(['branch_id' => $branchB->id, 'asset_name' => 'Branch B Press']);
        $this->makeMachineProfile($assetA, ['machine_code' => 'A-01']);
        $this->makeMachineProfile($assetB, ['machine_code' => 'B-01']);

        session(['active_branch_id' => $branchB->id]);

        $this->actingAs($user)
            ->get(route('admin.assets.machines.index'))
            ->assertOk()
            ->assertSee('Branch B Press', false)
            ->assertDontSee('Branch A Press', false);
    }

    protected function productionManager(): User
    {
        $user = User::factory()->create([
            'company_id' => Company::query()->first()->id,
            'is_active' => true,
        ]);
        $user->assignRole('Production');

        return $user;
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function makeMachineAsset(array $overrides = []): FixedAsset
    {
        $companyId = Company::query()->first()->id;
        $category = AssetCategory::query()->firstOrCreate(
            ['company_id' => $companyId, 'code' => 'MCH'],
            [
                'name' => 'Machines',
                'asset_type' => AssetType::Machine->value,
                'useful_life_months' => 84,
                'useful_life_years' => 7,
                'is_active' => true,
            ],
        );

        return FixedAsset::query()->create(array_merge([
            'company_id' => $companyId,
            'branch_id' => Branch::query()->where('company_id', $companyId)->value('id'),
            'asset_category_id' => $category->id,
            'asset_number' => 'AST-MCH-'.fake()->unique()->numerify('####'),
            'asset_name' => 'Heidelberg Offset',
            'acquisition_date' => now(),
            'acquisition_cost' => 5000000,
            'status' => FixedAssetStatus::Active,
        ], $overrides));
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function makeMachineProfile(FixedAsset $asset, array $overrides = []): MachineProfile
    {
        return MachineProfile::query()->create(array_merge([
            'company_id' => $asset->company_id,
            'branch_id' => $asset->branch_id,
            'fixed_asset_id' => $asset->id,
            'machine_code' => 'MC-'.fake()->unique()->numerify('###'),
            'machine_type' => 'Offset Press',
            'production_status' => ProductionMachineStatus::Available,
            'shift_capacity' => 10,
            'hourly_capacity' => 2,
        ], $overrides));
    }
}
