<?php

namespace Tests\Feature\Inventory;

use App\Support\AccessControl\PermissionCatalog;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PermissionCatalogParityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_new_inventory_governance_permissions_exist_in_catalog_and_seeder(): void
    {
        $expected = [
            'inventory.classification.manage',
            'inventory.variance-reasons.view',
            'inventory.variance-reasons.manage',
            'inventory.reconcile.approve',
        ];

        foreach ($expected as $permission) {
            $this->assertTrue(
                Permission::query()->where('name', $permission)->exists(),
                "Missing seeded permission: {$permission}",
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

        foreach ($expected as $permission) {
            $this->assertContains($permission, $permissions, "Missing catalog permission: {$permission}");
        }
    }

    public function test_relevant_roles_receive_expected_governance_permissions(): void
    {
        $companyAdmin = Role::findByName('Company Admin', 'web');
        $storekeeper = Role::findByName('Storekeeper', 'web');
        $accountant = Role::findByName('Accountant', 'web');

        foreach (['inventory.classification.manage', 'inventory.variance-reasons.view', 'inventory.variance-reasons.manage'] as $permission) {
            $this->assertTrue($companyAdmin->hasPermissionTo($permission));
        }

        $this->assertTrue($storekeeper->hasPermissionTo('inventory.variance-reasons.view'));
        $this->assertTrue($storekeeper->hasPermissionTo('inventory.variance-reasons.manage'));
        $this->assertTrue($storekeeper->hasPermissionTo('inventory.classification.manage'));

        $this->assertTrue($accountant->hasPermissionTo('inventory.variance-reasons.view'));
        $this->assertTrue($accountant->hasPermissionTo('inventory.reconcile.view'));
        $this->assertTrue($accountant->hasPermissionTo('inventory.reconcile.approve'));
    }
}
