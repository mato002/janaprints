<?php

namespace Tests\Feature\Admin;

use App\Enums\CompensationStatus;
use App\Enums\PayrollRunStatus;
use App\Models\ActivityLog;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Hr\EmployeeCompensation;
use App\Models\Hr\PayrollPayslip;
use App\Models\Hr\PayrollRun;
use App\Models\User;
use App\Support\Hr\CompensationService;
use App\Support\Hr\PayrollCalculationService;
use App\Support\Hr\PayrollEmployeeScopeService;
use App\Support\Hr\PayrollFrozenSnapshotService;
use App\Support\Hr\PayrollGroupService;
use App\Support\Hr\PayrollIntegrityValidationService;
use App\Support\Hr\PayrollRunService;
use Database\Seeders\GlAccountTypeSeeder;
use Database\Seeders\JanaPrintsAccountingPeriodsSeeder;
use Database\Seeders\JanaPrintsChartOfAccountsSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PayrollPostingSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PayrollIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(GlAccountTypeSeeder::class);
        $this->seed(JanaPrintsChartOfAccountsSeeder::class);
        $this->seed(JanaPrintsAccountingPeriodsSeeder::class);
        $this->seed(PayrollPostingSeeder::class);
        $this->seed(PlatformConfigurationSeeder::class);

        $this->company = Company::query()->where('code', 'JANA')->firstOrFail();
        $this->branch = Branch::query()->where('company_id', $this->company->id)->where('code', 'HQ')->firstOrFail();
        app(PayrollGroupService::class)->ensureDefaults($this->company->id);
    }

    public function test_payroll_run_only_includes_matching_payroll_group(): void
    {
        $hr = $this->hrUser();
        $managementEmployee = $this->employeeWithGroup('EMP-MGT-01', 'management');
        $mainEmployee = $this->employeeWithGroup('EMP-MAIN-01', 'main');

        $run = app(PayrollRunService::class)->create($this->company->id, [
            'payroll_group' => 'management',
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'pay_date' => now()->endOfMonth()->toDateString(),
        ], $hr);

        app(PayrollRunService::class)->generate($run, $hr);

        $employeeIds = PayrollPayslip::query()->where('payroll_run_id', $run->id)->pluck('employee_id');

        $this->assertTrue($employeeIds->contains($managementEmployee->id));
        $this->assertFalse($employeeIds->contains($mainEmployee->id));
    }

    public function test_scope_certification_lists_excluded_wrong_group(): void
    {
        $hr = $this->hrUser();
        $this->employeeWithGroup('EMP-MGT-02', 'management');
        $this->employeeWithGroup('EMP-MAIN-02', 'main');

        $run = app(PayrollRunService::class)->create($this->company->id, [
            'payroll_group' => 'management',
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'pay_date' => now()->endOfMonth()->toDateString(),
        ], $hr);

        $certification = app(PayrollEmployeeScopeService::class)->certify($run);

        $this->assertSame(1, $certification['included_count']);
        $this->assertGreaterThanOrEqual(1, $certification['excluded_count']);
        $this->assertTrue(
            collect($certification['excluded'])->contains(
                fn ($row) => $row['exclusion_reason'] === PayrollEmployeeScopeService::REASON_WRONG_GROUP,
            ),
        );
    }

    public function test_compensation_components_include_risk_and_responsibility_allowances(): void
    {
        $employee = $this->employeeWithGroup('EMP-RISK-01', 'main', [
            'risk_allowance' => 5000,
            'responsibility_allowance' => 7000,
        ]);

        $calc = app(PayrollCalculationService::class)->calculateForEmployee(
            $employee,
            now()->startOfMonth(),
            now()->endOfMonth(),
        );

        $this->assertSame(80000.0, $calc['gross_pay']);
        $this->assertSame(30000.0, $calc['total_allowances']);
        $this->assertContains('RISK', collect($calc['items'])->pluck('code')->all());
        $this->assertContains('RESPONSIBILITY', collect($calc['items'])->pluck('code')->all());
    }

    public function test_payslip_stores_compensation_and_calculation_snapshots(): void
    {
        $hr = $this->hrUser();
        $employee = $this->employeeWithGroup('EMP-SNAP-01', 'main', [
            'risk_allowance' => 2000,
        ]);

        $run = app(PayrollRunService::class)->create($this->company->id, [
            'payroll_group' => 'main',
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'pay_date' => now()->endOfMonth()->toDateString(),
        ], $hr);

        app(PayrollRunService::class)->generate($run, $hr);

        $payslip = PayrollPayslip::query()->where('payroll_run_id', $run->id)->where('employee_id', $employee->id)->firstOrFail();

        $this->assertNotNull($payslip->compensation_snapshot);
        $this->assertSame(2000.0, (float) $payslip->compensation_snapshot['risk_allowance']);
        $this->assertNotNull($payslip->calculation_breakdown);
        $this->assertSame(70000.0, (float) $payslip->calculation_breakdown['gross_pay']);
    }

    public function test_salary_change_does_not_alter_historical_payslip(): void
    {
        $hr = $this->hrUser();
        $employee = $this->employeeWithGroup('EMP-HIST-01', 'main');

        $run = app(PayrollRunService::class)->create($this->company->id, [
            'payroll_group' => 'main',
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'pay_date' => now()->endOfMonth()->toDateString(),
        ], $hr);

        app(PayrollRunService::class)->generate($run, $hr);
        $payslip = PayrollPayslip::query()->where('payroll_run_id', $run->id)->firstOrFail();
        $originalNet = (float) $payslip->net_pay;
        $originalGross = (float) $payslip->gross_pay;

        app(CompensationService::class)->revise($employee, [
            'basic_salary' => 90000,
            'house_allowance' => 20000,
            'transport_allowance' => 0,
            'medical_allowance' => 0,
            'risk_allowance' => 0,
            'responsibility_allowance' => 0,
            'effective_from' => now()->addMonth()->startOfMonth()->toDateString(),
            'change_reason' => 'Promotion',
            'payroll_group' => 'main',
        ], $hr);

        $payslip->refresh();

        $this->assertSame($originalNet, (float) $payslip->net_pay);
        $this->assertSame($originalGross, (float) $payslip->gross_pay);
        $this->assertSame(50000.0, (float) $payslip->compensation_snapshot['basic_salary']);
    }

    public function test_integrity_validator_flags_missing_salary_setup(): void
    {
        $hr = $this->hrUser();
        $this->employeeWithGroup('EMP-OK-01', 'main');
        $this->employeeWithGroup('EMP-BAD-01', 'main', ['basic_salary' => 0]);

        $run = app(PayrollRunService::class)->create($this->company->id, [
            'payroll_group' => 'main',
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'pay_date' => now()->endOfMonth()->toDateString(),
        ], $hr);

        $result = app(PayrollIntegrityValidationService::class)->validateBeforeGeneration($run);

        $this->assertTrue($result['valid']);
        $this->assertGreaterThan(0, $result['summary']['setup_warnings']);
    }

    public function test_approval_freezes_payroll_snapshot(): void
    {
        $hr = $this->hrUser();
        $this->employeeWithGroup('EMP-FRZ-01', 'main');
        $service = app(PayrollRunService::class);

        $run = $service->create($this->company->id, [
            'payroll_group' => 'main',
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'pay_date' => now()->endOfMonth()->toDateString(),
        ], $hr);

        $service->generate($run, $hr);
        $service->submitForReview($run->fresh(), $hr);
        $service->submitForApproval($run->fresh(), $hr);
        $service->approve($run->fresh(), $hr);

        $run->refresh();

        $this->assertNotNull($run->frozen_snapshot);
        $this->assertTrue(app(PayrollFrozenSnapshotService::class)->matches($run));
        $this->assertDatabaseHas('activity_logs', ['action' => 'payroll_approved']);
    }

    public function test_compensation_revision_creates_audit_entry(): void
    {
        $hr = $this->hrUser();
        $employee = $this->employeeWithGroup('EMP-AUD-01', 'main');

        app(CompensationService::class)->revise($employee, [
            'basic_salary' => 65000,
            'house_allowance' => 12000,
            'transport_allowance' => 5000,
            'medical_allowance' => 3000,
            'risk_allowance' => 1000,
            'responsibility_allowance' => 0,
            'effective_from' => now()->addMonth()->startOfMonth()->toDateString(),
            'change_reason' => 'Annual review',
            'payroll_group' => 'management',
        ], $hr);

        $this->assertDatabaseHas('compensation_salary_changes', [
            'employee_id' => $employee->id,
            'old_salary' => 50000,
            'new_salary' => 65000,
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'compensation_revised',
            'model_id' => $employee->id,
        ]);
    }

    public function test_independent_groups_can_run_separately(): void
    {
        $hr = $this->hrUser();
        $this->employeeWithGroup('EMP-CAS-01', 'casual');
        $this->employeeWithGroup('EMP-CON-01', 'contract');

        $casualRun = app(PayrollRunService::class)->create($this->company->id, [
            'payroll_group' => 'casual',
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'pay_date' => now()->endOfMonth()->toDateString(),
        ], $hr);

        $contractRun = app(PayrollRunService::class)->create($this->company->id, [
            'payroll_group' => 'contract',
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'pay_date' => now()->endOfMonth()->toDateString(),
        ], $hr);

        app(PayrollRunService::class)->generate($casualRun, $hr);
        app(PayrollRunService::class)->generate($contractRun, $hr);

        $this->assertSame(1, PayrollPayslip::query()->where('payroll_run_id', $casualRun->id)->count());
        $this->assertSame(1, PayrollPayslip::query()->where('payroll_run_id', $contractRun->id)->count());
    }

    /**
     * @param  array<string, mixed>  $compOverrides
     */
    protected function employeeWithGroup(string $employeeNumber, string $payrollGroup, array $compOverrides = []): Employee
    {
        $department = Department::query()->where('company_id', $this->company->id)->firstOrFail();

        $employee = Employee::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'department_id' => $department->id,
            'employee_number' => $employeeNumber,
            'first_name' => 'Pay',
            'last_name' => 'Test',
            'email' => strtolower($employeeNumber).'@janaprints.test',
            'employment_status' => 'active',
            'is_active' => true,
        ]);

        EmployeeCompensation::query()->create(array_merge([
            'company_id' => $this->company->id,
            'employee_id' => $employee->id,
            'basic_salary' => 50000,
            'house_allowance' => 10000,
            'transport_allowance' => 5000,
            'medical_allowance' => 3000,
            'risk_allowance' => 0,
            'responsibility_allowance' => 0,
            'payroll_group' => $payrollGroup,
            'effective_from' => now()->startOfYear()->toDateString(),
            'status' => CompensationStatus::Active,
            'is_active' => true,
        ], $compOverrides));

        return $employee;
    }

    protected function hrUser(): User
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $user->assignRole(Role::findByName('HR', 'web'));

        return $user;
    }
}
