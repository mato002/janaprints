<?php

namespace Tests\Feature\EmailIdentity;

use App\Enums\EmailIdentity\EmployeeActivationStatus;
use App\Enums\EmailIdentity\MailboxAuditAction;
use App\Models\ActivityLog;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\EmailIdentity\CorporateMailbox;
use App\Models\EmailIdentity\EmployeeActivation;
use App\Models\Employee;
use App\Models\JobTitle;
use App\Models\User;
use App\Services\EmailIdentity\EmployeeActivationManagementService;
use App\Services\EmailIdentity\EmployeeActivationService;
use App\Services\EmailIdentity\EmployeeOnboardingService;
use App\Services\EmailIdentity\MailboxAddressResolver;
use App\Models\Hr\Candidate;
use App\Models\Hr\JobApplication;
use App\Models\Hr\OnboardingRecord;
use App\Support\Hr\OnboardingService;
use App\Enums\RecruitmentPipelineStage;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmployeeActivationGovernanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);

        config([
            'mailboxes.domain' => 'janaprints.co.ke',
            'employee_onboarding.fallback_roles' => ['Staff', 'Viewer'],
        ]);
    }

    public function test_activation_assigns_fallback_role(): void
    {
        [$employee, $user, $plainToken] = $this->pendingActivationFixtures();

        $this->post(route('employee.activate.store', ['token' => $plainToken]), [
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertTrue($user->fresh()->hasRole('Staff'));
    }

    public function test_activation_assigns_selected_role_when_provided(): void
    {
        [$employee, $user, $plainToken] = $this->pendingActivationFixtures(intendedRole: 'Sales');

        $this->post(route('employee.activate.store', ['token' => $plainToken]), [
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertTrue($user->fresh()->hasRole('Sales'));
        $this->assertFalse($user->fresh()->hasRole('Staff'));
    }

    public function test_activation_does_not_assign_super_admin_for_non_super_admin_selection(): void
    {
        [$employee, $user, $plainToken] = $this->pendingActivationFixtures(intendedRole: 'Super Admin');

        $this->post(route('employee.activate.store', ['token' => $plainToken]), [
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertFalse($user->fresh()->hasRole('Super Admin'));
        $this->assertTrue($user->fresh()->hasRole('Staff'));
    }

    public function test_activation_succeeds_without_role_when_fallback_missing_with_audit(): void
    {
        config(['employee_onboarding.fallback_roles' => ['MissingRole']]);

        [$employee, $user, $plainToken] = $this->pendingActivationFixtures();

        $this->post(route('employee.activate.store', ['token' => $plainToken]), [
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertTrue($user->fresh()->is_active);
        $this->assertSame(0, $user->fresh()->roles()->count());
        $this->assertTrue(
            ActivityLog::query()->where('action', MailboxAuditAction::ActivationCompletedWithoutRole->value)->exists()
        );
    }

    public function test_duplicate_onboarding_does_not_duplicate_mailbox_or_activation(): void
    {
        Queue::fake();

        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        $employee = Employee::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'EMP-DUP-ONB',
            'first_name' => 'Dup',
            'last_name' => 'Check',
            'email' => 'dup.personal@example.com',
            'employment_status' => 'active',
        ]);

        $service = app(EmployeeOnboardingService::class);
        $service->ensureOnboarded($employee, 'dup.personal@example.com');
        $service->ensureOnboarded($employee->fresh(), 'dup.personal@example.com');

        $this->assertSame(1, CorporateMailbox::query()->where('employee_id', $employee->id)->count());
        $this->assertSame(1, EmployeeActivation::query()->where('employee_id', $employee->id)->whereNull('activated_at')->count());
    }

    public function test_expired_activation_can_be_regenerated(): void
    {
        Queue::fake();

        [$employee, $user] = $this->pendingActivationFixtures(expired: true);
        $admin = $this->employeesAdmin();

        $this->actingAs($admin)
            ->post(route('admin.employees.regenerate-activation', $employee))
            ->assertRedirect();

        $activation = EmployeeActivation::query()
            ->where('employee_id', $employee->id)
            ->whereNull('activated_at')
            ->latest('id')
            ->first();

        $this->assertNotNull($activation);
        $this->assertTrue($activation->expires_at->isFuture());
        $this->assertTrue(
            ActivityLog::query()->where('action', MailboxAuditAction::ActivationRegenerated->value)->exists()
        );
    }

    public function test_activation_email_can_be_resent_for_pending_activation(): void
    {
        Queue::fake();

        [$employee, $user, $plainToken] = $this->pendingActivationFixtures();
        $admin = $this->employeesAdmin();

        $this->actingAs($admin)
            ->post(route('admin.employees.resend-activation', $employee))
            ->assertRedirect();

        $this->assertTrue(
            ActivityLog::query()->where('action', MailboxAuditAction::InvitationResent->value)->exists()
        );
    }

    public function test_mailbox_status_appears_on_employee_index(): void
    {
        Queue::fake();
        $admin = $this->employeesAdmin();
        $admin->assignRole(Role::findByName('HR', 'web'));
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        $employee = Employee::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'EMP-UI-01',
            'first_name' => 'Ui',
            'last_name' => 'Check',
            'email' => 'ui.personal@example.com',
            'employment_status' => 'active',
        ]);

        app(EmployeeOnboardingService::class)->ensureOnboarded($employee, 'ui.personal@example.com');

        $this->actingAs($admin)
            ->get(route('admin.employees.edit', $employee))
            ->assertOk()
            ->assertSee('ui.check@janaprints.co.ke', false);
    }

    public function test_mailbox_address_resolver_returns_configured_addresses(): void
    {
        config([
            'mailboxes.department.support' => 'support@janaprints.co.ke',
            'mailboxes.system.noreply' => 'noreply@janaprints.co.ke',
        ]);

        $resolver = app(MailboxAddressResolver::class);

        $this->assertSame('support@janaprints.co.ke', $resolver->resolve('support'));
        $this->assertSame('noreply@janaprints.co.ke', $resolver->resolve('noreply'));
    }

    public function test_sms_hook_does_not_block_onboarding(): void
    {
        Queue::fake();
        Mail::fake();

        $admin = $this->employeesAdmin();
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.employees.store'), [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'employee_number' => 'EMP-SMS-01',
                'first_name' => 'Sms',
                'last_name' => 'Hook',
                'phone' => '254712345678',
                'employment_status' => 'active',
                'email' => 'sms.hook@example.com',
                'is_active' => true,
            ])
            ->assertRedirect(route('admin.employees.index'));

        $this->assertDatabaseHas('employees', ['employee_number' => 'EMP-SMS-01']);
    }

    public function test_old_activation_token_cannot_be_reused(): void
    {
        [$employee, $user, $plainToken] = $this->pendingActivationFixtures();

        $this->post(route('employee.activate.store', ['token' => $plainToken]), [
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ])->assertRedirect(route('admin.dashboard'));

        $this->post(route('employee.activate.store', ['token' => $plainToken]), [
            'password' => 'AnotherPass123!',
            'password_confirmation' => 'AnotherPass123!',
        ])->assertSessionHasErrors('token');
    }

    public function test_hr_recruitment_completion_triggers_email_onboarding(): void
    {
        Queue::fake();

        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $department = Department::query()->where('company_id', $company->id)->firstOrFail();
        $hr = $this->employeesAdmin();
        $hr->assignRole(Role::findByName('HR', 'web'));

        $vacancy = \App\Models\Hr\Vacancy::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'department_id' => $department->id,
            'reference' => 'VAC-ONB-001',
            'title' => 'Onboarding QA',
            'positions' => 1,
            'status' => \App\Enums\VacancyStatus::Open,
            'created_by_user_id' => $hr->id,
        ]);

        $candidate = Candidate::query()->create([
            'company_id' => $company->id,
            'first_name' => 'Recruit',
            'last_name' => 'Hire',
            'email' => 'recruit.hire@example.com',
            'phone' => '254700000001',
        ]);

        $application = JobApplication::query()->create([
            'company_id' => $company->id,
            'vacancy_id' => $vacancy->id,
            'candidate_id' => $candidate->id,
            'reference' => 'APP-ONB-001',
            'stage' => RecruitmentPipelineStage::Accepted,
            'applied_at' => now(),
        ]);

        $record = OnboardingRecord::query()->create([
            'company_id' => $company->id,
            'job_application_id' => $application->id,
            'branch_id' => $branch->id,
            'department_id' => $department->id,
            'employee_number' => 'EMP-REC-ONB',
            'status' => \App\Enums\OnboardingStatus::InProgress,
        ]);

        app(OnboardingService::class)->complete($record->fresh(), $hr);

        $employee = Employee::query()->where('employee_number', 'EMP-REC-ONB')->firstOrFail();
        $this->assertSame('recruit.hire@janaprints.co.ke', $employee->corporate_email);
        $this->assertSame(EmployeeActivationStatus::PendingActivation, $employee->activation_status);
    }

    /**
     * @return array{0: Employee, 1: User, 2: string}
     */
    protected function pendingActivationFixtures(?string $intendedRole = null, bool $expired = false): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        $employee = Employee::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'EMP-ACT-'.uniqid(),
            'first_name' => 'Act',
            'last_name' => 'User',
            'email' => 'act.personal@example.com',
            'corporate_email' => 'act.user@janaprints.co.ke',
            'employment_status' => 'active',
            'activation_status' => EmployeeActivationStatus::PendingActivation,
            'activation_role' => $intendedRole,
            'is_active' => false,
        ]);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'employee_id' => $employee->id,
            'email' => 'act.user@janaprints.co.ke',
            'is_active' => false,
        ]);

        $payload = app(EmployeeActivationService::class)->createActivation(
            $employee,
            $user,
            'act.personal@example.com',
            'act.user@janaprints.co.ke',
            $intendedRole,
        );

        if ($expired) {
            $payload['activation']->update(['expires_at' => now()->subHour()]);
        }

        return [$employee, $user, $payload['plain_token']];
    }

    protected function employeesAdmin(): User
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $role = Role::findOrCreate('Employees Admin', 'web');
        Permission::findOrCreate('employees.manage', 'web');
        $role->syncPermissions(['employees.manage']);
        $user->assignRole($role);

        return $user;
    }
}
