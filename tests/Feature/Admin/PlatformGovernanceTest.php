<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PlatformGovernanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_guest_cannot_access_admin_users(): void
    {
        $this->get(route('admin.users.index'))->assertRedirect(route('admin.login'));
    }

    public function test_user_without_permission_cannot_access_users(): void
    {
        $user = $this->createUserWithRole('Viewer', ['roles.view']);

        $this->actingAs($user)->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_user_with_permission_can_access_users_index(): void
    {
        $user = $this->createUserWithRole('Company Admin', ['users.view']);

        $this->actingAs($user)->get(route('admin.users.index'))->assertOk();
    }

    public function test_company_admin_cannot_edit_user_from_another_company(): void
    {
        $companyA = Company::factory()->create(['code' => 'COA']);
        $companyB = Company::factory()->create(['code' => 'COB']);
        $branchA = Branch::factory()->create(['company_id' => $companyA->id, 'code' => 'BA']);
        $branchB = Branch::factory()->create(['company_id' => $companyB->id, 'code' => 'BB']);

        $admin = $this->createUserWithRole('Company Admin', ['users.view', 'users.edit'], $companyA, $branchA);
        $otherUser = User::factory()->create([
            'company_id' => $companyB->id,
            'default_branch_id' => $branchB->id,
            'email_verified_at' => now(),
        ]);
        $otherUser->assignRole('Viewer');

        $this->actingAs($admin)
            ->get(route('admin.users.edit', $otherUser))
            ->assertForbidden();
    }

    public function test_company_admin_can_create_user_in_own_company(): void
    {
        $company = Company::factory()->create(['code' => 'OWN']);
        $branch = Branch::factory()->create(['company_id' => $company->id, 'code' => 'HQ', 'is_head_office' => true]);

        $admin = $this->createUserWithRole('Company Admin', ['users.view', 'users.create', 'users.edit'], $company, $branch);

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'New Staff',
            'email' => 'staff@example.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'role' => 'Viewer',
            'is_active' => 1,
            'system_account' => 1,
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'staff@example.com',
            'company_id' => $company->id,
        ]);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'is_active' => false,
            'email_verified_at' => now(),
        ]);
        $user->assignRole('Viewer');

        $this->post(route('admin.login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');
    }

    public function test_inactive_super_admin_cannot_bypass_authorization(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => false,
        ]);
        $user->assignRole('Super Admin');

        $this->actingAs($user)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_accountant_and_hr_roles_receive_default_permissions(): void
    {
        $accountant = Role::findByName('Accountant', 'web');
        $hr = Role::findByName('HR', 'web');

        $this->assertTrue($accountant->hasPermissionTo('settings.view'));
        $this->assertTrue($accountant->hasPermissionTo('sales_orders.view'));
        $this->assertTrue($hr->hasPermissionTo('employees.manage'));
        $this->assertFalse($hr->hasPermissionTo('users.create'));
    }

    protected function createUserWithRole(string $role, array $permissions, ?Company $company = null, ?Branch $branch = null): User
    {
        $company ??= Company::factory()->create();
        $branch ??= Branch::factory()->create(['company_id' => $company->id]);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $roleModel = Role::findByName($role, 'web');
        $roleModel->syncPermissions($permissions);
        $user->assignRole($role);

        return $user;
    }
}
