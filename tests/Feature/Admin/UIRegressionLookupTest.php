<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\CrmFoundationSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UIRegressionLookupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(PlatformConfigurationSeeder::class);
        $this->seed(CrmFoundationSeeder::class);
    }

    public function test_admin_layout_includes_nested_lookup_modal_host(): void
    {
        [$company, $branch, $user] = $this->tenantContext();

        $this->actingAs($user)
            ->get(route('admin.crm.customers.index'))
            ->assertOk()
            ->assertSee('id="erp-lookup-modal-overlay"', false)
            ->assertSee('id="erp-lookup-modal"', false);
    }

    public function test_lookup_quick_create_form_is_isolated_from_parent_modal_form(): void
    {
        $user = $this->superAdmin();

        $this->actingAs($user)
            ->withHeader('X-Erp-Lookup-Create', '1')
            ->get(route('admin.companies.quick-create'))
            ->assertOk()
            ->assertSee('data-erp-lookup-form', false)
            ->assertDontSee('data-erp-form-modal-panel', false);
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User}
     */
    protected function tenantContext(): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $role = Role::findByName('Sales', 'web');
        $role->syncPermissions(['crm.customers.view', 'crm.customers.create']);
        $user->assignRole('Sales');

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        return [$company, $branch, $user];
    }

    protected function superAdmin(): User
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $role = Role::findByName('Super Admin', 'web');
        $role->syncPermissions(['companies.manage']);
        $user->assignRole('Super Admin');

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        return $user;
    }
}
