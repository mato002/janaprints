<?php

namespace Tests\Feature\Assets;

use App\Enums\AssetAssignmentStatus;
use App\Enums\AssetBranchTransferStatus;
use App\Enums\AssetCustodyStatus;
use App\Enums\AssetHandoverStatus;
use App\Enums\AssetPhysicalCondition;
use App\Enums\AssetReturnCondition;
use App\Enums\AssetType;
use App\Enums\FixedAssetStatus;
use App\Models\Assets\AssetAssignmentHistory;
use App\Models\Assets\AssetBranchTransfer;
use App\Models\Assets\AssetConditionHistory;
use App\Models\Assets\AssetCustodyTimelineEntry;
use App\Models\Assets\AssetHandover;
use App\Models\Assets\AssetReturn;
use App\Models\Assets\AssetCategory;
use App\Models\Assets\FixedAsset;
use App\Models\Assets\MachineOperatorAssignment;
use App\Models\Assets\VehicleDriverAssignment;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use App\Services\Assets\AssetCustodyAssignmentService;
use App\Services\Assets\MachineOperatorService;
use App\Services\Assets\VehicleDriverAssignmentService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetCustodyAccountabilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(PlatformConfigurationSeeder::class);
    }

    public function test_custody_dashboard_requires_permission(): void
    {
        $user = User::factory()->create([
            'company_id' => Company::query()->first()->id,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('admin.assets.custody.dashboard'))
            ->assertForbidden();
    }

    public function test_employee_assignment_creation(): void
    {
        $user = $this->custodyManager();
        $asset = $this->makeAsset();
        $employee = $this->makeEmployee();

        $this->actingAs($user)
            ->post(route('admin.assets.custody.assignments.store'), [
                'fixed_asset_id' => $asset->id,
                'assignment_type' => 'employee',
                'assigned_to_employee_id' => $employee->id,
                'expected_return_date' => now()->addMonth()->toDateString(),
                'assignment_reason' => 'Field laptop',
                'condition' => AssetPhysicalCondition::Good->value,
            ])
            ->assertRedirect(route('admin.assets.custody.assignments.index'));

        $asset->refresh();
        $this->assertSame($employee->id, $asset->assigned_to_employee_id);
        $this->assertSame(AssetCustodyStatus::Assigned, $asset->custody_status);

        $history = AssetAssignmentHistory::query()->where('fixed_asset_id', $asset->id)->latest('id')->first();
        $this->assertNotNull($history);
        $this->assertSame(AssetAssignmentStatus::Assigned, $history->status);
        $this->assertSame($employee->id, $history->assigned_to_employee_id);
    }

    public function test_department_assignment(): void
    {
        $user = $this->custodyManager();
        $asset = $this->makeAsset();
        $department = $this->makeDepartment();

        app(AssetCustodyAssignmentService::class)->assignToDepartment($asset, [
            'assigned_to_department_id' => $department->id,
            'assignment_reason' => 'Design pool',
        ], $user->id);

        $asset->refresh();
        $this->assertSame($department->id, $asset->assigned_to_department_id);
        $this->assertDatabaseHas('asset_assignment_histories', [
            'fixed_asset_id' => $asset->id,
            'assigned_to_department_id' => $department->id,
            'status' => AssetAssignmentStatus::Assigned->value,
        ]);
    }

    public function test_handover_workflow_and_numbering(): void
    {
        $user = $this->custodyManager();
        $asset = $this->makeAsset();
        $from = $this->makeEmployee('Jane', 'Doe');
        $to = $this->makeEmployee('John', 'Smith');

        $this->actingAs($user)
            ->post(route('admin.assets.custody.handovers.store'), [
                'fixed_asset_id' => $asset->id,
                'from_employee_id' => $from->id,
                'to_employee_id' => $to->id,
                'handover_date' => now()->toDateString(),
                'condition' => AssetPhysicalCondition::Good->value,
            ])
            ->assertRedirect();

        $handover = AssetHandover::query()->first();
        $this->assertNotNull($handover);
        $this->assertStringStartsWith('AHO-', $handover->handover_no);
        $this->assertSame(AssetHandoverStatus::Draft, $handover->status);

        $this->actingAs($user)
            ->post(route('admin.assets.custody.handovers.submit', $handover))
            ->assertRedirect();

        $this->assertSame(AssetHandoverStatus::PendingAcceptance, $handover->fresh()->status);

        $this->actingAs($user)
            ->post(route('admin.assets.custody.handovers.accept', $handover))
            ->assertRedirect();

        $handover->refresh();
        $this->assertSame(AssetHandoverStatus::Accepted, $handover->status);
        $this->assertSame($to->id, $handover->asset->fresh()->assigned_to_employee_id);
    }

    public function test_return_workflow_and_condition_tracking(): void
    {
        $user = $this->custodyManager();
        $asset = $this->makeAsset();
        $employee = $this->makeEmployee();

        app(AssetCustodyAssignmentService::class)->assignToEmployee($asset, [
            'assigned_to_employee_id' => $employee->id,
        ], $user->id);

        $this->actingAs($user)
            ->post(route('admin.assets.custody.returns.store'), [
                'fixed_asset_id' => $asset->id,
                'return_date' => now()->toDateString(),
                'condition' => AssetReturnCondition::Fair->value,
                'returned_by' => $employee->id,
                'notes' => 'Minor wear',
            ])
            ->assertRedirect(route('admin.assets.custody.returns.index'));

        $asset->refresh();
        $this->assertNull($asset->assigned_to_employee_id);
        $this->assertSame(AssetCustodyStatus::Returned, $asset->custody_status);
        $this->assertSame(AssetPhysicalCondition::Fair, $asset->current_condition);

        $this->assertDatabaseHas('asset_returns', ['fixed_asset_id' => $asset->id]);
        $this->assertDatabaseHas('asset_condition_histories', [
            'fixed_asset_id' => $asset->id,
            'condition' => AssetPhysicalCondition::Fair->value,
        ]);
    }

    public function test_branch_transfer_workflow(): void
    {
        $user = $this->custodyManager();
        $asset = $this->makeAsset();
        $toBranch = Branch::query()->create([
            'company_id' => $asset->company_id,
            'name' => 'West Branch',
            'code' => 'WST',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->post(route('admin.assets.custody.transfers.store'), [
                'fixed_asset_id' => $asset->id,
                'to_branch_id' => $toBranch->id,
                'transfer_reason' => 'Capacity rebalancing',
                'condition' => AssetPhysicalCondition::Good->value,
            ])
            ->assertRedirect();

        $transfer = AssetBranchTransfer::query()->first();
        $this->assertNotNull($transfer);
        $this->assertStringStartsWith('ABT-', $transfer->transfer_no);

        $this->actingAs($user)
            ->post(route('admin.assets.custody.transfers.accept', $transfer))
            ->assertRedirect();

        $asset->refresh();
        $this->assertSame($toBranch->id, $asset->branch_id);
        $this->assertSame(AssetBranchTransferStatus::Accepted, $transfer->fresh()->status);
    }

    public function test_machine_operator_assignment(): void
    {
        $user = $this->custodyManager();
        $asset = $this->makeAsset();
        $employee = $this->makeEmployee();

        $assignment = app(MachineOperatorService::class)->assign($asset, [
            'employee_id' => $employee->id,
            'is_primary' => true,
        ], $user->id);

        $this->assertTrue($assignment->is_primary);
        $this->assertDatabaseHas('machine_operator_assignments', [
            'fixed_asset_id' => $asset->id,
            'employee_id' => $employee->id,
        ]);
    }

    public function test_vehicle_driver_assignment(): void
    {
        $user = $this->custodyManager();
        $category = $this->makeCategory(AssetType::Vehicle);
        $asset = $this->makeAsset($category);
        $employee = $this->makeEmployee();

        $assignment = app(VehicleDriverAssignmentService::class)->assign($asset, [
            'employee_id' => $employee->id,
            'license_number' => 'DL-12345',
        ], $user->id);

        $this->assertSame('DL-12345', $assignment->license_number);
        $this->assertSame($employee->id, $asset->fresh()->assigned_to_employee_id);
    }

    public function test_custody_timeline_on_asset_detail(): void
    {
        $user = $this->custodyManager();
        $asset = $this->makeAsset();
        $employee = $this->makeEmployee();

        app(AssetCustodyAssignmentService::class)->assignToEmployee($asset, [
            'assigned_to_employee_id' => $employee->id,
        ], $user->id);

        $this->assertGreaterThan(0, AssetCustodyTimelineEntry::query()->where('fixed_asset_id', $asset->id)->count());

        $this->actingAs($user)
            ->get(route('admin.assets.show', $asset))
            ->assertOk()
            ->assertSee(__('Custody Timeline'));
    }

    public function test_tenant_isolation_on_custody_routes(): void
    {
        $user = $this->custodyManager();
        $otherCompany = Company::query()->create([
            'name' => 'Other Co',
            'code' => 'OTH',
            'is_active' => true,
        ]);
        $otherCategory = AssetCategory::query()->create([
            'company_id' => $otherCompany->id,
            'name' => 'Other',
            'code' => 'OTH',
            'asset_type' => AssetType::Computer->value,
            'is_active' => true,
        ]);
        $otherAsset = FixedAsset::query()->create([
            'company_id' => $otherCompany->id,
            'asset_category_id' => $otherCategory->id,
            'asset_number' => 'AST-OTHER-001',
            'asset_name' => 'Foreign Asset',
            'acquisition_date' => now(),
            'acquisition_cost' => 1000,
            'status' => FixedAssetStatus::Active,
        ]);

        $this->actingAs($user)
            ->post(route('admin.assets.custody.assignments.store'), [
                'fixed_asset_id' => $otherAsset->id,
                'assignment_type' => 'employee',
                'assigned_to_employee_id' => $this->makeEmployee()->id,
            ])
            ->assertNotFound();
    }

    public function test_assign_permission_required(): void
    {
        $viewer = User::factory()->create([
            'company_id' => Company::query()->first()->id,
            'is_active' => true,
        ]);
        $viewer->assignRole('Viewer');
        $asset = $this->makeAsset();

        $this->actingAs($viewer)
            ->post(route('admin.assets.custody.assignments.store'), [
                'fixed_asset_id' => $asset->id,
                'assignment_type' => 'employee',
                'assigned_to_employee_id' => $this->makeEmployee()->id,
            ])
            ->assertForbidden();
    }

    protected function custodyManager(): User
    {
        $company = Company::query()->first();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'is_active' => true,
        ]);
        $user->assignRole('Company Admin');

        return $user;
    }

    protected function makeCategory(?AssetType $type = null): AssetCategory
    {
        return AssetCategory::query()->create([
            'company_id' => Company::query()->first()->id,
            'name' => 'Equipment',
            'code' => 'EQP',
            'asset_type' => ($type ?? AssetType::Computer)->value,
            'useful_life_months' => 36,
            'is_active' => true,
        ]);
    }

    protected function makeAsset(?AssetCategory $category = null): FixedAsset
    {
        $category ??= $this->makeCategory();

        return FixedAsset::query()->create([
            'company_id' => $category->company_id,
            'branch_id' => Branch::query()->where('company_id', $category->company_id)->value('id'),
            'asset_category_id' => $category->id,
            'asset_number' => 'AST-'.fake()->unique()->numerify('####'),
            'asset_name' => 'Test Asset',
            'acquisition_date' => now(),
            'acquisition_cost' => 50000,
            'status' => FixedAssetStatus::Active,
            'current_condition' => AssetPhysicalCondition::Good,
            'custody_status' => AssetCustodyStatus::Unassigned,
        ]);
    }

    protected function makeEmployee(string $first = 'Test', string $last = 'Employee'): Employee
    {
        $company = Company::query()->first();

        return Employee::query()->create([
            'company_id' => $company->id,
            'branch_id' => Branch::query()->where('company_id', $company->id)->value('id'),
            'employee_number' => 'EMP-'.uniqid(),
            'first_name' => $first,
            'last_name' => $last,
            'is_active' => true,
        ]);
    }

    protected function makeDepartment(): Department
    {
        return Department::query()->create([
            'company_id' => Company::query()->first()->id,
            'name' => 'Design',
            'code' => 'DES',
            'is_active' => true,
        ]);
    }
}
