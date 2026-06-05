<?php

namespace Tests\Feature\Admin;

use App\Enums\SecurityAuditRiskLevel;
use App\Models\Branch;
use App\Models\Company;
use App\Models\SecurityAuditEvent;
use App\Models\User;
use App\Services\Operations\ComplianceAuditService;
use App\Services\Security\SecurityAuditService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuditLogsCenterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_audit_logs_dashboard_renders_for_authorized_admin(): void
    {
        $admin = $this->companyAdmin();
        $this->seedComplianceEvent($admin, 'permission_assignment', SecurityAuditRiskLevel::Critical);

        $this->actingAs($admin)
            ->get(route('admin.operations.audit.index'))
            ->assertOk()
            ->assertSee(__('Audit Logs'))
            ->assertSee(__('Permission Changed'))
            ->assertSee(__('Old Value'))
            ->assertSee(__('New Value'));
    }

    public function test_compliance_scope_excludes_authentication_events(): void
    {
        $admin = $this->companyAdmin();

        SecurityAuditEvent::query()->create([
            'company_id' => $admin->company_id,
            'branch_id' => $admin->default_branch_id,
            'user_id' => $admin->id,
            'module' => 'authentication',
            'entity' => 'user',
            'action' => 'login',
            'description' => __('User signed in'),
            'risk_level' => SecurityAuditRiskLevel::Low,
            'occurred_at' => now(),
        ]);

        $this->seedComplianceEvent($admin, 'permission_assignment', SecurityAuditRiskLevel::Critical);

        $this->actingAs($admin);
        $events = app(ComplianceAuditService::class)->paginate([])->items();

        $this->assertCount(1, $events);
        $this->assertSame('permission_assignment', $events[0]->action);
    }

    public function test_user_created_event_appears_in_audit_logs(): void
    {
        $admin = $this->companyAdmin();

        SecurityAuditEvent::query()->create([
            'company_id' => $admin->company_id,
            'branch_id' => $admin->default_branch_id,
            'user_id' => $admin->id,
            'module' => 'users',
            'entity' => 'user',
            'action' => 'created',
            'description' => __('User created'),
            'after_values' => ['email' => 'new.user@example.com'],
            'risk_level' => SecurityAuditRiskLevel::Low,
            'occurred_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.operations.audit.index'))
            ->assertOk()
            ->assertSee(__('User Created'));
    }

    public function test_export_requires_export_permission(): void
    {
        $viewer = $this->auditViewer();

        $this->actingAs($viewer)
            ->get(route('admin.operations.audit.export', ['format' => 'csv']))
            ->assertForbidden();
    }

    public function test_authorized_admin_can_export_csv(): void
    {
        $admin = $this->companyAdmin();
        $this->seedComplianceEvent($admin, 'inventory_adjusted', SecurityAuditRiskLevel::High);

        $response = $this->actingAs($admin)
            ->get(route('admin.operations.audit.export', ['format' => 'csv']));

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
            ->get(route('admin.operations.audit.index'))
            ->assertForbidden();
    }

    public function test_system_operations_section_links_to_audit_logs(): void
    {
        $admin = $this->companyAdmin();

        $this->actingAs($admin)
            ->get(route('admin.workspaces.administration.section', ['section' => 'system-operations']))
            ->assertOk()
            ->assertSee(route('admin.operations.audit.index'), false)
            ->assertSee(__('Audit Logs'));
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
            'after_values' => ['permissions' => ['users.view', 'operations.audit.view']],
            'changed_fields' => ['permissions'],
            'occurred_at' => now(),
        ]);

        $this->actingAs($admin)
            ->getJson(route('admin.operations.audit.show', $event))
            ->assertOk()
            ->assertJsonPath('action', __('Permission Changed'))
            ->assertJsonPath('old_value.permissions.0', 'users.view')
            ->assertJsonPath('new_value.permissions.1', 'operations.audit.view');
    }

    public function test_security_audit_service_records_compliance_events(): void
    {
        $admin = $this->companyAdmin();

        $this->actingAs($admin);

        app(SecurityAuditService::class)->record(
            action: 'number_series_changed',
            before: ['quotation' => ['prefix' => 'Q']],
            after: ['quotation' => ['prefix' => 'QT']],
            module: 'configuration',
            entity: 'number_series',
        );

        $this->assertDatabaseHas('security_audit_events', [
            'action' => 'number_series_changed',
            'module' => 'configuration',
            'entity' => 'number_series',
        ]);
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

    protected function auditViewer(): User
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $user->assignRole('Viewer');
        $user->givePermissionTo('operations.audit.view');

        return $user;
    }

    protected function seedComplianceEvent(
        User $admin,
        string $action,
        SecurityAuditRiskLevel $risk,
    ): SecurityAuditEvent {
        return SecurityAuditEvent::query()->create([
            'company_id' => $admin->company_id,
            'branch_id' => $admin->default_branch_id,
            'user_id' => $admin->id,
            'module' => 'roles',
            'entity' => 'role',
            'action' => $action,
            'description' => __('Permission assignment changed'),
            'risk_level' => $risk,
            'before_values' => ['permissions' => ['users.view']],
            'after_values' => ['permissions' => ['users.view', 'roles.view']],
            'changed_fields' => ['permissions'],
            'occurred_at' => now(),
        ]);
    }
}
