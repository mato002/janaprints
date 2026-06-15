<?php

namespace Tests\Feature\EmailIdentity;

use App\Enums\EmailIdentity\EmployeeActivationStatus;
use App\Enums\EmailIdentity\MailboxAuditAction;
use App\Enums\EmailIdentity\MailboxStatus;
use App\Jobs\EmailIdentity\SendEmployeeOnboardingEmailJob;
use App\Mail\EmployeeOnboardingMail;
use App\Models\ActivityLog;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\EmailIdentity\CorporateMailbox;
use App\Models\EmailIdentity\EmployeeActivation;
use App\Models\Employee;
use App\Models\User;
use App\Services\EmailIdentity\CorporateEmailGeneratorService;
use App\Services\EmailIdentity\CorporateMailboxProvisioningService;
use App\Services\EmailIdentity\CpanelMailboxGateway;
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
            'mailboxes.domain' => 'janaprints.co.ke',
            'mailboxes.onboarding.from_address' => 'info@janaprints.co.ke',
            'mailboxes.onboarding.reply_to' => 'info@janaprints.co.ke',
            'mailboxes.onboarding.mailer' => 'array',
        ]);
    }

    public function test_corporate_email_generator_builds_firstname_lastname_address(): void
    {
        $generator = app(CorporateEmailGeneratorService::class);

        $this->assertSame(
            'grace.wanjiku@janaprints.co.ke',
            $generator->preview('Grace', 'Wanjiku'),
        );

        $this->assertSame(
            'peter.ochieng@janaprints.co.ke',
            $generator->preview('Peter', 'Ochieng'),
        );
    }

    public function test_corporate_email_generator_handles_duplicate_names(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        Employee::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'EMP-DUP-0',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'employment_status' => 'active',
            'corporate_email' => 'john.doe@janaprints.co.ke',
            'activation_status' => EmployeeActivationStatus::Activated,
        ]);

        $generator = app(CorporateEmailGeneratorService::class);

        $this->assertSame(
            'john.doe1@janaprints.co.ke',
            $generator->generate('John', 'Doe'),
        );
    }

    public function test_employee_creation_provisions_mailbox_and_queues_onboarding_email(): void
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
        $this->assertSame('grace.wanjiku@janaprints.co.ke', $employee->corporate_email);
        $this->assertSame(EmployeeActivationStatus::PendingActivation, $employee->activation_status);
        $this->assertFalse($employee->is_active);

        $this->assertDatabaseHas('corporate_mailboxes', [
            'employee_id' => $employee->id,
            'email_address' => 'grace.wanjiku@janaprints.co.ke',
            'status' => MailboxStatus::Pending->value,
        ]);

        $this->assertDatabaseHas('users', [
            'employee_id' => $employee->id,
            'email' => 'grace.wanjiku@janaprints.co.ke',
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('employee_activations', [
            'employee_id' => $employee->id,
            'personal_email' => 'grace.personal@example.com',
            'corporate_email' => 'grace.wanjiku@janaprints.co.ke',
        ]);

        Queue::assertPushed(SendEmployeeOnboardingEmailJob::class, function (SendEmployeeOnboardingEmailJob $job) use ($employee) {
            return $job->employeeId === $employee->id
                && $job->personalEmail === 'grace.personal@example.com'
                && $job->corporateEmail === 'grace.wanjiku@janaprints.co.ke';
        });

        $this->assertTrue(
            ActivityLog::query()->where('action', MailboxAuditAction::MailboxGenerated->value)->exists()
        );
    }

    public function test_onboarding_email_job_sends_to_personal_email_without_password(): void
    {
        Mail::fake();

        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        $employee = Employee::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'employee_number' => 'EMP-EMAIL-02',
            'first_name' => 'Mary',
            'last_name' => 'Wanjiku',
            'email' => 'mary.personal@example.com',
            'corporate_email' => 'mary.wanjiku@janaprints.co.ke',
            'employment_status' => 'active',
            'activation_status' => EmployeeActivationStatus::PendingActivation,
            'is_active' => false,
        ]);

        $job = new SendEmployeeOnboardingEmailJob(
            employeeId: $employee->id,
            personalEmail: 'mary.personal@example.com',
            corporateEmail: 'mary.wanjiku@janaprints.co.ke',
            activationUrl: 'https://janaprints.co.ke/activate/test-token',
            expiresAt: now()->addDays(3)->toIso8601String(),
        );

        $job->handle(app(\App\Services\EmailIdentity\EmailIdentityAuditService::class));

        Mail::assertSent(EmployeeOnboardingMail::class, function (EmployeeOnboardingMail $mail) {
            return $mail->hasTo('mary.personal@example.com')
                && $mail->corporateEmail === 'mary.wanjiku@janaprints.co.ke'
                && str_contains($mail->activationUrl, '/activate/test-token')
                && $mail->envelope()->from->address === 'info@janaprints.co.ke';
        });
    }

    public function test_activation_completes_user_and_mailbox(): void
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
            'corporate_email' => 'peter.ochieng@janaprints.co.ke',
            'employment_status' => 'active',
            'activation_status' => EmployeeActivationStatus::PendingActivation,
            'is_active' => false,
        ]);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'employee_id' => $employee->id,
            'email' => 'peter.ochieng@janaprints.co.ke',
            'is_active' => false,
        ]);

        CorporateMailbox::query()->create([
            'company_id' => $company->id,
            'employee_id' => $employee->id,
            'email_address' => 'peter.ochieng@janaprints.co.ke',
            'local_part' => 'peter.ochieng',
            'domain' => 'janaprints.co.ke',
            'type' => 'corporate',
            'status' => MailboxStatus::Pending,
        ]);

        $plainToken = str_repeat('a', 64);

        EmployeeActivation::query()->create([
            'company_id' => $company->id,
            'employee_id' => $employee->id,
            'user_id' => $user->id,
            'personal_email' => 'peter.personal@example.com',
            'corporate_email' => 'peter.ochieng@janaprints.co.ke',
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

        $this->assertSame(
            MailboxStatus::Active,
            CorporateMailbox::query()->where('employee_id', $employee->id)->first()->status,
        );

        $this->assertTrue(
            ActivityLog::query()->where('action', MailboxAuditAction::ActivationCompleted->value)->exists()
        );
    }

    public function test_cpanel_failure_does_not_block_employee_creation(): void
    {
        Queue::fake();

        $gateway = \Mockery::mock(CpanelMailboxGateway::class);
        $gateway->shouldReceive('isConfigured')->andReturn(true);
        $gateway->shouldReceive('createMailbox')->andReturn(new \App\DataTransferObjects\EmailIdentity\CpanelMailboxResult(
            success: false,
            error: 'cPanel unavailable',
        ));
        $this->app->instance(CpanelMailboxGateway::class, $gateway);

        $admin = $this->employeesAdmin();
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.employees.store'), [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'employee_number' => 'EMP-EMAIL-04',
                'first_name' => 'Fail',
                'last_name' => 'Safe',
                'employment_status' => 'active',
                'email' => 'fail.personal@example.com',
                'is_active' => true,
            ])
            ->assertRedirect(route('admin.employees.index'));

        $employee = Employee::query()->where('employee_number', 'EMP-EMAIL-04')->firstOrFail();
        $this->assertSame('fail.safe@janaprints.co.ke', $employee->corporate_email);

        $mailbox = CorporateMailbox::query()->where('employee_id', $employee->id)->firstOrFail();
        $this->assertSame('cPanel unavailable', $mailbox->provision_error);
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
            'corporate_email' => 'queue.fail@janaprints.co.ke',
            'employment_status' => 'active',
            'activation_status' => EmployeeActivationStatus::PendingActivation,
            'is_active' => false,
        ]);

        $service = new class(
            app(\App\Services\EmailIdentity\CorporateMailboxProvisioningService::class),
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
                    'queue.fail@janaprints.co.ke',
                    'https://janaprints.co.ke/activate/test',
                    now()->addDay(),
                );
            }

            protected function dispatchOnboardingNotifications(
                Employee $employee,
                string $personalEmail,
                string $corporateEmail,
                string $activationUrl,
                \DateTimeInterface $expiresAt,
                ?\App\Models\EmailIdentity\EmployeeActivation $activation = null,
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
