<?php

namespace Tests\Feature\Admin;

use App\Enums\ClearanceStatus;
use App\Enums\EmploymentStatus;
use App\Enums\ExitStatus;
use App\Enums\ExitType;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Hr\EmployeeCompensation;
use App\Models\Hr\EmployeeExit;
use App\Models\Hr\EmployeeExitClearance;
use App\Models\Hr\LeaveBalance;
use App\Models\Hr\LeaveType;
use App\Models\Hr\PayrollDeduction;
use App\Models\User;
use App\Support\Hr\EmployeeExitService;
use App\Support\Hr\ExitFinalDuesService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ExitManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_dashboard_renders_for_hr_user(): void
    {
        $this->actingAs($this->hrUser())
            ->get(route('admin.hr.exit.dashboard'))
            ->assertOk()
            ->assertSee(__('Exit Management'));
    }

    public function test_exit_workflow_initiates_with_clearance_items(): void
    {
        $hr = $this->hrUser();
        $employee = $this->testEmployee();

        $exit = app(EmployeeExitService::class)->initiate($employee->company_id, [
            'employee_id' => $employee->id,
            'exit_type' => ExitType::Resignation->value,
            'last_working_date' => now()->addWeek()->toDateString(),
            'reason' => 'Personal reasons',
        ], $hr);

        $this->assertDatabaseHas('employee_exits', [
            'employee_id' => $employee->id,
            'exit_type' => ExitType::Resignation->value,
            'status' => ExitStatus::Initiated->value,
        ]);

        $this->assertCount(5, $exit->clearances);
        $this->assertStringStartsWith('EXIT-', $exit->reference);
    }

    public function test_clearance_completion_advances_workflow(): void
    {
        $hr = $this->hrUser();
        $employee = $this->testEmployee();
        $service = app(EmployeeExitService::class);

        $exit = $service->initiate($employee->company_id, [
            'employee_id' => $employee->id,
            'exit_type' => ExitType::Termination->value,
            'last_working_date' => now()->toDateString(),
        ], $hr);

        foreach ($exit->clearances as $clearance) {
            $service->updateClearance($clearance, ClearanceStatus::Cleared, $hr);
        }

        $exit->refresh();
        $this->assertSame(ExitStatus::ClearanceComplete, $exit->status);
    }

    public function test_final_dues_calculation_includes_leave_and_deductions(): void
    {
        $employee = $this->testEmployee();
        $annual = LeaveType::query()->where('code', 'ANNUAL')->firstOrFail();

        LeaveBalance::query()->create([
            'company_id' => $employee->company_id,
            'employee_id' => $employee->id,
            'leave_type_id' => $annual->id,
            'balance_year' => now()->year,
            'opening_balance' => 10,
            'earned' => 5,
            'taken' => 2,
            'pending' => 0,
        ]);

        EmployeeCompensation::query()->create([
            'company_id' => $employee->company_id,
            'employee_id' => $employee->id,
            'basic_salary' => 44000,
            'effective_from' => now()->startOfYear()->toDateString(),
            'is_active' => true,
        ]);

        PayrollDeduction::query()->create([
            'company_id' => $employee->company_id,
            'employee_id' => $employee->id,
            'code' => 'LOAN',
            'name' => 'Loan Recovery',
            'category' => 'loan',
            'amount' => 5000,
            'is_active' => true,
        ]);

        $dues = app(ExitFinalDuesService::class)->calculate($employee, now());

        $this->assertGreaterThan(0, $dues['leave_balance_days']);
        $this->assertGreaterThan(0, $dues['leave_balance_amount']);
        $this->assertGreaterThan(0, $dues['salary_balance']);
        $this->assertSame(5000.0, $dues['deductions_total']);
        $this->assertGreaterThan(0, $dues['net_final_dues']);
    }

    public function test_close_deactivates_employee(): void
    {
        $hr = $this->hrUser();
        $employee = $this->testEmployee();
        $service = app(EmployeeExitService::class);

        $exit = $service->initiate($employee->company_id, [
            'employee_id' => $employee->id,
            'exit_type' => ExitType::Retirement->value,
            'last_working_date' => now()->toDateString(),
        ], $hr);

        foreach ($exit->clearances as $clearance) {
            $service->updateClearance($clearance, ClearanceStatus::Cleared, $hr);
        }

        $service->settle($exit->fresh(), $hr);
        $service->close($exit->fresh(), $hr);

        $employee->refresh();
        $this->assertFalse($employee->is_active);
        $this->assertSame(EmploymentStatus::Terminated, $employee->employment_status);
        $this->assertSame(ExitStatus::Closed, $exit->fresh()->status);
    }

    public function test_viewer_cannot_access_exit_management(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();

        $viewer = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $viewer->assignRole(Role::findByName('Viewer', 'web'));

        $this->actingAs($viewer)
            ->get(route('admin.hr.exit.dashboard'))
            ->assertForbidden();
    }

    public function test_manage_forbidden_without_permission(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $employee = $this->testEmployee();

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);

        $role = Role::findOrCreate('Exit Viewer', 'web');
        Permission::findOrCreate('hr.exit.view', 'web');
        $role->syncPermissions(['hr.exit.view']);
        $user->assignRole($role);

        $this->actingAs($user)
            ->get(route('admin.hr.exit.create'))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('admin.hr.exit.store'), [
                'employee_id' => $employee->id,
                'exit_type' => ExitType::Resignation->value,
                'last_working_date' => now()->toDateString(),
            ])
            ->assertForbidden();
    }

    protected function hrUser(): User
    {
        $user = User::factory()->create([
            'company_id' => Company::query()->where('code', 'JANA')->value('id'),
            'default_branch_id' => Branch::query()->where('code', 'HQ')->value('id'),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $user->assignRole(Role::findByName('HR', 'web'));

        return $user;
    }

    protected function testEmployee(): Employee
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $department = Department::query()->where('company_id', $company->id)->firstOrFail();

        return Employee::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'department_id' => $department->id,
            'employee_number' => 'EMP-EXIT-001',
            'first_name' => 'Exit',
            'last_name' => 'Candidate',
            'email' => 'exit.candidate@janaprints.local',
            'employment_status' => 'active',
            'is_active' => true,
        ]);
    }
}
