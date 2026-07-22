<?php

namespace Tests\Feature\Assets;

use App\Enums\AssetType;
use App\Enums\DowntimeImpactLevel;
use App\Enums\FixedAssetStatus;
use App\Enums\MaintenanceFrequencyType;
use App\Enums\MaintenancePriority;
use App\Enums\MaintenanceType;
use App\Enums\MaintenanceWorkOrderStatus;
use App\Enums\ProductionMachineStatus;
use App\Models\Assets\AssetCategory;
use App\Models\Assets\AssetDowntimeRecord;
use App\Models\Assets\FixedAsset;
use App\Models\Assets\MachineProfile;
use App\Models\Assets\MaintenancePlan;
use App\Models\Assets\MaintenanceWorkOrder;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Procurement\Vendor;
use App\Models\Production\ProductionJobCard;
use App\Models\User;
use App\Services\Assets\MaintenanceBlockingService;
use App\Services\Assets\MaintenanceDowntimeService;
use App\Services\Assets\MachineAvailabilityService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(PlatformConfigurationSeeder::class);
    }

    public function test_maintenance_dashboard_requires_permission(): void
    {
        $user = User::factory()->create([
            'company_id' => Company::query()->first()->id,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->withHeader('Turbo-Frame', 'module-workspace-content')
            ->get(route('admin.assets.maintenance.dashboard'))
            ->assertForbidden();
    }

    public function test_maintenance_hub_renders_for_authorized_user(): void
    {
        $user = $this->maintenanceManager();

        $this->actingAs($user)
            ->withHeader('Turbo-Frame', 'module-workspace-content')
            ->get(route('admin.assets.maintenance.dashboard'))
            ->assertOk()
            ->assertSee(__('Maintenance Operations'), false)
            ->assertSee(__('Work Orders'), false);
    }

    public function test_work_order_creation_and_numbering(): void
    {
        $user = $this->maintenanceManager();
        $asset = $this->makeAsset();

        $this->actingAs($user)
            ->post(route('admin.assets.maintenance.work-orders.store'), [
                'fixed_asset_id' => $asset->id,
                'maintenance_type' => MaintenanceType::Corrective->value,
                'priority' => MaintenancePriority::High->value,
                'description' => 'Plotter head failure',
            ])
            ->assertRedirect();

        $order = MaintenanceWorkOrder::query()->first();
        $this->assertNotNull($order);
        $this->assertStringStartsWith('MWO-', $order->work_order_no);
        $this->assertSame(MaintenanceWorkOrderStatus::Draft, $order->status);
    }

    public function test_work_order_status_change_and_lifecycle(): void
    {
        $user = $this->maintenanceManager();
        $asset = $this->makeAsset();
        $order = $this->makeWorkOrder($asset, ['priority' => MaintenancePriority::Critical]);

        $this->actingAs($user)
            ->post(route('admin.assets.maintenance.work-orders.open', $order))
            ->assertRedirect();

        $this->assertSame(MaintenanceWorkOrderStatus::Open, $order->fresh()->status);
        $this->assertDatabaseHas('maintenance_work_order_status_histories', [
            'maintenance_work_order_id' => $order->id,
            'to_status' => MaintenanceWorkOrderStatus::Open->value,
        ]);
    }

    public function test_maintenance_plan_creation_and_schedule(): void
    {
        $user = $this->maintenanceManager();
        $asset = $this->makeAsset();

        $this->actingAs($user)
            ->post(route('admin.assets.maintenance.plans.store'), [
                'fixed_asset_id' => $asset->id,
                'plan_name' => 'Monthly Lubrication',
                'frequency_type' => MaintenanceFrequencyType::Monthly->value,
                'frequency_value' => 1,
            ])
            ->assertRedirect(route('admin.assets.maintenance.dashboard', ['tab' => 'plans']));

        $plan = MaintenancePlan::query()->first();
        $this->assertNotNull($plan);
        $this->assertNotNull($plan->next_due_date);
    }

    public function test_downtime_duration_calculation(): void
    {
        $asset = $this->makeAsset();
        $start = now()->subHours(2);
        $end = now();

        $record = app(MaintenanceDowntimeService::class)->record([
            'fixed_asset_id' => $asset->id,
            'start_time' => $start,
            'end_time' => $end,
            'impact_level' => DowntimeImpactLevel::High->value,
            'reason' => 'Emergency repair',
        ], $asset->company_id, $asset->branch_id);

        $this->assertGreaterThanOrEqual(119, $record->duration_minutes);
        $this->assertGreaterThan(1.9, $record->durationHours());
    }

    public function test_production_blocking_from_active_downtime(): void
    {
        $asset = $this->makeAsset();
        $profile = $this->makeMachineProfile($asset);

        AssetDowntimeRecord::query()->create([
            'company_id' => $asset->company_id,
            'branch_id' => $asset->branch_id,
            'fixed_asset_id' => $asset->id,
            'start_time' => now(),
            'impact_level' => DowntimeImpactLevel::Critical,
        ]);

        $this->assertTrue(app(MaintenanceBlockingService::class)->assetBlocksProduction($asset));

        $availability = app(MachineAvailabilityService::class)->evaluate($profile);
        $this->assertSame('unavailable', $availability['state']->value);
    }

    public function test_critical_work_order_blocks_machine_job_assignment(): void
    {
        $user = $this->maintenanceManager();
        $asset = $this->makeAsset();
        $this->makeMachineProfile($asset);
        $order = $this->makeWorkOrder($asset, [
            'maintenance_type' => MaintenanceType::Emergency,
            'priority' => MaintenancePriority::Critical,
            'status' => MaintenanceWorkOrderStatus::InProgress,
            'opened_at' => now(),
        ]);

        AssetDowntimeRecord::query()->create([
            'company_id' => $asset->company_id,
            'branch_id' => $asset->branch_id,
            'fixed_asset_id' => $asset->id,
            'maintenance_work_order_id' => $order->id,
            'start_time' => now(),
            'impact_level' => DowntimeImpactLevel::Critical,
        ]);

        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $asset->company_id,
            'branch_id' => $asset->branch_id,
        ]);

        $user->givePermissionTo('machines.assign');

        $this->actingAs($user)
            ->post(route('admin.production.job-cards.assign-machine', $jobCard), [
                'assigned_machine_asset_id' => $asset->id,
            ])
            ->assertSessionHasErrors('machine');
    }

    public function test_vendor_link_on_work_order(): void
    {
        $user = $this->maintenanceManager();
        $asset = $this->makeAsset();
        $vendor = Vendor::query()->where('company_id', $asset->company_id)->first()
            ?? Vendor::query()->create([
                'company_id' => $asset->company_id,
                'vendor_code' => 'V-MAINT',
                'vendor_name' => 'Repair Contractor',
                'status' => 'active',
            ]);

        $this->actingAs($user)
            ->post(route('admin.assets.maintenance.work-orders.store'), [
                'fixed_asset_id' => $asset->id,
                'maintenance_type' => MaintenanceType::Warranty->value,
                'priority' => MaintenancePriority::Normal->value,
                'vendor_id' => $vendor->id,
            ])
            ->assertRedirect();

        $this->assertSame($vendor->id, MaintenanceWorkOrder::query()->first()->vendor_id);
    }

    public function test_tenant_isolation_on_work_order_show(): void
    {
        $user = $this->maintenanceManager();
        $foreignCompany = Company::query()->create(['name' => 'Foreign Maint', 'code' => 'FM', 'is_active' => true]);
        $foreignCategory = AssetCategory::query()->create([
            'company_id' => $foreignCompany->id,
            'name' => 'Foreign Cat',
            'code' => 'FC',
            'asset_type' => AssetType::Machine->value,
            'useful_life_months' => 60,
            'useful_life_years' => 5,
            'is_active' => true,
        ]);
        $foreignAsset = FixedAsset::query()->create([
            'company_id' => $foreignCompany->id,
            'asset_category_id' => $foreignCategory->id,
            'asset_number' => 'AST-FM-001',
            'asset_name' => 'Foreign Machine',
            'acquisition_date' => now(),
            'acquisition_cost' => 1000,
            'status' => FixedAssetStatus::Active,
        ]);
        $foreignOrder = MaintenanceWorkOrder::query()->create([
            'company_id' => $foreignCompany->id,
            'fixed_asset_id' => $foreignAsset->id,
            'work_order_no' => 'MWO-FM-001',
            'maintenance_type' => MaintenanceType::Corrective,
            'priority' => MaintenancePriority::Normal,
            'status' => MaintenanceWorkOrderStatus::Open,
        ]);

        $response = $this->actingAs($user)
            ->get(route('admin.assets.maintenance.work-orders.show', $foreignOrder));

        $this->assertTrue(in_array($response->status(), [403, 404], true));
    }

    public function test_branch_isolation_on_work_order_listing(): void
    {
        $user = $this->maintenanceManager();
        $company = Company::query()->first();
        $branchA = Branch::query()->where('company_id', $company->id)->first();
        $branchB = Branch::query()->create([
            'company_id' => $company->id,
            'name' => 'Maint Branch B',
            'code' => 'MBB',
            'is_active' => true,
        ]);

        $assetA = $this->makeAsset(['branch_id' => $branchA->id, 'asset_name' => 'Branch A Machine']);
        $assetB = $this->makeAsset(['branch_id' => $branchB->id, 'asset_name' => 'Branch B Machine']);
        $this->makeWorkOrder($assetA, ['work_order_no' => 'MWO-A-001']);
        $this->makeWorkOrder($assetB, ['work_order_no' => 'MWO-B-001']);

        session(['active_branch_id' => $branchB->id]);

        $this->actingAs($user)
            ->withHeader('Turbo-Frame', 'module-workspace-content')
            ->get(route('admin.assets.maintenance.dashboard', ['tab' => 'work-orders']))
            ->assertOk()
            ->assertSee('MWO-B-001', false)
            ->assertDontSee('MWO-A-001', false);
    }

    protected function maintenanceManager(): User
    {
        $user = User::factory()->create([
            'company_id' => Company::query()->first()->id,
            'is_active' => true,
        ]);
        $user->assignRole('Company Admin');

        return $user;
    }

    protected function makeAsset(array $overrides = []): FixedAsset
    {
        $companyId = Company::query()->first()->id;
        $category = AssetCategory::query()->firstOrCreate(
            ['company_id' => $companyId, 'code' => 'MNT'],
            [
                'name' => 'Maintenance Assets',
                'asset_type' => AssetType::Machine->value,
                'useful_life_months' => 60,
                'useful_life_years' => 5,
                'is_active' => true,
            ],
        );

        return FixedAsset::query()->create(array_merge([
            'company_id' => $companyId,
            'branch_id' => Branch::query()->where('company_id', $companyId)->value('id'),
            'asset_category_id' => $category->id,
            'asset_number' => 'AST-MNT-'.fake()->unique()->numerify('####'),
            'asset_name' => 'Konica Press',
            'acquisition_date' => now(),
            'acquisition_cost' => 1000000,
            'status' => FixedAssetStatus::Active,
        ], $overrides));
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function makeWorkOrder(FixedAsset $asset, array $overrides = []): MaintenanceWorkOrder
    {
        return MaintenanceWorkOrder::query()->create(array_merge([
            'company_id' => $asset->company_id,
            'branch_id' => $asset->branch_id,
            'fixed_asset_id' => $asset->id,
            'work_order_no' => 'MWO-'.fake()->unique()->numerify('####'),
            'maintenance_type' => MaintenanceType::Corrective,
            'priority' => MaintenancePriority::Normal,
            'status' => MaintenanceWorkOrderStatus::Draft,
        ], $overrides));
    }

    protected function makeMachineProfile(FixedAsset $asset): MachineProfile
    {
        return MachineProfile::query()->create([
            'company_id' => $asset->company_id,
            'branch_id' => $asset->branch_id,
            'fixed_asset_id' => $asset->id,
            'machine_code' => 'MC-01',
            'machine_type' => 'Digital Press',
            'production_status' => ProductionMachineStatus::Available,
            'shift_capacity' => 10,
            'hourly_capacity' => 2,
        ]);
    }
}
