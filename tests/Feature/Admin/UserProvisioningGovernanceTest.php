<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserProvisioningGovernanceTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->company = Company::factory()->create(['code' => 'JANA']);
        $this->branch = Branch::factory()->create([
            'company_id' => $this->company->id,
            'code' => 'HQ',
            'is_head_office' => true,
        ]);
    }

    public function test_hr_role_cannot_create_users(): void
    {
        $hrUser = $this->userWithRole('HR');

        $this->actingAs($hrUser)
            ->get(route('admin.users.create'))
            ->assertForbidden();

        $this->actingAs($hrUser)
            ->post(route('admin.users.store'), $this->userPayload())
            ->assertForbidden();
    }

    public function test_cannot_create_user_without_employee_link_or_system_account_flag(): void
    {
        $admin = $this->companyAdmin();

        $this->actingAs($admin)
            ->post(route('admin.users.store'), $this->userPayload([
                'email' => 'orphan@example.com',
            ]))
            ->assertSessionHasErrors('system_account');
    }

    public function test_can_create_system_account_when_explicitly_confirmed(): void
    {
        $admin = $this->companyAdmin();

        $this->actingAs($admin)
            ->post(route('admin.users.store'), $this->userPayload([
                'email' => 'integration@example.com',
                'system_account' => 1,
            ]))
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'integration@example.com',
            'employee_id' => null,
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_create_user_with_employee_email_without_linking_employee(): void
    {
        $admin = $this->companyAdmin();
        $employee = $this->createEmployee('EMP-GOV-01', 'hire@example.com');

        $this->actingAs($admin)
            ->post(route('admin.users.store'), $this->userPayload([
                'email' => 'hire@example.com',
                'system_account' => 1,
            ]))
            ->assertSessionHasErrors('email');
    }

    public function test_cannot_create_duplicate_login_for_employee_with_existing_user(): void
    {
        $admin = $this->companyAdmin();
        $employee = $this->createEmployee('EMP-GOV-02', 'linked@example.com');

        User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'employee_id' => $employee->id,
            'email' => 'linked@example.com',
            'email_verified_at' => now(),
            'is_active' => false,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.users.store'), $this->userPayload([
                'email' => 'linked@example.com',
                'employee_id' => $employee->id,
            ]))
            ->assertSessionHasErrors('employee_id');
    }

    public function test_can_link_user_to_employee_without_login_when_emails_match(): void
    {
        $admin = $this->companyAdmin();
        $employee = $this->createEmployee('EMP-GOV-03', 'legacy@example.com');

        $this->actingAs($admin)
            ->post(route('admin.users.store'), $this->userPayload([
                'email' => 'legacy@example.com',
                'employee_id' => $employee->id,
            ]))
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'legacy@example.com',
            'employee_id' => $employee->id,
        ]);
    }

    public function test_cannot_unlink_staff_user_from_employee_on_update(): void
    {
        $admin = $this->companyAdmin();
        $employee = $this->createEmployee('EMP-GOV-04', 'staff@example.com');

        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'employee_id' => $employee->id,
            'email' => 'staff@example.com',
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $user->assignRole('Viewer');

        $this->actingAs($admin)
            ->put(route('admin.users.update', $user), [
                'name' => $user->name,
                'email' => $user->email,
                'company_id' => $this->company->id,
                'default_branch_id' => $this->branch->id,
                'employee_id' => '',
                'role' => 'Viewer',
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('employee_id');
    }

    protected function companyAdmin(): User
    {
        return $this->userWithRole('Company Admin', [
            'users.view',
            'users.create',
            'users.edit',
        ]);
    }

    protected function userWithRole(string $role, ?array $permissions = null): User
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $roleModel = Role::findByName($role, 'web');

        if ($permissions !== null) {
            $roleModel->syncPermissions($permissions);
        }

        $user->assignRole($role);

        return $user;
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function userPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Provisioned User',
            'email' => 'provisioned@example.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'role' => 'Viewer',
            'is_active' => 1,
        ], $overrides);
    }

    protected function createEmployee(string $number, string $email): Employee
    {
        return Employee::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'employee_number' => $number,
            'first_name' => 'Test',
            'last_name' => 'Employee',
            'employment_status' => 'active',
            'email' => $email,
            'is_active' => true,
        ]);
    }
}
