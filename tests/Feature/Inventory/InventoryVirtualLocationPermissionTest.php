<?php

namespace Tests\Feature\Inventory;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Support\AccessControl\PermissionCatalog;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InventoryVirtualLocationPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_permissions_exist_in_catalog_and_seeder(): void
    {
        foreach (['inventory.virtual-locations.view', 'inventory.virtual-locations.manage'] as $permission) {
            $this->assertTrue(
                Permission::query()->where('name', $permission)->exists(),
                "Missing permission: {$permission}",
            );
        }

        $catalog = app(PermissionCatalog::class);
        $inventory = collect($catalog->matrixSections())->firstWhere('module_key', 'inventory');
        $permissions = collect($inventory['rows'])
            ->flatMap(fn (array $row) => collect($row['cells'])->pluck('permission'))
            ->merge(collect($inventory['rows'])->flatMap(fn (array $row) => collect($row['extra'] ?? [])->pluck('permission')))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $this->assertContains('inventory.virtual-locations.view', $permissions);
        $this->assertContains('inventory.virtual-locations.manage', $permissions);
    }

    public function test_relevant_roles_receive_expected_permissions(): void
    {
        $this->assertTrue(Role::findByName('Company Admin', 'web')->hasPermissionTo('inventory.virtual-locations.view'));
        $this->assertTrue(Role::findByName('Company Admin', 'web')->hasPermissionTo('inventory.virtual-locations.manage'));
        $this->assertTrue(Role::findByName('Storekeeper', 'web')->hasPermissionTo('inventory.virtual-locations.view'));
        $this->assertTrue(Role::findByName('Production', 'web')->hasPermissionTo('inventory.virtual-locations.view'));
        $this->assertTrue(Role::findByName('Accountant', 'web')->hasPermissionTo('inventory.virtual-locations.view'));
    }

    public function test_unauthorized_users_cannot_manage_virtual_locations(): void
    {
        [$company, $branch, $user] = $this->viewerContext();

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.inventory.virtual-locations.ensure-defaults'))
            ->assertForbidden();
    }

    public function test_authorized_users_can_view_virtual_locations_page(): void
    {
        [$company, $branch, $user] = $this->storekeeperContext();

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.inventory.virtual-locations.index'))
            ->assertOk()
            ->assertSee(__('Virtual locations'), false)
            ->assertSee('VIRTUAL-WIP', false);
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User}
     */
    protected function viewerContext(): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        Role::findByName('Viewer', 'web')->syncPermissions(['inventory.view']);
        $user->assignRole('Viewer');

        return [$company, $branch, $user];
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User}
     */
    protected function storekeeperContext(): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        Role::findByName('Storekeeper', 'web')->syncPermissions(['inventory.virtual-locations.view']);
        $user->assignRole('Storekeeper');

        return [$company, $branch, $user];
    }
}
