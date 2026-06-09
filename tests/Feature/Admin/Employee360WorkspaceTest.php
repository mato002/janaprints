<?php

namespace Tests\Feature\Admin;

use App\Enums\CompensationStatus;
use App\Enums\EmployeeDocumentCategory;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Hr\EmployeeCompensation;
use App\Models\Hr\EmployeeDocument;
use App\Models\Hr\LeaveBalance;
use App\Models\Hr\LeaveType;
use App\Models\User;
use App\Support\Hr\Employee360WorkspaceService;
use App\Support\Hr\EmployeeTimelineService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class Employee360WorkspaceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_overview_loads(): void
    {
        $employee = $this->employeeWithCompensation();

        $this->actingAs($this->hrUser())
            ->get(route('admin.hr.employees.show', $employee))
            ->assertOk()
            ->assertSee($employee->full_name)
            ->assertSee($employee->employee_number)
            ->assertSee(__('Overview'))
            ->assertSee(__('Timeline'));
    }

    public function test_attendance_tab_renders(): void
    {
        $employee = $this->employeeWithCompensation();

        $this->actingAs($this->hrUser())
            ->get(route('admin.hr.employees.show', ['employee' => $employee, 'tab' => 'attendance']))
            ->assertOk()
            ->assertSee(__('Attendance Register'));
    }

    public function test_leave_tab_renders_with_balances(): void
    {
        $employee = $this->employeeWithCompensation();
        $leaveType = LeaveType::query()->where('company_id', $employee->company_id)->first();

        LeaveBalance::query()->create([
            'company_id' => $employee->company_id,
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'balance_year' => now()->year,
            'opening_balance' => 21,
            'earned' => 0,
            'taken' => 2,
            'pending' => 0,
        ]);

        $this->actingAs($this->hrUser())
            ->get(route('admin.hr.employees.show', ['employee' => $employee, 'tab' => 'leave']))
            ->assertOk()
            ->assertSee(__('Leave Balances'));
    }

    public function test_payroll_tab_renders(): void
    {
        $employee = $this->employeeWithCompensation();

        $this->actingAs($this->hrUser())
            ->get(route('admin.hr.employees.show', ['employee' => $employee, 'tab' => 'payroll']))
            ->assertOk()
            ->assertSee(__('Payslips'));
    }

    public function test_documents_tab_renders(): void
    {
        $employee = $this->employeeWithCompensation();

        EmployeeDocument::query()->create([
            'company_id' => $employee->company_id,
            'employee_id' => $employee->id,
            'title' => 'Employment Contract',
            'category' => EmployeeDocumentCategory::Contract,
            'uploaded_by_user_id' => $this->hrUser()->id,
        ]);

        $this->actingAs($this->hrUser())
            ->get(route('admin.hr.employees.show', ['employee' => $employee, 'tab' => 'documents']))
            ->assertOk()
            ->assertSee('Employment Contract');
    }

    public function test_performance_tab_renders(): void
    {
        $employee = $this->employeeWithCompensation();

        $this->actingAs($this->hrUser())
            ->get(route('admin.hr.employees.show', ['employee' => $employee, 'tab' => 'performance']))
            ->assertOk()
            ->assertSee(__('KPI Snapshot'));
    }

    public function test_training_tab_renders(): void
    {
        $employee = $this->employeeWithCompensation();

        $this->actingAs($this->hrUser())
            ->get(route('admin.hr.employees.show', ['employee' => $employee, 'tab' => 'training']))
            ->assertOk()
            ->assertSee(__('Courses'));
    }

    public function test_timeline_service_includes_joined_event(): void
    {
        $employee = $this->employeeWithCompensation();
        $employee->update(['hire_date' => now()->subYear()]);

        $events = app(EmployeeTimelineService::class)->eventsFor($employee->fresh());

        $this->assertTrue($events->contains(fn ($e) => $e->eventType === 'joined'));
    }

    public function test_workspace_service_builds_all_tabs(): void
    {
        $employee = $this->employeeWithCompensation();
        $workspace = app(Employee360WorkspaceService::class)->build($employee);

        $this->assertArrayHasKey('overview', $workspace);
        $this->assertArrayHasKey('attendance', $workspace);
        $this->assertArrayHasKey('leave', $workspace);
        $this->assertArrayHasKey('compensation', $workspace);
        $this->assertArrayHasKey('payroll', $workspace);
        $this->assertArrayHasKey('documents', $workspace);
        $this->assertArrayHasKey('performance', $workspace);
        $this->assertArrayHasKey('training', $workspace);
        $this->assertArrayHasKey('assets', $workspace);
        $this->assertArrayHasKey('exit', $workspace);
        $this->assertArrayHasKey('timeline', $workspace);
        $this->assertCount(11, $workspace['tabs']);
    }

    public function test_viewer_without_permission_is_forbidden(): void
    {
        $employee = $this->employeeWithCompensation();
        $company = Company::query()->first();
        $role = Role::findOrCreate('No Employee 360', 'web');
        $role->syncPermissions([]);

        $user = User::factory()->create(['company_id' => $company->id]);
        $user->assignRole($role);

        $this->actingAs($user)
            ->get(route('admin.hr.employees.show', $employee))
            ->assertForbidden();
    }

    protected function hrUser(): User
    {
        $user = User::query()->whereHas('roles', fn ($q) => $q->where('name', 'HR'))->first()
            ?? User::factory()->create(['company_id' => Company::query()->value('id')]);

        if (! $user->hasRole('HR')) {
            $user->assignRole(Role::findByName('HR', 'web'));
        }

        return $user;
    }

    protected function employeeWithCompensation(): Employee
    {
        $company = Company::query()->first();
        $branch = Branch::query()->where('company_id', $company->id)->first();
        $department = Department::query()->where('company_id', $company->id)->first();

        $employee = Employee::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'department_id' => $department?->id,
            'employee_number' => 'EMP-360-'.uniqid(),
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'hire_date' => now()->subMonths(6),
            'employment_status' => 'active',
            'is_active' => true,
        ]);

        EmployeeCompensation::query()->create([
            'company_id' => $employee->company_id,
            'employee_id' => $employee->id,
            'basic_salary' => 75000,
            'effective_from' => now()->subMonths(6)->toDateString(),
            'payment_frequency' => 'monthly',
            'payroll_group' => 'main',
            'currency' => 'KES',
            'status' => CompensationStatus::Active,
            'is_active' => true,
        ]);

        return $employee->fresh();
    }
}
