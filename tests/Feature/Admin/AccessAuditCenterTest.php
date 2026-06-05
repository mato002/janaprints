<?php

namespace Tests\Feature\Admin;

use App\Enums\SecurityAuditRiskLevel;
use App\Models\Branch;
use App\Models\Company;
use App\Models\SecurityAuditEvent;
use App\Models\User;
use App\Services\Security\SecurityAuditService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AccessAuditCenterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_security_audit_event_is_created(): void
    {
        $admin = $this->companyAdmin();

        $this->actingAs($admin);

        app(SecurityAuditService::class)->record(
            action: 'login',
            subject: $admin,
            userId: $admin->id,
            module: 'authentication',
            entity: 'user',
        );

        $this->assertDatabaseHas('security_audit_events', [
            'user_id' => $admin->id,
            'company_id' => $admin->company_id,
            'action' => 'login',
            'module' => 'authentication',
        ]);
    }

    public function test_admin_can_view_audit_workspace(): void
    {
        $admin = $this->companyAdmin();
        $this->seedAuditEvent($admin, 'permission_assignment', SecurityAuditRiskLevel::Critical);

        $this->actingAs($admin)
            ->get(route('admin.security.audit.index'))
            ->assertOk()
            ->assertSee(__('Access Audit'))
            ->assertSee(__('Permission assignment changed'));
    }

    public function test_filters_narrow_audit_results(): void
    {
        $admin = $this->companyAdmin();
        $this->seedAuditEvent($admin, 'login', SecurityAuditRiskLevel::Low, 'authentication');
        $this->seedAuditEvent($admin, 'failed_login', SecurityAuditRiskLevel::High, 'authentication');

        $this->actingAs($admin)
            ->get(route('admin.security.audit.index', ['risk_level' => 'high']))
            ->assertOk()
            ->assertSee(__('Failed login attempt'))
            ->assertDontSee(__('User signed in'), false);
    }

    public function test_audit_detail_endpoint_returns_change_tracking(): void
    {
        $admin = $this->companyAdmin();
        $event = SecurityAuditEvent::query()->create([
            'company_id' => $admin->company_id,
            'branch_id' => $admin->default_branch_id,
            'user_id' => $admin->id,
            'module' => 'roles',
            'entity' => 'role',
            'action' => 'permission_assignment',
            'description' => __('Permission assignment changed'),
            'risk_level' => SecurityAuditRiskLevel::Critical,
            'before_values' => ['permissions' => ['users.view']],
            'after_values' => ['permissions' => ['users.view', 'users.edit']],
            'changed_fields' => ['permissions'],
            'occurred_at' => now(),
        ]);

        $this->actingAs($admin)
            ->getJson(route('admin.security.audit.show', $event))
            ->assertOk()
            ->assertJsonPath('action', 'permission_assignment')
            ->assertJsonPath('before_values.permissions.0', 'users.view')
            ->assertJsonPath('after_values.permissions.1', 'users.edit')
            ->assertJsonPath('changed_fields.0', 'permissions');
    }

    public function test_export_requires_export_permission(): void
    {
        $viewer = $this->viewerWithoutExport();

        $this->actingAs($viewer)
            ->get(route('admin.security.audit.export', ['format' => 'csv']))
            ->assertForbidden();
    }

    public function test_authorized_user_can_export_csv(): void
    {
        $admin = $this->companyAdmin();
        $this->seedAuditEvent($admin, 'login', SecurityAuditRiskLevel::Low, 'authentication');

        $response = $this->actingAs($admin)
            ->get(route('admin.security.audit.export', ['format' => 'csv']));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_permission_enforcement_blocks_view_without_rights(): void
    {
        $user = User::factory()->create([
            'company_id' => 1,
            'default_branch_id' => 1,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $user->assignRole('Designer');

        $this->actingAs($user)
            ->get(route('admin.security.audit.index'))
            ->assertForbidden();
    }

    public function test_login_creates_security_audit_event(): void
    {
        $admin = $this->companyAdmin();

        $this->post(route('admin.login'), [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertDatabaseHas('security_audit_events', [
            'user_id' => $admin->id,
            'action' => 'login',
            'module' => 'authentication',
        ]);
    }

    public function test_failed_login_creates_security_audit_event(): void
    {
        $admin = $this->companyAdmin();

        $this->post(route('admin.login'), [
            'email' => $admin->email,
            'password' => 'wrong-password',
        ]);

        $this->assertDatabaseHas('security_audit_events', [
            'action' => 'failed_login',
            'module' => 'authentication',
        ]);
    }

    public function test_permission_update_tracks_before_and_after_values(): void
    {
        $admin = $this->companyAdmin();
        $role = Role::findByName('Viewer', 'web');

        $this->actingAs($admin)
            ->put(route('admin.roles.permissions.update', $role), [
                'permissions' => ['users.view', 'roles.view', 'security.audit.view'],
            ])
            ->assertRedirect();

        $event = SecurityAuditEvent::query()
            ->where('action', 'permission_assignment')
            ->latest('id')
            ->first();

        $this->assertNotNull($event);
        $this->assertContains('users.view', $event->before_values['permissions'] ?? []);
        $this->assertContains('security.audit.view', $event->after_values['permissions'] ?? []);
    }

    public function test_security_access_section_links_to_access_audit(): void
    {
        $admin = $this->companyAdmin();

        $this->actingAs($admin)
            ->get(route('admin.workspaces.administration.section', ['section' => 'security-access']))
            ->assertOk()
            ->assertSee(route('admin.security.audit.index'), false)
            ->assertDontSee(__('Coming Soon'), false);
    }

    protected function companyAdmin(): User
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $user->assignRole('Company Admin');

        return $user;
    }

    protected function viewerWithoutExport(): User
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $user->assignRole('Viewer');

        return $user;
    }

    protected function seedAuditEvent(
        User $admin,
        string $action,
        SecurityAuditRiskLevel $risk,
        string $module = 'users',
    ): SecurityAuditEvent {
        return SecurityAuditEvent::query()->create([
            'company_id' => $admin->company_id,
            'branch_id' => $admin->default_branch_id,
            'user_id' => $admin->id,
            'module' => $module,
            'entity' => 'user',
            'action' => $action,
            'description' => match ($action) {
                'permission_assignment' => __('Permission assignment changed'),
                'failed_login' => __('Failed login attempt'),
                default => __('User signed in'),
            },
            'risk_level' => $risk,
            'occurred_at' => now(),
        ]);
    }
}
