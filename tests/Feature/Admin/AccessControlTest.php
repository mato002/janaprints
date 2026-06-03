<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_access_control_hub_is_available(): void
    {
        $admin = User::factory()->create([
            'company_id' => 1,
            'default_branch_id' => 1,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $admin->assignRole('Company Admin');

        $this->actingAs($admin)
            ->get(route('admin.access-control.index'))
            ->assertOk()
            ->assertSee(__('Access Control'))
            ->assertSee(__('Permission Matrix'))
            ->assertDontSee(__('Permissions'));
    }

    public function test_roles_sidebar_route_redirects_to_access_control_roles(): void
    {
        $admin = User::factory()->create([
            'company_id' => 1,
            'default_branch_id' => 1,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $admin->assignRole('Company Admin');

        $this->actingAs($admin)
            ->get(route('admin.roles.index'))
            ->assertRedirect(route('admin.access-control.roles'));
    }

    public function test_roles_index_renders_governance_dashboard(): void
    {
        $admin = User::factory()->create([
            'company_id' => 1,
            'default_branch_id' => 1,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $admin->assignRole('Company Admin');

        $this->actingAs($admin)
            ->get(route('admin.access-control.roles'))
            ->assertOk()
            ->assertSee(__('Security Groups'))
            ->assertSee(__('Roles').':')
            ->assertSee(__('Active').':')
            ->assertSee(__('Broken roles'))
            ->assertSee(__('Draft roles'))
            ->assertSee(__('Modules'))
            ->assertSee(__('Access coverage'))
            ->assertSee(__('Finance'));
    }

    public function test_create_role_can_clone_permissions_from_existing_role(): void
    {
        $admin = User::factory()->create([
            'company_id' => 1,
            'default_branch_id' => 1,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $admin->assignRole('Company Admin');

        $source = Role::findByName('Storekeeper', 'web');

        $this->actingAs($admin)
            ->post(route('admin.roles.store'), [
                'name' => 'Warehouse Lead',
                'clone_from' => $source->id,
            ])
            ->assertRedirect();

        $clone = Role::findByName('Warehouse Lead', 'web');

        $this->assertTrue($clone->hasPermissionTo('inventory.view'));
        $this->assertFalse($clone->users()->exists());
    }

    public function test_role_workspace_shows_filtered_permission_matrix(): void
    {
        $admin = User::factory()->create([
            'company_id' => 1,
            'default_branch_id' => 1,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $admin->assignRole('Company Admin');

        $role = Role::findByName('Sales', 'web');

        $this->actingAs($admin)
            ->get(route('admin.roles.show', $role))
            ->assertOk()
            ->assertSee(__('Save access rights'))
            ->assertSee(__('Capability'))
            ->assertSee(__('Enable module'))
            ->assertSee(__('Disable module'))
            ->assertSee(__('Users'))
            ->assertSee(__('CRM'));
    }

    public function test_permission_matrix_page_loads_for_role(): void
    {
        $admin = User::factory()->create([
            'company_id' => 1,
            'default_branch_id' => 1,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $admin->assignRole('Company Admin');

        $this->actingAs($admin)
            ->get(route('admin.access-control.matrix', ['role' => 'Sales']))
            ->assertOk()
            ->assertSee(__('Permission Matrix'))
            ->assertSee(__('Quotations'));
    }

    public function test_assigning_permissions_still_uses_existing_keys(): void
    {
        $admin = User::factory()->create([
            'company_id' => 1,
            'default_branch_id' => 1,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $admin->assignRole('Company Admin');

        $role = Role::findByName('Viewer', 'web');

        $this->actingAs($admin)
            ->put(route('admin.roles.permissions.update', $role), [
                'permissions' => ['crm.customers.view', 'crm.leads.view'],
            ])
            ->assertRedirect(route('admin.roles.show', $role));

        $this->assertTrue($role->fresh()->hasPermissionTo('crm.customers.view'));
        $this->assertFalse($role->fresh()->hasPermissionTo('crm.customers.create'));
    }
}
