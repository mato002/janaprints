<?php

namespace Tests\Feature\Admin;

use App\Enums\CompensationStatus;
use App\Enums\PayrollComponentCalculationType;
use App\Enums\PayrollRunStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Hr\CompensationSalaryChange;
use App\Models\Hr\EmployeeCompensation;
use App\Models\Hr\PayrollAllowance;
use App\Models\Hr\PayrollDeduction;
use App\Models\Hr\PayrollRun;
use App\Models\User;
use App\Support\Hr\CompensationService;
use App\Support\Hr\PayrollCompensationValidationService;
use App\Support\Hr\PayrollRunService;
use Database\Seeders\GlAccountTypeSeeder;
use Database\Seeders\JanaPrintsAccountingPeriodsSeeder;
use Database\Seeders\JanaPrintsChartOfAccountsSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PayrollPostingSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CompensationManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(GlAccountTypeSeeder::class);
        $this->seed(JanaPrintsChartOfAccountsSeeder::class);
        $this->seed(JanaPrintsAccountingPeriodsSeeder::class);
        $this->seed(PayrollPostingSeeder::class);
    }

    public function test_dashboard_renders(): void
    {
        $this->actingAs($this->hrUser())
            ->get(route('admin.hr.compensation.dashboard'))
            ->assertOk()
            ->assertSee(__('Compensation Center'));
    }

    public function test_create_compensation(): void
    {
        $hr = $this->hrUser();
        $employee = $this->employeeWithoutCompensation();

        $this->actingAs($hr)
            ->post(route('admin.hr.compensation.store'), [
                'employee_id' => $employee->id,
                'basic_salary' => 85000,
                'house_allowance' => 15000,
                'transport_allowance' => 5000,
                'medical_allowance' => 3000,
                'risk_allowance' => 2000,
                'responsibility_allowance' => 1000,
                'effective_from' => now()->toDateString(),
                'payment_frequency' => 'monthly',
                'payroll_group' => 'main',
                'currency' => 'KES',
            ])
            ->assertRedirect(route('admin.hr.compensation.register'));

        $compensation = EmployeeCompensation::query()->where('employee_id', $employee->id)->first();
        $this->assertNotNull($compensation);
        $this->assertSame('85000.00', $compensation->basic_salary);
        $this->assertSame(CompensationStatus::Active, $compensation->status);
    }

    public function test_update_compensation_uses_effective_dating(): void
    {
        $hr = $this->hrUser();
        $employee = $this->employeeWithCompensation(60000);

        $this->actingAs($hr)
            ->put(route('admin.hr.compensation.update', $employee), [
                'basic_salary' => 75000,
                'effective_from' => now()->addMonth()->toDateString(),
                'payment_frequency' => 'monthly',
                'payroll_group' => 'main',
                'currency' => 'KES',
                'change_reason' => 'Annual increment',
            ])
            ->assertRedirect(route('admin.hr.employees.show', ['employee' => $employee, 'tab' => 'compensation']));

        $this->assertSame(2, EmployeeCompensation::query()->where('employee_id', $employee->id)->count());

        $active = EmployeeCompensation::query()
            ->where('employee_id', $employee->id)
            ->where('is_active', true)
            ->first();

        $this->assertSame('75000.00', $active->basic_salary);
        $this->assertSame(1, CompensationSalaryChange::query()->where('employee_id', $employee->id)->count());
    }

    public function test_allowance_percentage_calculation(): void
    {
        $employee = $this->employeeWithCompensation(100000);

        $allowance = PayrollAllowance::query()->create([
            'company_id' => $employee->company_id,
            'employee_id' => $employee->id,
            'code' => 'BONUS',
            'name' => 'Performance Bonus',
            'calculation_type' => PayrollComponentCalculationType::Percentage,
            'frequency' => 'recurring',
            'amount' => 0,
            'percentage_rate' => 10,
            'is_active' => true,
        ]);

        $this->assertSame(10000.0, $allowance->resolvedAmount(100000));
    }

    public function test_deduction_fixed_calculation(): void
    {
        $employee = $this->employeeWithCompensation(80000);

        $deduction = PayrollDeduction::query()->create([
            'company_id' => $employee->company_id,
            'employee_id' => $employee->id,
            'code' => 'ADVANCE',
            'name' => 'Salary Advance',
            'category' => 'custom',
            'calculation_type' => PayrollComponentCalculationType::Fixed,
            'frequency' => 'recurring',
            'amount' => 5000,
            'is_active' => true,
        ]);

        $this->assertSame(5000.0, $deduction->resolvedAmount(80000));
    }

    public function test_employee_workspace_shows_compensation_tab(): void
    {
        $employee = $this->employeeWithCompensation(70000);

        $this->actingAs($this->hrUser())
            ->get(route('admin.hr.employees.show', ['employee' => $employee, 'tab' => 'compensation']))
            ->assertOk()
            ->assertSee(__('Compensation'))
            ->assertSee('70,000.00');
    }

    public function test_payroll_validation_blocks_missing_compensation(): void
    {
        $hr = $this->hrUser();
        $covered = $this->employeeWithCompensation(90000);
        $this->employeeWithoutCompensation();

        $run = app(PayrollRunService::class)->create($covered->company_id, [
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'pay_date' => now()->endOfMonth()->toDateString(),
        ], $hr);

        $validation = app(PayrollCompensationValidationService::class)->validateForRun($run);
        $this->assertFalse($validation['valid']);
        $this->assertGreaterThan(0, $validation['summary']['employees_with_issues']);

        $this->expectException(ValidationException::class);
        app(PayrollRunService::class)->calculate($run, $hr);
    }

    public function test_payroll_runs_when_compensation_valid(): void
    {
        $hr = $this->hrUser();
        $employee = $this->employeeWithCompensation(90000);

        $run = app(PayrollRunService::class)->create($employee->company_id, [
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'pay_date' => now()->endOfMonth()->toDateString(),
        ], $hr);

        app(PayrollRunService::class)->calculate($run, $hr);
        $run->refresh();

        $this->assertSame(PayrollRunStatus::Calculated, $run->status);
    }

    public function test_viewer_cannot_access_compensation(): void
    {
        $company = Company::query()->first();
        $role = Role::findOrCreate('Comp Viewer Block', 'web');
        Permission::findOrCreate('hr.compensation.view', 'web');
        $role->syncPermissions([]);

        $user = User::factory()->create(['company_id' => $company->id]);
        $user->assignRole($role);

        $this->actingAs($user)
            ->get(route('admin.hr.compensation.dashboard'))
            ->assertForbidden();
    }

    public function test_compensation_service_approve(): void
    {
        $hr = $this->hrUser();
        $employee = $this->employeeWithoutCompensation();

        $compensation = app(CompensationService::class)->create($employee, [
            'basic_salary' => 50000,
            'effective_from' => now()->toDateString(),
            'payment_frequency' => 'monthly',
            'payroll_group' => 'main',
            'currency' => 'KES',
        ], $hr, true);

        $this->assertSame(CompensationStatus::PendingApproval, $compensation->status);

        $approved = app(CompensationService::class)->approve($compensation, $hr);
        $this->assertSame(CompensationStatus::Active, $approved->status);
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

    protected function employeeWithoutCompensation(): Employee
    {
        $company = Company::query()->first();
        $branch = Branch::query()->where('company_id', $company->id)->first();
        $department = Department::query()->where('company_id', $company->id)->first();

        return Employee::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'department_id' => $department?->id,
            'employee_number' => 'EMP-COMP-'.uniqid(),
            'first_name' => 'Test',
            'last_name' => 'Employee',
            'employment_status' => 'active',
            'is_active' => true,
        ]);
    }

    protected function employeeWithCompensation(float $salary): Employee
    {
        $employee = $this->employeeWithoutCompensation();

        EmployeeCompensation::query()->create([
            'company_id' => $employee->company_id,
            'employee_id' => $employee->id,
            'basic_salary' => $salary,
            'house_allowance' => 0,
            'transport_allowance' => 0,
            'medical_allowance' => 0,
            'risk_allowance' => 0,
            'responsibility_allowance' => 0,
            'effective_from' => now()->subMonth()->toDateString(),
            'payment_frequency' => 'monthly',
            'payroll_group' => 'main',
            'currency' => 'KES',
            'status' => CompensationStatus::Active,
            'is_active' => true,
        ]);

        return $employee->fresh('compensation');
    }
}
