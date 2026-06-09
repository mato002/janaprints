<?php

namespace Tests\Feature\Inventory;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Inventory\InventoryCategory;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\UnitOfMeasure;
use App\Models\User;
use Database\Seeders\InventoryFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UnitOfMeasureManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_index_requires_catalogue_permission(): void
    {
        [$company, $branch, $user] = $this->catalogueUser(['catalogue.view']);
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.inventory.catalogue.units.index'))
            ->assertOk()
            ->assertSee('Units of Measure');
    }

    public function test_crud_lifecycle_for_unit_of_measure(): void
    {
        [$company, $branch, $user] = $this->catalogueUser([
            'catalogue.view', 'catalogue.create', 'catalogue.edit', 'catalogue.delete',
        ]);
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $sheet = UnitOfMeasure::query()
            ->where('company_id', $company->id)
            ->where('branch_id', $branch->id)
            ->where('code', 'SHEET')
            ->firstOrFail();

        $this->actingAs($user)
            ->post(route('admin.inventory.catalogue.units.store'), [
                'code' => 'BUNDLE',
                'name' => 'Bundle',
                'base_unit_id' => $sheet->id,
                'conversion_factor' => 100,
                'is_active' => 1,
            ])
            ->assertRedirect();

        $unit = UnitOfMeasure::query()->where('code', 'BUNDLE')->firstOrFail();
        $this->assertEquals($sheet->id, $unit->base_unit_id);
        $this->assertEquals(100, (float) $unit->conversion_factor);

        $this->actingAs($user)
            ->get(route('admin.inventory.catalogue.units.show', $unit))
            ->assertOk()
            ->assertSee('Bundle');

        $this->actingAs($user)
            ->put(route('admin.inventory.catalogue.units.update', $unit), [
                'code' => 'BUNDLE',
                'name' => 'Paper Bundle',
                'base_unit_id' => $sheet->id,
                'conversion_factor' => 120,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.inventory.catalogue.units.show', $unit));

        $unit->refresh();
        $this->assertEquals('Paper Bundle', $unit->name);
        $this->assertEquals(120, (float) $unit->conversion_factor);

        $this->actingAs($user)
            ->delete(route('admin.inventory.catalogue.units.destroy', $unit))
            ->assertRedirect(route('admin.inventory.catalogue.units.index'));

        $this->assertDatabaseMissing('units_of_measure', ['id' => $unit->id]);
    }

    public function test_cannot_delete_unit_when_in_use(): void
    {
        [$company, $branch, $user, $unit, $item] = $this->contextWithItem();
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->from(route('admin.inventory.catalogue.units.show', $unit))
            ->delete(route('admin.inventory.catalogue.units.destroy', $unit))
            ->assertRedirect(route('admin.inventory.catalogue.units.show', $unit))
            ->assertSessionHasErrors('unit');

        $this->assertDatabaseHas('units_of_measure', ['id' => $unit->id]);
        $this->assertDatabaseHas('inventory_items', ['id' => $item->id, 'unit_of_measure_id' => $unit->id]);
    }

    public function test_can_deactivate_unit_when_in_use(): void
    {
        [$company, $branch, $user, $unit] = $this->contextWithItem();
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->patch(route('admin.inventory.catalogue.units.deactivate', $unit))
            ->assertRedirect();

        $unit->refresh();
        $this->assertFalse($unit->is_active);
    }

    public function test_seeded_conversion_examples_exist(): void
    {
        [$company, $branch] = $this->tenant();

        $ream = UnitOfMeasure::query()->where('company_id', $company->id)->where('branch_id', $branch->id)->where('code', 'REAM')->first();
        $carton = UnitOfMeasure::query()->where('company_id', $company->id)->where('branch_id', $branch->id)->where('code', 'CARTON')->first();
        $box = UnitOfMeasure::query()->where('company_id', $company->id)->where('branch_id', $branch->id)->where('code', 'BOX')->first();

        $this->assertNotNull($ream);
        $this->assertEquals(500, (float) $ream->conversion_factor);
        $this->assertEquals('SHEET', $ream->baseUnit?->code);

        $this->assertNotNull($box);
        $this->assertEquals(10, (float) $box->conversion_factor);
        $this->assertEquals('PACK', $box->baseUnit?->code);

        $this->assertNotNull($carton);
        $this->assertEquals(12, (float) $carton->conversion_factor);
        $this->assertEquals('BOX', $carton->baseUnit?->code);
    }

    /**
     * @param  list<string>  $permissions
     * @return array{0: Company, 1: Branch, 2: User}
     */
    protected function catalogueUser(array $permissions): array
    {
        [$company, $branch] = $this->tenant();

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        Role::findByName('Storekeeper', 'web')->syncPermissions($permissions);
        $user->assignRole('Storekeeper');

        return [$company, $branch, $user];
    }

    /**
     * @return array{0: Company, 1: Branch}
     */
    protected function tenant(): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $this->seed(InventoryFoundationSeeder::class);

        return [$company, $branch];
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User, 3: UnitOfMeasure, 4: InventoryItem}
     */
    protected function contextWithItem(): array
    {
        [$company, $branch, $user] = $this->catalogueUser([
            'catalogue.view', 'catalogue.create', 'catalogue.edit', 'catalogue.delete',
        ]);

        $category = InventoryCategory::query()->where('company_id', $company->id)->where('branch_id', $branch->id)->firstOrFail();
        $unit = UnitOfMeasure::query()->where('company_id', $company->id)->where('branch_id', $branch->id)->where('code', 'PIECE')->firstOrFail();

        $item = InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'inventory_category_id' => $category->id,
            'unit_of_measure_id' => $unit->id,
            'sku' => 'UOM-'.uniqid(),
        ]);

        return [$company, $branch, $user, $unit, $item];
    }
}
