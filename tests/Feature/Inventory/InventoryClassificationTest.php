<?php

namespace Tests\Feature\Inventory;

use App\Enums\InventoryStockRole;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Inventory\InventoryCategory;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\UnitOfMeasure;
use App\Models\User;
use Database\Seeders\InventoryFoundationSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InventoryClassificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(InventoryFoundationSeeder::class);
    }

    public function test_stock_role_column_exists_on_inventory_items(): void
    {
        $this->assertTrue(
            \Illuminate\Support\Facades\Schema::hasColumn('inventory_items', 'stock_role'),
        );
    }

    public function test_item_can_be_created_with_stock_role(): void
    {
        [$company, $branch, $user, $category, $uom] = $this->context();

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)->post(route('admin.inventory.items.store'), [
            'inventory_category_id' => $category->id,
            'unit_of_measure_id' => $uom->id,
            'sku' => 'CLASS-001',
            'item_name' => 'Classified Item',
            'stock_role' => InventoryStockRole::Consumable->value,
            'reorder_level' => 0,
            'reorder_quantity' => 0,
            'standard_cost' => 10,
            'is_active' => 1,
        ])->assertRedirect();

        $this->assertDatabaseHas('inventory_items', [
            'sku' => 'CLASS-001',
            'stock_role' => InventoryStockRole::Consumable->value,
        ]);
    }

    public function test_invalid_stock_role_rejected(): void
    {
        [$company, $branch, $user, $category, $uom] = $this->context();

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)->from(route('admin.inventory.items.create'))
            ->post(route('admin.inventory.items.store'), [
                'inventory_category_id' => $category->id,
                'unit_of_measure_id' => $uom->id,
                'sku' => 'BAD-ROLE',
                'item_name' => 'Bad Role Item',
                'stock_role' => 'not_a_role',
                'reorder_level' => 0,
                'reorder_quantity' => 0,
                'standard_cost' => 10,
            ])->assertSessionHasErrors('stock_role');
    }

    public function test_stock_role_filter_works(): void
    {
        [$company, $branch, $user] = $this->context();

        InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'sku' => 'FG-001',
            'item_name' => 'Finished Product',
            'stock_role' => InventoryStockRole::FinishedGood,
        ]);

        InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'sku' => 'RM-001',
            'item_name' => 'Raw Material Item',
            'stock_role' => InventoryStockRole::RawMaterial,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)
            ->get(route('admin.inventory.items.index', ['stock_role' => InventoryStockRole::FinishedGood->value]));

        $response->assertOk();
        $response->assertSee('FG-001', false);
        $response->assertDontSee('RM-001', false);
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User, 3: InventoryCategory, 4: UnitOfMeasure}
     */
    protected function context(): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        Role::findByName('Storekeeper', 'web')->syncPermissions([
            'catalogue.view', 'catalogue.create', 'catalogue.edit',
            'inventory.view', 'inventory.create', 'inventory.edit',
            'inventory.classification.manage',
        ]);
        $user->assignRole('Storekeeper');

        $category = InventoryCategory::query()
            ->where('company_id', $company->id)
            ->where('branch_id', $branch->id)
            ->firstOrFail();

        $uom = UnitOfMeasure::query()
            ->where('company_id', $company->id)
            ->where('branch_id', $branch->id)
            ->firstOrFail();

        return [$company, $branch, $user, $category, $uom];
    }
}
