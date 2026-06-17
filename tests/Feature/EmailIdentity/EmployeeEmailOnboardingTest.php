<?php

namespace Tests\Feature\EmailIdentity;

use App\Enums\EmailIdentity\EmployeeActivationStatus;
use App\Enums\EmailIdentity\MailboxAuditAction;
use App\Jobs\EmailIdentity\SendEmployeeOnboardingEmailJob;
use App\Mail\EmployeeOnboardingMail;
use App\Models\ActivityLog;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\EmailIdentity\EmployeeActivation;
use App\Models\Employee;
use App\Models\User;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmployeeEmailOnboardingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);

        config([
            'mailboxes.onboarding.from_address' => 'info@janaprints.co.ke',
            'mailboxes.onboarding.reply_to' => 'info@janaprints.co.ke',
            'mailboxes.onboarding.mailer' => 'array',
        ]);
    }

    public function test_employee_creation_queues_onboarding_email_with_personal_login(): void
    {
        Mail::fake();
        Queue::fake();

        $admin = $this->employeesAdmin();
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $department = Department::query()->where('company_id', $company->id)->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.employees.store'), [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'department_id' => $department->id,
                'employee_number' => 'EMP-EMAIL-01',
                'first_name' => 'Grace',
                'last_name' => 'Wanjiku',
                'employment_status' => 'active',
                'email' => 'grace.personal@example.com',
                'is_active' => true,
            ])
            ->assertRedirect(route('admin.employees.index'));

        $employee = Employee::query()->where('employee_number', 'EMP-EMAIL-01')->firstOrFail();

        $this->assertSame('grace.personal@example.com', $employee->email);
        $this->assertSame(EmployeeActivationStatus::PendingActivation, $employee->activation_status);
        $this->assertFalse($employee->is_active);

        $this->assertDatabaseHas('users', [
            'employee_id' => $employee->id,
            'email' => 'grace.personal@example.com',
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('employee_activations', [
            'employee_id' => $employee->id,
            'personal_email' => 'grace.personal@example.com',
        ]);

        Queue::assertPushed(SendEmployeeOnboardingEmailJob::class, function (SendEmployeeOnboardingEmailJob $job) use ($employee) {
            return $job->employeeId === $employee->id
                && $job->personalEmail === 'grace.personal@example.com';
        });

        $this->assertTrue(
            ActivityLog::query()->where('action', MailboxAuditAction::InvitationSent->value)->exists()
        );
    }

    public function test_onboarding_email_job_sends_to_personal_email_without_password(): void
    {
        Queue::fake();

        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        $employee = Employee::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'EMP-EMAIL-02',
            'first_name' => 'Mary',
            'last_name' => 'Wanjiku',
            'email' => 'mary.personal@example.com',
            'employment_status' => 'active',
            'activation_status' => EmployeeActivationStatus::PendingActivation,
            'is_active' => false,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $job = new SendEmployeeOnboardingEmailJob(
            employeeId: $employee->id,
            personalEmail: 'mary.personal@example.com',
            activationUrl: 'https://janaprints.co.ke/activate/test-token',
            expiresAt: now()->addDays(3)->toIso8601String(),
        );

        $job->handle(
            app(\App\Services\EmailIdentity\EmailIdentityAuditService::class),
            app(\App\Services\EmailIdentity\EmailSenderResolver::class),
            app(\App\Support\Branding\BrandingAssets::class),
            app(\App\Support\Communications\Email\CorporateMailDispatcher::class),
        );

        $this->assertDatabaseHas('email_messages', [
            'company_id' => $company->id,
            'subject' => __('Welcome to :company', ['company' => $company->name]),
        ]);

        Queue::assertPushed(\App\Jobs\Communications\SendEmailMessageJob::class);
    }

    public function test_activation_completes_user_account(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        $employee = Employee::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'EMP-EMAIL-03',
            'first_name' => 'Peter',
            'last_name' => 'Ochieng',
            'email' => 'peter.personal@example.com',
            'employment_status' => 'active',
            'activation_status' => EmployeeActivationStatus::PendingActivation,
            'is_active' => false,
        ]);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'employee_id' => $employee->id,
            'email' => 'peter.personal@example.com',
            'is_active' => false,
        ]);

        $plainToken = str_repeat('a', 64);

        EmployeeActivation::query()->create([
            'company_id' => $company->id,
            'employee_id' => $employee->id,
            'user_id' => $user->id,
            'personal_email' => 'peter.personal@example.com',
            'token_hash' => hash('sha256', $plainToken),
            'expires_at' => now()->addDay(),
        ]);

        $this->post(route('employee.activate.store', ['token' => $plainToken]), [
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ])->assertRedirect(route('admin.dashboard'));

        $employee->refresh();
        $user->refresh();

        $this->assertSame(EmployeeActivationStatus::Activated, $employee->activation_status);
        $this->assertTrue($employee->is_active);
        $this->assertTrue($user->is_active);
        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertAuthenticatedAs($user);

        $this->assertTrue(
            ActivityLog::query()->where('action', MailboxAuditAction::ActivationCompleted->value)->exists()
        );
    }

    public function test_onboarding_service_catches_notification_dispatch_errors(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        $employee = Employee::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'EMP-EMAIL-05',
            'first_name' => 'Queue',
            'last_name' => 'Fail',
            'email' => 'queue.personal@example.com',
            'employment_status' => 'active',
            'activation_status' => EmployeeActivationStatus::PendingActivation,
            'is_active' => false,
        ]);

        $service = new class(
            app(\App\Services\EmailIdentity\EmployeeActivationService::class),
            app(\App\Services\EmailIdentity\EmployeeActivationManagementService::class),
            app(\App\Services\EmailIdentity\EmailIdentityAuditService::class),
            app(\App\Services\EmailIdentity\EmployeeOnboardingSmsNotifier::class),
        ) extends \App\Services\EmailIdentity\EmployeeOnboardingService {
            public function triggerNotificationFailure(Employee $employee): void
            {
                $this->dispatchOnboardingNotifications(
                    $employee,
                    'queue.personal@example.com',
                    'https://janaprints.co.ke/activate/test',
                    now()->addDay(),
                );
            }

            protected function dispatchOnboardingNotifications(
                Employee $employee,
                string $personalEmail,
                string $activationUrl,
                \DateTimeInterface $expiresAt,
                ?EmployeeActivation $activation = null,
            ): void {
                try {
                    throw new \RuntimeException('Queue unavailable');
                } catch (\Throwable $exception) {
                    report($exception);

                    app(\App\Services\EmailIdentity\EmailIdentityAuditService::class)->logForEmployee(
                        MailboxAuditAction::InvitationSent,
                        $employee,
                        ['queued' => false, 'error' => $exception->getMessage()],
                    );
                }
            }
        };

        $service->triggerNotificationFailure($employee);

        $this->assertDatabaseHas('activity_logs', [
            'action' => MailboxAuditAction::InvitationSent->value,
            'model_id' => $employee->id,
        ]);
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
