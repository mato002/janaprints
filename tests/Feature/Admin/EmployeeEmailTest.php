<?php

namespace Tests\Feature\Admin;

use App\Enums\IntegrationEmailProvider;
use App\Jobs\Communications\SendEmailMessageJob;
use App\Models\Branch;
use App\Models\Communications\EmailMessage;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Hr\PayrollPayslip;
use App\Models\Integrations\IntegrationEmailSetting;
use App\Models\User;
use App\Support\Hr\PayrollRunService;
use App\Support\TenantContext;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmployeeEmailTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Branch $branch;

    protected Department $department;

    protected User $hrUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);

        Config::set('mailboxes.department.hr', 'hr@janaprints.co.ke');

        $this->company = Company::query()->where('code', 'JANA')->firstOrFail();
        $this->branch = Branch::query()->where('company_id', $this->company->id)->where('code', 'HQ')->firstOrFail();
        $this->department = Department::query()->where('company_id', $this->company->id)->firstOrFail();

        $this->hrUser = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $this->hrUser->assignRole(Role::findByName('HR', 'web'));

        session(['active_company_id' => $this->company->id, 'active_branch_id' => $this->branch->id]);
        app()->instance(TenantContext::class, new TenantContext($this->company, $this->branch, false));

        $this->createIntegration();
    }

    public function test_compose_page_lists_selected_employees(): void
    {
        $employee = $this->createEmployee('EMP-MAIL-01', 'alice@example.com');

        $this->actingAs($this->hrUser)
            ->get(route('admin.employees.email.compose', ['employees' => [$employee->id]]))
            ->assertOk()
            ->assertSee('alice@example.com')
            ->assertSee('EMP-MAIL-01');
    }

    public function test_single_employee_email_queues_message_from_hr_mailbox(): void
    {
        Queue::fake();

        $employee = $this->createEmployee('EMP-MAIL-02', 'bob@example.com');

        $this->actingAs($this->hrUser)
            ->post(route('admin.employees.email.send'), [
                'employees' => [$employee->id],
                'subject' => 'Team update',
                'body' => 'Hello {{name}}',
            ])
            ->assertRedirect(route('admin.employees.index'));

        $message = EmailMessage::query()->latest('id')->first();

        $this->assertNotNull($message);
        $this->assertSame('Team update', $message->subject);
        $this->assertSame('hr@janaprints.co.ke', $message->account?->from_email);
        $this->assertStringContainsString('Hello', $message->body);
        Queue::assertPushed(SendEmailMessageJob::class);
    }

    public function test_multiple_employees_each_receive_individual_email(): void
    {
        Queue::fake();

        $first = $this->createEmployee('EMP-MAIL-03', 'carol@example.com');
        $second = $this->createEmployee('EMP-MAIL-04', 'dave@example.com');

        $this->actingAs($this->hrUser)
            ->post(route('admin.employees.email.send'), [
                'employees' => [$first->id, $second->id],
                'subject' => 'Same announcement',
                'body' => 'Shared message body',
            ])
            ->assertRedirect(route('admin.employees.index'));

        $this->assertSame(2, EmailMessage::query()->count());
        Queue::assertPushed(SendEmailMessageJob::class, 2);
    }

    public function test_email_all_active_staff_with_email(): void
    {
        Queue::fake();

        $this->createEmployee('EMP-MAIL-05', 'eve@example.com');
        $this->createEmployee('EMP-MAIL-06', 'frank@example.com');
        $this->createEmployee('EMP-MAIL-07', null, false);

        $this->actingAs($this->hrUser)
            ->post(route('admin.employees.email.send'), [
                'all' => 1,
                'subject' => 'Company notice',
                'body' => 'Important update',
            ])
            ->assertRedirect(route('admin.employees.index'));

        $this->assertGreaterThanOrEqual(2, EmailMessage::query()->count());
    }

    public function test_user_without_permission_cannot_send_employee_email(): void
    {
        $viewer = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'email_verified_at' => now(),
        ]);
        $viewer->assignRole(Role::findByName('Viewer', 'web'));

        $employee = $this->createEmployee('EMP-MAIL-08', 'guest@example.com');

        $this->actingAs($viewer)
            ->post(route('admin.employees.email.send'), [
                'employees' => [$employee->id],
                'subject' => 'Blocked',
                'body' => 'Should not send',
            ])
            ->assertForbidden();
    }

    public function test_payslip_email_queues_pdf_attachment(): void
    {
        Queue::fake();

        $employee = $this->createEmployee('EMP-PAY-MAIL', 'payslip@example.com');
        $this->seedPayrollCompensation($employee);

        $run = app(PayrollRunService::class)->create($this->company->id, [
            'payroll_group' => 'main',
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'pay_date' => now()->endOfMonth()->toDateString(),
        ], $this->hrUser);

        app(PayrollRunService::class)->generate($run, $this->hrUser);

        $payslip = PayrollPayslip::query()->where('payroll_run_id', $run->id)->firstOrFail();

        $this->actingAs($this->hrUser)
            ->post(route('admin.hr.payroll.payslip.email', $payslip))
            ->assertRedirect();

        $message = EmailMessage::query()->latest('id')->first();

        $this->assertNotNull($message);
        $this->assertSame('hr@janaprints.co.ke', $message->account?->from_email);
        $this->assertDatabaseHas('email_attachments', [
            'email_message_id' => $message->id,
            'attachment_type' => 'payslip_pdf',
        ]);
        $this->assertNull($payslip->fresh()->emailed_at);
        Queue::assertPushed(SendEmailMessageJob::class);
    }

    protected function createEmployee(string $number, ?string $email, bool $active = true): Employee
    {
        return Employee::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'department_id' => $this->department->id,
            'employee_number' => $number,
            'first_name' => 'Test',
            'last_name' => $number,
            'email' => $email,
            'employment_status' => 'active',
            'is_active' => $active,
        ]);
    }

    protected function seedPayrollCompensation(Employee $employee): void
    {
        \App\Models\Hr\EmployeeCompensation::query()->create([
            'company_id' => $this->company->id,
            'employee_id' => $employee->id,
            'basic_salary' => 50000,
            'house_allowance' => 10000,
            'transport_allowance' => 5000,
            'medical_allowance' => 3000,
            'payroll_group' => 'main',
            'effective_from' => now()->startOfYear()->toDateString(),
            'is_active' => true,
        ]);
    }

    protected function createIntegration(): IntegrationEmailSetting
    {
        return IntegrationEmailSetting::query()->create([
            'company_id' => $this->company->id,
            'provider' => IntegrationEmailProvider::Smtp,
            'from_name' => 'Jana Prints HR',
            'from_email' => 'hr@janaprints.test',
            'smtp_host' => 'smtp.test.local',
            'smtp_port' => 587,
            'smtp_encryption' => 'tls',
            'smtp_username' => 'smtp-user',
            'smtp_password' => 'smtp-pass',
            'is_active' => true,
        ]);
    }
}
