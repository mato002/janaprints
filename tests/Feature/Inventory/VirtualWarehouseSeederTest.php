<?php

namespace Tests\Feature\Inventory;

use App\Enums\VirtualWarehouseRole;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Inventory\Warehouse;
use Database\Seeders\InventoryVirtualWarehouseSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VirtualWarehouseSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_creates_required_virtual_warehouses_per_company(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();

        $this->seed(InventoryVirtualWarehouseSeeder::class);

        foreach (VirtualWarehouseRole::seededRoles() as $role) {
            $this->assertDatabaseHas('warehouses', [
                'company_id' => $company->id,
                'code' => $role->defaultCode(),
                'is_virtual' => true,
                'virtual_role' => $role->value,
                'is_active' => true,
            ]);
        }

        $this->assertSame(
            count(VirtualWarehouseRole::seededRoles()),
            Warehouse::query()->where('company_id', $company->id)->virtual()->count(),
        );
    }

    public function test_seeder_is_idempotent_on_repeated_run(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();

        $this->seed(InventoryVirtualWarehouseSeeder::class);
        $firstCount = Warehouse::query()->where('company_id', $company->id)->virtual()->count();

        $this->seed(InventoryVirtualWarehouseSeeder::class);
        $secondCount = Warehouse::query()->where('company_id', $company->id)->virtual()->count();

        $this->assertSame($firstCount, $secondCount);
        $this->assertSame(count(VirtualWarehouseRole::seededRoles()), $secondCount);
    }

    public function test_seeder_is_tenant_safe_across_companies(): void
    {
        $jana = Company::query()->where('code', 'JANA')->firstOrFail();
        $other = Company::query()->create([
            'code' => 'OTHER',
            'name' => 'Other Co',
            'is_active' => true,
        ]);
        Branch::query()->create([
            'company_id' => $other->id,
            'code' => 'HQ',
            'name' => 'HQ',
            'is_head_office' => true,
            'is_active' => true,
        ]);

        $this->seed(InventoryVirtualWarehouseSeeder::class);

        $this->assertSame(
            count(VirtualWarehouseRole::seededRoles()),
            Warehouse::query()->where('company_id', $jana->id)->virtual()->count(),
        );
        $this->assertSame(
            count(VirtualWarehouseRole::seededRoles()),
            Warehouse::query()->where('company_id', $other->id)->virtual()->count(),
        );
    }
}
