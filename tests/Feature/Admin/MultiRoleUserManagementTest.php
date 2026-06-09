<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MultiRoleUserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_single_role_assignment_still_works(): void
    {
        [$company, $branch, $admin] = $this->adminContext();

        $this->actingAs($admin)
            ->post(route('admin.users.store'), $this->userPayload($company, $branch, [
                'name' => 'Single Role User',
                'email' => 'single-role@example.com',
                'roles' => ['Viewer'],
                'primary_role' => 'Viewer',
            ]))
            ->assertRedirect(route('admin.users.index'));

        $user = User::query()->where('email', 'single-role@example.com')->firstOrFail();

        $this->assertSame(['Viewer'], $user->getRoleNames()->sort()->values()->all());
        $this->assertSame('Viewer', $user->primary_role);
        $this->assertSame('Viewer', $user->roleSummaryLabel());
    }

    public function test_multi_role_assignment_with_primary_role(): void
    {
        [$company, $branch, $admin] = $this->adminContext();

        $this->actingAs($admin)
            ->post(route('admin.users.store'), $this->userPayload($company, $branch, [
                'name' => 'Dual Role User',
                'email' => 'dual-role@example.com',
                'roles' => ['HR', 'Branch Manager'],
                'primary_role' => 'HR',
            ]))
            ->assertRedirect(route('admin.users.index'));

        $user = User::query()->where('email', 'dual-role@example.com')->firstOrFail();

        $this->assertSame(
            ['Branch Manager', 'HR'],
            $user->getRoleNames()->sort()->values()->all(),
        );
        $this->assertSame('HR', $user->primary_role);
        $this->assertSame('HR (+ Branch Manager)', $user->roleSummaryLabel());
    }

    public function test_permissions_aggregate_across_assigned_roles(): void
    {
        [$company, $branch, $admin] = $this->adminContext();

        $this->actingAs($admin)
            ->post(route('admin.users.store'), $this->userPayload($company, $branch, [
                'name' => 'Aggregated Permissions User',
                'email' => 'aggregated@example.com',
                'roles' => ['Accountant', 'Storekeeper'],
                'primary_role' => 'Accountant',
            ]))
            ->assertRedirect(route('admin.users.index'));

        $user = User::query()->where('email', 'aggregated@example.com')->firstOrFail();
        $user->refresh();

        $this->assertTrue($user->hasPermissionTo('accounting.journals.create'));
        $this->assertTrue($user->hasPermissionTo('inventory.create'));
    }

    public function test_role_removal_updates_assignments_and_primary_role(): void
    {
        [$company, $branch, $admin] = $this->adminContext();

        $this->actingAs($admin)
            ->post(route('admin.users.store'), $this->userPayload($company, $branch, [
                'name' => 'Role Removal User',
                'email' => 'role-removal@example.com',
                'roles' => ['Production', 'Storekeeper'],
                'primary_role' => 'Production',
            ]))
            ->assertRedirect(route('admin.users.index'));

        $user = User::query()->where('email', 'role-removal@example.com')->firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.users.update', $user), $this->userPayload($company, $branch, [
                'name' => 'Role Removal User',
                'email' => 'role-removal@example.com',
                'roles' => ['Storekeeper'],
                'primary_role' => 'Storekeeper',
            ]))
            ->assertRedirect(route('admin.users.index'));

        $user->refresh();

        $this->assertSame(['Storekeeper'], $user->getRoleNames()->sort()->values()->all());
        $this->assertSame('Storekeeper', $user->primary_role);
        $this->assertFalse($user->hasRole('Production'));
        $this->assertTrue($user->hasPermissionTo('inventory.create'));
        $this->assertFalse($user->hasPermissionTo('production.create'));
    }

    public function test_primary_role_must_be_one_of_selected_roles(): void
    {
        [$company, $branch, $admin] = $this->adminContext();

        $this->actingAs($admin)
            ->post(route('admin.users.store'), $this->userPayload($company, $branch, [
                'name' => 'Invalid Primary Role',
                'email' => 'invalid-primary@example.com',
                'roles' => ['Viewer'],
                'primary_role' => 'HR',
            ]))
            ->assertSessionHasErrors('primary_role');
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User}
     */
    protected function adminContext(): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        $admin = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $admin->assignRole('Company Admin');
        $admin->update(['primary_role' => 'Company Admin']);

        return [$company, $branch, $admin];
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function userPayload(Company $company, Branch $branch, array $overrides = []): array
    {
        return array_merge([
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'is_active' => 1,
        ], $overrides);
    }
}
