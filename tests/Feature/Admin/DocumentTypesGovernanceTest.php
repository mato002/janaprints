<?php

namespace Tests\Feature\Admin;

use App\Enums\DocumentTypeStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Platform\ApprovalRule;
use App\Models\Platform\DocumentTypeDefinition;
use App\Models\Platform\NumberingSequence;
use App\Models\SecurityAuditEvent;
use App\Models\User;
use App\Support\Platform\DocumentTypesService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DocumentTypesGovernanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(PlatformConfigurationSeeder::class);
    }

    public function test_document_types_dashboard_is_accessible(): void
    {
        $user = $this->userWithPermissions(['configuration.document_types.view']);
        $company = Company::query()->where('code', 'JANA')->firstOrFail();

        $this->actingAs($user)
            ->get(route('admin.settings.document-types.index', ['company_id' => $company->id]))
            ->assertOk()
            ->assertSee(__('Document Types'))
            ->assertSee(__('Quotation'))
            ->assertSee(__('Purchase Order'));
    }

    public function test_user_without_permission_cannot_view_document_types(): void
    {
        $user = $this->userWithPermissions(['settings.view']);

        $this->actingAs($user)
            ->get(route('admin.settings.document-types.index'))
            ->assertForbidden();
    }

    public function test_admin_can_create_document_type(): void
    {
        $user = $this->userWithPermissions([
            'configuration.document_types.view',
            'configuration.document_types.create',
        ]);
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        $this->actingAs($user)
            ->post(route('admin.settings.document-types.store'), [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'code' => 'custom_doc',
                'name' => 'Custom Document',
                'module' => 'commercial',
                'prefix' => 'CUST',
                'number_series_key' => 'quotation',
                'approval_required' => '1',
                'approval_levels' => '2',
                'approval_rule_type' => 'quotation_approval',
                'retention_period_days' => '365',
                'auto_numbering' => '1',
                'audit_tracking' => '1',
            ])
            ->assertRedirect(route('admin.settings.document-types.index', [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
            ]));

        $definition = DocumentTypeDefinition::query()
            ->where('company_id', $company->id)
            ->where('branch_id', $branch->id)
            ->where('code', 'custom_doc')
            ->first();

        $this->assertNotNull($definition);
        $this->assertSame('Custom Document', $definition->name);
        $this->assertTrue($definition->approval_required);
        $this->assertSame('quotation', $definition->number_series_key);
        $this->assertTrue($definition->workflow_json['audit_tracking']);

        $this->assertDatabaseHas('security_audit_events', [
            'action' => 'document_type.created',
            'module' => 'configuration',
            'entity' => 'document_type',
        ]);
    }

    public function test_admin_can_edit_document_type(): void
    {
        $user = $this->userWithPermissions([
            'configuration.document_types.view',
            'configuration.document_types.edit',
        ]);
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        $definition = DocumentTypeDefinition::query()
            ->where('company_id', $company->id)
            ->where('branch_id', $branch->id)
            ->where('code', 'quotation')
            ->firstOrFail();

        $this->actingAs($user)
            ->put(route('admin.settings.document-types.update', ['documentTypeDefinition' => $definition->id]), [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'name' => 'Sales Quotation',
                'module' => 'commercial',
                'prefix' => 'SQ',
                'number_series_key' => 'quotation',
                'approval_required' => '1',
                'approval_levels' => '3',
                'approval_rule_type' => 'quotation_approval',
                'retention_period_days' => '3000',
                'auto_numbering' => '1',
                'audit_tracking' => '1',
            ])
            ->assertRedirect(route('admin.settings.document-types.index', [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
            ]));

        $definition->refresh();

        $this->assertSame('Sales Quotation', $definition->name);
        $this->assertSame('SQ', $definition->prefix);
        $this->assertSame(3, $definition->approval_levels);

        $this->assertTrue(
            SecurityAuditEvent::query()->where('action', 'document_type.updated')->exists()
        );
    }

    public function test_admin_can_activate_document_type(): void
    {
        $user = $this->userWithPermissions([
            'configuration.document_types.view',
            'configuration.document_types.deactivate',
            'configuration.document_types.activate',
        ]);
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        $definition = DocumentTypeDefinition::query()
            ->where('company_id', $company->id)
            ->where('branch_id', $branch->id)
            ->where('code', 'invoice')
            ->firstOrFail();

        $definition->update(['status' => DocumentTypeStatus::Inactive]);

        $this->actingAs($user)
            ->patch(route('admin.settings.document-types.activate', ['documentTypeDefinition' => $definition->id]), [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
            ])
            ->assertRedirect();

        $this->assertSame(DocumentTypeStatus::Active, $definition->fresh()->status);
    }

    public function test_admin_can_deactivate_document_type(): void
    {
        $user = $this->userWithPermissions([
            'configuration.document_types.view',
            'configuration.document_types.deactivate',
        ]);
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        $definition = DocumentTypeDefinition::query()
            ->where('company_id', $company->id)
            ->where('branch_id', $branch->id)
            ->where('code', 'invoice')
            ->firstOrFail();

        $this->actingAs($user)
            ->patch(route('admin.settings.document-types.deactivate', ['documentTypeDefinition' => $definition->id]), [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
            ])
            ->assertRedirect();

        $this->assertSame(DocumentTypeStatus::Inactive, $definition->fresh()->status);
    }

    public function test_deactivate_preserves_embedded_workspace_context_on_redirect(): void
    {
        $user = $this->userWithPermissions([
            'configuration.document_types.view',
            'configuration.document_types.deactivate',
        ]);
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        $definition = DocumentTypeDefinition::query()
            ->where('company_id', $company->id)
            ->where('branch_id', $branch->id)
            ->where('code', 'invoice')
            ->firstOrFail();

        $this->actingAs($user)
            ->patch(route('admin.settings.document-types.deactivate', ['documentTypeDefinition' => $definition->id]), [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'embedded' => '1',
            ])
            ->assertRedirect(route('admin.settings.document-types.index', [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'embedded' => '1',
            ]))
            ->assertSessionHas('status');
    }

    public function test_service_resolves_number_series_link(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $service = app(DocumentTypesService::class);

        NumberingSequence::query()
            ->where('company_id', $company->id)
            ->where('branch_id', $branch->id)
            ->where('document_type', 'quotation')
            ->update(['next_number' => 42]);

        $sequence = $service->resolveNumberSeries($company->id, $branch->id, 'quotation');

        $this->assertNotNull($sequence);
        $this->assertSame('quotation', $sequence->document_type);
        $this->assertSame(42, $sequence->next_number);
    }

    public function test_service_resolves_approval_rule_link(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $service = app(DocumentTypesService::class);

        ApprovalRule::query()
            ->where('company_id', $company->id)
            ->where('rule_type', 'quotation_approval')
            ->update(['is_enabled' => true]);

        $rule = $service->resolveApprovalRule($company->id, $branch->id, 'quotation');

        $this->assertNotNull($rule);
        $this->assertSame('quotation_approval', $rule->rule_type);
        $this->assertTrue($service->requiresApproval($company->id, $branch->id, 'quotation'));
    }

    public function test_inactive_document_type_does_not_resolve_integrations(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $service = app(DocumentTypesService::class);

        DocumentTypeDefinition::query()
            ->where('company_id', $company->id)
            ->where('branch_id', $branch->id)
            ->where('code', 'quotation')
            ->update(['status' => DocumentTypeStatus::Inactive]);

        $this->assertNull($service->resolveNumberSeries($company->id, $branch->id, 'quotation'));
        $this->assertNull($service->resolveApprovalRule($company->id, $branch->id, 'quotation'));
        $this->assertFalse($service->requiresApproval($company->id, $branch->id, 'quotation'));
    }

    public function test_edit_permission_is_enforced(): void
    {
        $user = $this->userWithPermissions(['configuration.document_types.view']);
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        $definition = DocumentTypeDefinition::query()
            ->where('company_id', $company->id)
            ->where('branch_id', $branch->id)
            ->where('code', 'quotation')
            ->firstOrFail();

        $this->actingAs($user)
            ->put(route('admin.settings.document-types.update', ['documentTypeDefinition' => $definition->id]), [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'name' => 'Blocked Update',
                'module' => 'commercial',
            ])
            ->assertForbidden();
    }

    /**
     * @param  list<string>  $permissions
     */
    protected function userWithPermissions(array $permissions): User
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $role = Role::create(['name' => 'Document Types Tester', 'guard_name' => 'web']);
        $role->syncPermissions($permissions);
        $user->assignRole($role);

        return $user;
    }
}
