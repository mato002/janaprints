<?php

namespace Tests\Feature\Admin;

use App\Enums\ClearanceStatus;
use App\Enums\CommunicationLogChannel;
use App\Enums\CommunicationLogStatus;
use App\Enums\CommunicationLogType;
use App\Enums\EmailAccountStatus;
use App\Enums\EmailDeliveryStatus;
use App\Enums\EmailProvider;
use App\Enums\EmailVerificationStatus;
use App\Enums\EmploymentStatus;
use App\Enums\ExitStatus;
use App\Enums\ExitType;
use App\Enums\PayrollRunStatus;
use App\Models\ActivityLog;
use App\Models\Branch;
use App\Models\Communications\CommunicationLog;
use App\Models\Communications\EmailAccount;
use App\Models\Communications\EmailMessage;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Hr\EmployeeCompensation;
use App\Models\Hr\EmployeeExit;
use App\Models\Hr\PayrollRun;
use App\Models\User;
use App\Support\Communications\CommunicationLogService;
use App\Support\Hr\EmployeeExitService;
use App\Support\Hr\PayrollConfidentialityService;
use App\Support\Hr\PayrollRunService;
use Database\Seeders\GlAccountTypeSeeder;
use Database\Seeders\JanaPrintsAccountingPeriodsSeeder;
use Database\Seeders\JanaPrintsChartOfAccountsSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PayrollPostingSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HrGovernanceHardeningTest extends TestCase
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
    }

    public function test_salary_values_hidden_from_communication_log_viewers_without_payroll_permission(): void
    {
        $log = $this->createConfidentialPayrollLog();
        $viewer = $this->userWithPermissions(['communications.logs.view']);

        $body = app(PayrollConfidentialityService::class)->communicationLogBodyForViewer($log, $viewer);

        $this->assertSame(PayrollConfidentialityService::REDACTED_PLACEHOLDER, $body);
        $this->assertStringNotContainsString('85000', $body);
        $this->assertStringNotContainsString('Net pay', $body);

        $this->actingAs($viewer)
            ->get(route('admin.communications.logs.show', $log))
            ->assertOk()
            ->assertSee(PayrollConfidentialityService::REDACTED_PLACEHOLDER, false)
            ->assertDontSee('85000', false);
    }

    public function test_payroll_viewers_can_see_confidential_communication_content(): void
    {
        $log = $this->createConfidentialPayrollLog();
        $payrollViewer = $this->userWithPermissions(['communications.logs.view', 'hr.payroll.view']);

        $body = app(PayrollConfidentialityService::class)->communicationLogBodyForViewer($log, $payrollViewer);

        $this->assertStringContainsString('85000', $body);
    }

    public function test_exit_close_deactivates_linked_user_and_revokes_roles(): void
    {
        $hr = $this->hrUser();
        [$employee, $user] = $this->employeeWithUser('EMP-EXIT-GOV-01');
        $user->assignRole(Role::findByName('Staff', 'web'));

        $exit = $this->closeExitWorkflow($employee, $hr);

        $employee->refresh();
        $user->refresh();

        $this->assertFalse($employee->is_active);
        $this->assertSame(EmploymentStatus::Terminated, $employee->employment_status);
        $this->assertFalse($user->is_active);
        $this->assertSame(0, $user->roles()->count());
        $this->assertSame(ExitStatus::Closed, $exit->status);
    }

    public function test_exit_closed_employee_cannot_login(): void
    {
        $hr = $this->hrUser();
        [$employee, $user] = $this->employeeWithUser('EMP-EXIT-GOV-02', 'exit.login@janaprints.test');
        $user->update(['password' => Hash::make('SecretPass123!')]);

        $this->closeExitWorkflow($employee, $hr);

        $this->post('/admin/login', [
            'email' => 'exit.login@janaprints.test',
            'password' => 'SecretPass123!',
        ])->assertSessionHasErrors('email');
    }

    public function test_exit_preserves_employee_and_exit_history(): void
    {
        $hr = $this->hrUser();
        [$employee] = $this->employeeWithUser('EMP-EXIT-GOV-03');

        $exit = $this->closeExitWorkflow($employee, $hr);

        $this->assertDatabaseHas('employees', ['id' => $employee->id, 'employee_number' => 'EMP-EXIT-GOV-03']);
        $this->assertDatabaseHas('employee_exits', ['id' => $exit->id, 'status' => ExitStatus::Closed->value]);
        $this->assertDatabaseHas('activity_logs', ['action' => 'employee_exit_access_locked']);
    }

    public function test_suspended_employee_cannot_login(): void
    {
        [$employee, $user] = $this->employeeWithUser('EMP-SUSP-01', 'suspended@janaprints.test');
        $user->update([
            'password' => Hash::make('SecretPass123!'),
            'is_active' => true,
        ]);

        $employee->update(['employment_status' => EmploymentStatus::Suspended]);
        app(\App\Support\Hr\EmployeeAccessGovernanceService::class)->onSuspended($employee->fresh(), $this->hrUser());

        $this->post('/admin/login', [
            'email' => 'suspended@janaprints.test',
            'password' => 'SecretPass123!',
        ])->assertSessionHasErrors('email');
    }

    public function test_suspended_employee_excluded_from_payroll_run(): void
    {
        $hr = $this->hrUser();
        $active = $this->payrollReadyEmployee('EMP-ACTIVE-PAY');
        $suspended = $this->payrollReadyEmployee('EMP-SUSP-PAY');
        $suspended->update(['employment_status' => EmploymentStatus::Suspended, 'is_active' => false]);

        $run = app(PayrollRunService::class)->create($this->company->id, [
            'payroll_group' => 'main',
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'pay_date' => now()->endOfMonth()->toDateString(),
        ], $hr);

        $scoped = app(PayrollRunService::class)->scopedEmployees($run);

        $this->assertTrue($scoped->contains('id', $active->id));
        $this->assertFalse($scoped->contains('id', $suspended->id));
    }

    public function test_reactivation_restores_employee_and_user_access(): void
    {
        $hr = $this->hrUser();
        [$employee, $user] = $this->employeeWithUser('EMP-REACT-01', 'reactivate@janaprints.test');
        $user->update(['password' => Hash::make('SecretPass123!')]);

        $employee->update(['employment_status' => EmploymentStatus::Suspended, 'is_active' => false]);
        app(\App\Support\Hr\EmployeeAccessGovernanceService::class)->onSuspended($employee->fresh(), $hr);

        $this->actingAs($hr)
            ->put(route('admin.employees.update', $employee), [
                'company_id' => $this->company->id,
                'branch_id' => $this->branch->id,
                'employee_number' => $employee->employee_number,
                'first_name' => $employee->first_name,
                'last_name' => $employee->last_name,
                'email' => $employee->email,
                'employment_status' => EmploymentStatus::Active->value,
                'is_active' => true,
            ])
            ->assertRedirect(route('admin.employees.index'));

        $employee->refresh();
        $user->refresh();

        $this->assertTrue($employee->is_active);
        $this->assertTrue($user->is_active);
        $this->assertSame(EmploymentStatus::Active, $employee->employment_status);

        $this->post('/admin/login', [
            'email' => 'reactivate@janaprints.test',
            'password' => 'SecretPass123!',
        ])->assertRedirect(route('admin.dashboard', absolute: false));
    }

    public function test_payroll_permissions_are_separated_at_policy_and_route_level(): void
    {
        $run = $this->draftPayrollRun();
        $approverOnly = $this->userWithPermissions(['hr.payroll.view', 'hr.payroll.approve']);
        $posterOnly = $this->userWithPermissions(['hr.payroll.view', 'hr.payroll.post']);

        $this->assertTrue($approverOnly->can('approve', $run));
        $this->assertFalse($approverOnly->can('post', $run));
        $this->assertFalse($approverOnly->can('release', $run));
        $this->assertFalse($approverOnly->can('markPaid', $run));

        $this->assertFalse($posterOnly->can('approve', $run));
        $this->assertTrue($posterOnly->can('post', $run));

        $this->actingAs($approverOnly)
            ->post(route('admin.hr.payroll.post', $run))
            ->assertForbidden();
    }

    public function test_activation_endpoint_is_rate_limited(): void
    {
        RateLimiter::clear('activate');

        for ($i = 0; $i < 6; $i++) {
            $this->get(route('employee.activate.show', ['token' => 'invalid-token-'.$i]))
                ->assertOk();
        }

        $this->get(route('employee.activate.show', ['token' => 'invalid-token-final']))
            ->assertStatus(429);
    }

    public function test_governance_actions_create_audit_entries(): void
    {
        $hr = $this->hrUser();
        [$employee, $user] = $this->employeeWithUser('EMP-AUDIT-01');
        $user->assignRole(Role::findByName('Staff', 'web'));

        $employee->update(['employment_status' => EmploymentStatus::Suspended]);
        app(\App\Support\Hr\EmployeeAccessGovernanceService::class)->onSuspended($employee->fresh(), $hr, 'Policy breach');

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'employee_suspended',
            'model_id' => $employee->id,
        ]);
        $this->assertDatabaseHas('activity_logs', ['action' => 'user_access_deactivated']);
        $this->assertDatabaseHas('activity_logs', ['action' => 'user_roles_revoked']);

        $this->createConfidentialPayrollLog();
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'payroll_communication_redacted',
        ]);
    }

    protected function createConfidentialPayrollLog(): CommunicationLog
    {
        $account = EmailAccount::query()->firstOrCreate(
            ['company_id' => $this->company->id, 'from_email' => 'payroll@janaprints.test'],
            [
                'branch_id' => $this->branch->id,
                'name' => 'Payroll',
                'from_name' => 'Payroll',
                'provider' => EmailProvider::Unconfigured,
                'status' => EmailAccountStatus::Active,
                'verification_status' => EmailVerificationStatus::Verified,
                'is_default' => false,
            ],
        );

        $hr = $this->hrUser();

        $message = EmailMessage::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'email_account_id' => $account->id,
            'to_emails' => [['email' => 'employee@janaprints.test']],
            'subject' => 'Your payslip for June 2026',
            'body' => '<p>Net pay: 85000.00</p>',
            'status' => EmailDeliveryStatus::Sent,
            'provider_response' => [
                'sender_purpose' => 'payslip',
                'metadata' => ['payroll_confidential' => true, 'module' => 'hr', 'entity_type' => 'payroll_payslip'],
            ],
            'sent_at' => now(),
            'created_by' => $hr->id,
        ]);

        return app(CommunicationLogService::class)->recordFromEmailMessage($message);
    }

    protected function closeExitWorkflow(Employee $employee, User $hr): EmployeeExit
    {
        $service = app(EmployeeExitService::class);

        $exit = $service->initiate($employee->company_id, [
            'employee_id' => $employee->id,
            'exit_type' => ExitType::Resignation->value,
            'last_working_date' => now()->toDateString(),
        ], $hr);

        foreach ($exit->clearances as $clearance) {
            $service->updateClearance($clearance, ClearanceStatus::Cleared, $hr);
        }

        $service->settle($exit->fresh(), $hr);

        return $service->close($exit->fresh(), $hr);
    }

    /**
     * @return array{0: Employee, 1: User}
     */
    protected function employeeWithUser(string $employeeNumber, string $email = 'employee.user@janaprints.test'): array
    {
        $department = Department::query()->where('company_id', $this->company->id)->firstOrFail();

        $employee = Employee::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'department_id' => $department->id,
            'employee_number' => $employeeNumber,
            'first_name' => 'Gov',
            'last_name' => 'Test',
            'email' => $email,
            'employment_status' => EmploymentStatus::Active,
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'employee_id' => $employee->id,
            'email' => $email,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        return [$employee, $user];
    }

    protected function payrollReadyEmployee(string $employeeNumber): Employee
    {
        $department = Department::query()->where('company_id', $this->company->id)->firstOrFail();

        $employee = Employee::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'department_id' => $department->id,
            'employee_number' => $employeeNumber,
            'first_name' => 'Pay',
            'last_name' => 'Roll',
            'email' => strtolower($employeeNumber).'@janaprints.test',
            'employment_status' => EmploymentStatus::Active,
            'is_active' => true,
        ]);

        EmployeeCompensation::query()->create([
            'company_id' => $this->company->id,
            'employee_id' => $employee->id,
            'basic_salary' => 50000,
            'effective_from' => now()->startOfYear()->toDateString(),
            'is_active' => true,
        ]);

        return $employee;
    }

    protected function draftPayrollRun(): PayrollRun
    {
        return PayrollRun::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'payroll_group' => 'main',
            'reference' => 'PR-GOV-TEST',
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth(),
            'pay_date' => now()->endOfMonth(),
            'status' => PayrollRunStatus::Draft,
            'created_by' => $this->hrUser()->id,
        ]);
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

    /**
     * @param  list<string>  $permissions
     */
    protected function userWithPermissions(array $permissions): User
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $role = Role::findOrCreate('Governance Test '.md5(implode(',', $permissions)), 'web');

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $role->syncPermissions($permissions);
        $user->assignRole($role);

        return $user;
    }
}
