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
use Illuminate\Support\Facades\Blade;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LookupSelectComponentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(PlatformConfigurationSeeder::class);
    }

    public function test_renders_plus_button_only_with_permission(): void
    {
        [$company, $branch, $user] = $this->tenantContext(['branches.manage']);

        $this->actingAs($user);

        $html = Blade::render(<<<'BLADE'
            <x-admin.lookup-select
                name="branch_id"
                label="Branch"
                :options="$branches"
                create-route="admin.branches.quick-create"
                refresh-route="admin.lookups.branches"
                permission="branches.manage"
                modal-title="Create branch"
            />
        BLADE, ['branches' => collect([$branch])]);

        $this->assertStringContainsString('erp-lookup-select__add', $html);

        $role = Role::findByName('Sales', 'web');
        $role->syncPermissions(['crm.customers.view']);
        $user->syncRoles(['Sales']);

        $this->actingAs($user);

        $htmlDenied = Blade::render(<<<'BLADE'
            <x-admin.lookup-select
                name="branch_id"
                label="Branch"
                :options="$branches"
                create-route="admin.branches.quick-create"
                refresh-route="admin.lookups.branches"
                permission="branches.manage"
                modal-title="Create branch"
            />
        BLADE, ['branches' => collect([$branch])]);

        $this->assertStringNotContainsString('erp-lookup-select__add', $htmlDenied);
    }

    public function test_hides_plus_button_when_read_only(): void
    {
        [$company, $branch, $user] = $this->tenantContext(['branches.manage']);

        $this->actingAs($user);

        $html = Blade::render(<<<'BLADE'
            <x-admin.lookup-select
                name="branch_id"
                label="Branch"
                :options="$branches"
                :readonly="true"
                create-route="admin.branches.quick-create"
                refresh-route="admin.lookups.branches"
                permission="branches.manage"
                modal-title="Create branch"
            />
        BLADE, ['branches' => collect([$branch])]);

        $this->assertStringNotContainsString('erp-lookup-select__add', $html);
        $this->assertStringContainsString('disabled', $html);
    }

    public function test_hides_component_when_hidden_by_form_control(): void
    {
        [$company, $branch] = $this->tenantContext(['branches.manage']);

        $html = Blade::render(<<<'BLADE'
            <x-admin.lookup-select
                name="branch_id"
                label="Branch"
                :options="$branches"
                :hidden="true"
                create-route="admin.branches.quick-create"
                refresh-route="admin.lookups.branches"
                permission="branches.manage"
                modal-title="Create branch"
            />
        BLADE, ['branches' => collect([$branch])]);

        $this->assertStringNotContainsString('erp-lookup-select', $html);
        $this->assertStringNotContainsString('name="branch_id"', $html);
    }

    /**
     * @param  list<string>  $extraPermissions
     * @return array{0: Company, 1: Branch, 2: User}
     */
    protected function tenantContext(array $extraPermissions = []): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $this->seed(CrmFoundationSeeder::class);

        $permissions = array_values(array_unique([
            'crm.customers.view',
            'crm.customers.create',
            ...$extraPermissions,
        ]));

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $role = Role::findByName('Sales', 'web');
        $role->syncPermissions($permissions);
        $user->assignRole('Sales');

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        return [$company, $branch, $user];
    }
}
