<?php

namespace Tests\Feature\Admin;

use App\Enums\JournalStatus;
use App\Enums\PayrollRunStatus;
use App\Models\Accounting\Journal;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Hr\EmployeeCompensation;
use App\Models\Hr\PayrollPayslip;
use App\Models\Hr\PayrollRun;
use App\Models\User;
use App\Support\Hr\PayrollRunService;
use Database\Seeders\GlAccountTypeSeeder;
use Database\Seeders\JanaPrintsAccountingPeriodsSeeder;
use Database\Seeders\JanaPrintsChartOfAccountsSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PayrollPostingSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PayrollManagementTest extends TestCase
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
            ->get(route('admin.hr.payroll.dashboard'))
            ->assertOk()
            ->assertSee(__('Payroll'));
    }

    public function test_payroll_calculation_generates_payslips(): void
    {
        $hr = $this->hrUser();
        $employee = $this->payrollEmployee();

        $run = app(PayrollRunService::class)->create($employee->company_id, [
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'pay_date' => now()->endOfMonth()->toDateString(),
        ], $hr);

        app(PayrollRunService::class)->calculate($run, $hr);

        $run->refresh();
        $this->assertSame(PayrollRunStatus::Calculated, $run->status);
        $this->assertGreaterThan(0, $run->employee_count);

        $payslip = PayrollPayslip::query()->where('payroll_run_id', $run->id)->first();
        $this->assertNotNull($payslip);
        $this->assertGreaterThan(0, (float) $payslip->gross_pay);
        $this->assertGreaterThan(0, (float) $payslip->paye);
        $this->assertGreaterThan(0, (float) $payslip->net_pay);
    }

    public function test_approve_and_post_creates_journal(): void
    {
        $hr = $this->hrUser();
        $employee = $this->payrollEmployee();
        $service = app(PayrollRunService::class);

        $run = $service->create($employee->company_id, [
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'pay_date' => now()->endOfMonth()->toDateString(),
        ], $hr);

        $service->calculate($run, $hr);
        $service->approve($run->fresh(), $hr);
        $service->post($run->fresh(), $hr);

        $run->refresh();
        $this->assertSame(PayrollRunStatus::Posted, $run->status);
        $this->assertNotNull($run->posted_journal_id);

        $journal = Journal::query()->find($run->posted_journal_id);
        $this->assertSame(JournalStatus::Posted, $journal->status);
        $this->assertSame('payroll_run', $journal->source_type);
    }

    public function test_payslip_download(): void
    {
        $hr = $this->hrUser();
        $employee = $this->payrollEmployee();
        $service = app(PayrollRunService::class);

        $run = $service->create($employee->company_id, [
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'pay_date' => now()->endOfMonth()->toDateString(),
        ], $hr);
        $service->calculate($run, $hr);

        $payslip = PayrollPayslip::query()->where('payroll_run_id', $run->id)->firstOrFail();

        $this->actingAs($hr)
            ->get(route('admin.hr.payroll.payslip.download', $payslip))
            ->assertOk();
    }

    public function test_viewer_cannot_access_payroll(): void
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
            ->get(route('admin.hr.payroll.dashboard'))
            ->assertForbidden();
    }

    public function test_export_forbidden_without_permission(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $run = PayrollRun::query()->create([
            'company_id' => $company->id,
            'reference' => 'PR-TEST-001',
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth(),
            'pay_date' => now()->endOfMonth(),
            'status' => PayrollRunStatus::Draft,
        ]);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);

        $role = Role::findOrCreate('Payroll Viewer', 'web');
        Permission::findOrCreate('hr.payroll.view', 'web');
        $role->syncPermissions(['hr.payroll.view']);
        $user->assignRole($role);

        $this->actingAs($user)
            ->get(route('admin.hr.payroll.export', $run))
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

    protected function payrollEmployee(): Employee
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $department = Department::query()->where('company_id', $company->id)->firstOrFail();

        $employee = Employee::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'department_id' => $department->id,
            'employee_number' => 'EMP-PAY-001',
            'first_name' => 'Payroll',
            'last_name' => 'Staff',
            'email' => 'payroll.staff@janaprints.local',
            'employment_status' => 'active',
            'is_active' => true,
        ]);

        EmployeeCompensation::query()->create([
            'company_id' => $company->id,
            'employee_id' => $employee->id,
            'basic_salary' => 50000,
            'house_allowance' => 10000,
            'transport_allowance' => 5000,
            'medical_allowance' => 3000,
            'effective_from' => now()->startOfYear()->toDateString(),
            'is_active' => true,
        ]);

        return $employee;
    }
}
