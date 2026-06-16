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
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
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
        $this->seed(PlatformConfigurationSeeder::class);
    }

    public function test_dashboard_renders(): void
    {
        $this->actingAs($this->hrUser())
            ->withHeaders(['Turbo-Frame' => 'module-workspace-content'])
            ->get(route('admin.hr.payroll.dashboard', ['embedded' => '1']))
            ->assertOk()
            ->assertSee(__('Payroll'));
    }

    public function test_create_form_renders_governed_fields_with_required_markers(): void
    {
        $this->actingAs($this->hrUser())
            ->get(route('admin.hr.payroll.create'))
            ->assertOk()
            ->assertSee(__('Period start'))
            ->assertSee(__('Period end'))
            ->assertSee(__('Pay date'))
            ->assertSee('class="erp-form-field"', false)
            ->assertSee('required', false);
    }

    public function test_store_creates_payroll_run_and_redirects_to_360(): void
    {
        $hr = $this->hrUser();

        $this->actingAs($hr)
            ->post(route('admin.hr.payroll.store'), [
                'period_start' => now()->startOfMonth()->toDateString(),
                'period_end' => now()->endOfMonth()->toDateString(),
                'pay_date' => now()->endOfMonth()->toDateString(),
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $run = PayrollRun::query()->latest('id')->first();
        $this->assertNotNull($run);
        $this->assertStringStartsWith('PR-', $run->reference);
        $this->assertSame(PayrollRunStatus::Draft, $run->status);
    }

    public function test_open_payroll_run_360_workspace(): void
    {
        $hr = $this->hrUser();
        $run = $this->createRun($hr);

        $this->actingAs($hr)
            ->get(route('admin.hr.payroll.show', $run))
            ->assertOk()
            ->assertSee(__('Payroll Run 360'))
            ->assertSee($run->reference)
            ->assertSee(__('Overview'))
            ->assertSee(__('Employees'))
            ->assertSee(__('Review'))
            ->assertSee(__('Generate payroll'));
    }

    public function test_generate_payroll_lines(): void
    {
        $hr = $this->hrUser();
        $employee = $this->payrollEmployee();
        $run = $this->createRun($hr);

        app(PayrollRunService::class)->generate($run, $hr);

        $run->refresh();
        $this->assertSame(PayrollRunStatus::Generated, $run->status);
        $this->assertGreaterThan(0, $run->employee_count);

        $payslip = PayrollPayslip::query()->where('payroll_run_id', $run->id)->first();
        $this->assertNotNull($payslip);
        $this->assertSame($employee->id, $payslip->employee_id);
        $this->assertGreaterThan(0, (float) $payslip->gross_pay);
    }

    public function test_prevent_duplicate_generation_without_confirmation(): void
    {
        $hr = $this->hrUser();
        $this->payrollEmployee();
        $run = $this->createRun($hr);
        $service = app(PayrollRunService::class);

        $service->generate($run, $hr);

        $this->expectException(ValidationException::class);
        $service->generate($run->fresh(), $hr, false);
    }

    public function test_regenerate_with_confirmation_replaces_lines(): void
    {
        $hr = $this->hrUser();
        $this->payrollEmployee();
        $run = $this->createRun($hr);
        $service = app(PayrollRunService::class);

        $service->generate($run, $hr);
        $firstCount = PayrollPayslip::query()->where('payroll_run_id', $run->id)->count();

        $service->generate($run->fresh(), $hr, true);

        $this->assertSame(
            $firstCount,
            PayrollPayslip::query()->where('payroll_run_id', $run->id)->count()
        );
        $this->assertSame(PayrollRunStatus::Generated, $run->fresh()->status);
    }

    public function test_missing_salary_setup_records_warning_without_crashing(): void
    {
        $hr = $this->hrUser();
        $this->payrollEmployee();
        $this->employeeWithoutCompensation();
        $run = $this->createRun($hr);

        app(PayrollRunService::class)->generate($run, $hr);

        $run->refresh();
        $this->assertSame(PayrollRunStatus::Generated, $run->status);
        $this->assertTrue($run->has_generation_warnings);
        $this->assertGreaterThan(0, count($run->generation_warnings ?? []));
        $this->assertGreaterThan(1, $run->employee_count);
    }

    public function test_branch_scoping_limits_generated_employee_lines(): void
    {
        $hr = $this->hrUser();
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $hq = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $otherBranch = Branch::query()->create([
            'company_id' => $company->id,
            'code' => 'BR2',
            'name' => 'Branch Two',
            'is_active' => true,
        ]);

        $this->payrollEmployee($hq);
        $this->payrollEmployee($otherBranch, 'EMP-BR2-001');

        $run = app(PayrollRunService::class)->create($company->id, [
            'branch_id' => $hq->id,
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'pay_date' => now()->endOfMonth()->toDateString(),
        ], $hr);

        app(PayrollRunService::class)->generate($run, $hr);

        $run->refresh();
        $this->assertSame(1, $run->employee_count);
        $this->assertSame(
            $hq->id,
            PayrollPayslip::query()->where('payroll_run_id', $run->id)->firstOrFail()->employee?->branch_id
        );
    }

    public function test_critical_review_blocks_submit_for_approval(): void
    {
        $hr = $this->hrUser();
        $this->payrollEmployee();
        $this->employeeWithoutCompensation();
        $run = $this->createRun($hr);
        $service = app(PayrollRunService::class);

        $service->generate($run, $hr);
        $service->submitForReview($run->fresh(), $hr);

        $this->expectException(ValidationException::class);
        $service->submitForApproval($run->fresh(), $hr);
    }

    public function test_payment_export_placeholder(): void
    {
        $hr = $this->hrUser();
        $this->payrollEmployee();
        $run = $this->createRun($hr);
        $service = app(PayrollRunService::class);

        $service->generate($run, $hr);
        $service->submitForReview($run->fresh(), $hr);
        $service->submitForApproval($run->fresh(), $hr);
        $service->approve($run->fresh(), $hr);
        $service->post($run->fresh(), $hr);

        $this->actingAs($hr)
            ->withHeaders(['Turbo-Frame' => 'module-workspace-content'])
            ->get(route('admin.hr.payroll.export-payment', ['payrollRun' => $run->fresh(), 'format' => 'bank']))
            ->assertOk();
    }

    public function test_payslip_view_page(): void
    {
        $hr = $this->hrUser();
        $this->payrollEmployee();
        $run = $this->createRun($hr);
        app(PayrollRunService::class)->generate($run, $hr);

        $payslip = PayrollPayslip::query()->where('payroll_run_id', $run->id)->firstOrFail();

        $this->actingAs($hr)
            ->get(route('admin.hr.payroll.payslip.show', $payslip))
            ->assertOk()
            ->assertSee($payslip->employee?->full_name ?? '');
    }

    public function test_status_transition_guards(): void
    {
        $hr = $this->hrUser();
        $this->payrollEmployee();
        $run = $this->createRun($hr);
        $service = app(PayrollRunService::class);

        $this->expectException(ValidationException::class);
        $service->approve($run, $hr);
    }

    public function test_approve_and_post_creates_journal(): void
    {
        $hr = $this->hrUser();
        $this->payrollEmployee();
        $service = app(PayrollRunService::class);
        $run = $this->createRun($hr);

        $service->generate($run, $hr);
        $service->submitForReview($run->fresh(), $hr);
        $service->submitForApproval($run->fresh(), $hr);
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
        $this->payrollEmployee();
        $run = $this->createRun($hr);
        app(PayrollRunService::class)->generate($run, $hr);

        $payslip = PayrollPayslip::query()->where('payroll_run_id', $run->id)->firstOrFail();

        $this->actingAs($hr)
            ->withHeaders(['Turbo-Frame' => 'module-workspace-content'])
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
            ->withHeaders(['Turbo-Frame' => 'module-workspace-content'])
            ->get(route('admin.hr.payroll.dashboard', ['embedded' => '1']))
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
            ->withHeaders(['Turbo-Frame' => 'module-workspace-content'])
            ->get(route('admin.hr.payroll.export', $run))
            ->assertForbidden();
    }

    protected function createRun(User $hr): PayrollRun
    {
        return app(PayrollRunService::class)->create(
            Company::query()->where('code', 'JANA')->value('id'),
            [
                'period_start' => now()->startOfMonth()->toDateString(),
                'period_end' => now()->endOfMonth()->toDateString(),
                'pay_date' => now()->endOfMonth()->toDateString(),
            ],
            $hr,
        );
    }

    protected function employeeWithoutCompensation(): Employee
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $department = Department::query()->where('company_id', $company->id)->firstOrFail();

        return Employee::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'department_id' => $department->id,
            'employee_number' => 'EMP-NOCOMP-001',
            'first_name' => 'No',
            'last_name' => 'Comp',
            'email' => 'nocomp@janaprints.local',
            'employment_status' => 'active',
            'is_active' => true,
        ]);
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

    protected function payrollEmployee(?Branch $branch = null, string $employeeNumber = 'EMP-PAY-001'): Employee
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch ??= Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $department = Department::query()->where('company_id', $company->id)->firstOrFail();

        $employee = Employee::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'department_id' => $department->id,
            'employee_number' => $employeeNumber,
            'first_name' => 'Payroll',
            'last_name' => 'Staff',
            'email' => $employeeNumber.'@janaprints.local',
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
