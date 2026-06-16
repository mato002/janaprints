<?php

namespace Tests\Feature\EmailIdentity;

use App\Enums\EmailIdentity\MailboxAuditAction;
use App\Jobs\EmailIdentity\SendEmployeeOnboardingSmsJob;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use App\Services\EmailIdentity\DepartmentMailboxCatalogService;
use App\Services\EmailIdentity\EmailIdentityReadinessService;
use App\Services\EmailIdentity\EmailSenderResolver;
use App\Services\EmailIdentity\EmployeeDefaultRoleService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmailCommunicationHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);

        config([
            'mailboxes.department.hr' => 'hr@janaprints.co.ke',
            'mail.from.address' => 'fallback@janaprints.co.ke',
        ]);
    }

    public function test_sender_resolver_returns_hr_for_onboarding(): void
    {
        $resolver = app(EmailSenderResolver::class);
        $resolution = $resolver->resolve('employee_onboarding');

        $this->assertSame('hr', $resolution->mailboxPurpose);
        $this->assertSame('hr@janaprints.co.ke', $resolution->address);
        $this->assertTrue($resolution->configured);
        $this->assertFalse($resolution->usedFallback);
    }

    public function test_sender_resolver_falls_back_safely(): void
    {
        config(['mailboxes.department.hr' => null]);

        $resolver = app(EmailSenderResolver::class);
        $resolution = $resolver->resolve('employee_onboarding');

        $this->assertSame('fallback@janaprints.co.ke', $resolution->address);
        $this->assertFalse($resolution->configured);
        $this->assertTrue($resolution->usedFallback);
    }

    public function test_staff_role_fallback_when_staff_exists(): void
    {
        $service = app(EmployeeDefaultRoleService::class);

        $this->assertTrue($service->staffRoleExists());
        $this->assertSame('Staff', $service->resolveDefaultRole());
    }

    public function test_viewer_fallback_when_staff_missing(): void
    {
        Role::query()->where('name', 'Staff')->delete();

        $service = app(EmployeeDefaultRoleService::class);

        $this->assertTrue($service->staffRoleMissing());
        $this->assertSame('Viewer', $service->resolveDefaultRole());
    }

    protected function getEmailIdentityPage(User $admin): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($admin)
            ->withHeaders(['Turbo-Frame' => 'module-workspace-content'])
            ->get(route('admin.email-identity.index', ['embedded' => '1']));
    }

    public function test_department_mailbox_visibility_page_loads(): void
    {
        $admin = $this->adminUser();

        $this->getEmailIdentityPage($admin)
            ->assertOk()
            ->assertSee('hr@janaprints.co.ke', false)
            ->assertSee(__('Human Resources'), false);
    }

    public function test_readiness_panel_shows_onboarding_mailer_status(): void
    {
        config([
            'mail.mailers.onboarding.host' => 'mail.janaprints.co.ke',
            'mail.mailers.onboarding.username' => 'info@janaprints.co.ke',
        ]);

        $admin = $this->adminUser();

        $this->getEmailIdentityPage($admin)
            ->assertOk()
            ->assertSee(__('Onboarding SMTP mailer'), false)
            ->assertSee('mail.janaprints.co.ke', false);
    }

    public function test_sms_skipped_does_not_block_onboarding(): void
    {
        Queue::fake();

        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->post(route('admin.employees.store'), [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'employee_number' => 'EMP-SMS-HARD',
                'first_name' => 'Sms',
                'last_name' => 'Ready',
                'phone' => '254712345678',
                'employment_status' => 'active',
                'email' => 'sms.ready@example.com',
                'is_active' => true,
            ])
            ->assertRedirect(route('admin.employees.index'));

        $this->assertDatabaseHas('employees', ['employee_number' => 'EMP-SMS-HARD']);
        Queue::assertNotPushed(SendEmployeeOnboardingSmsJob::class);
    }

    public function test_queue_readiness_warning_renders_in_production(): void
    {
        app()->detectEnvironment(fn () => 'production');
        config(['queue.default' => 'sync']);

        $checks = app(EmailIdentityReadinessService::class)->checks(
            Company::query()->where('code', 'JANA')->value('id'),
        );

        $queueCheck = collect($checks)->firstWhere('key', 'queue_driver');
        $this->assertSame('warning', $queueCheck['status']);
    }

    public function test_mailbox_catalog_lists_configured_entries(): void
    {
        $entries = app(DepartmentMailboxCatalogService::class)->entries();
        $hr = collect($entries)->firstWhere('purpose', 'hr');

        $this->assertNotNull($hr);
        $this->assertTrue($hr['configured']);
        $this->assertSame('hr@janaprints.co.ke', $hr['address']);
    }

    protected function adminUser(): User
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        Permission::findOrCreate('integrations.view', 'web');
        Permission::findOrCreate('employees.manage', 'web');
        Permission::findOrCreate('integrations.manage', 'web');
        $user->givePermissionTo(['integrations.view', 'employees.manage', 'integrations.manage']);

        session([
            'active_company_id' => $company->id,
            'active_branch_id' => $branch->id,
        ]);

        return $user;
    }
}
