<?php

namespace Tests\Feature\Inventory;

use App\Enums\InventoryMovementType;
use App\Enums\InventoryStockRole;
use App\Enums\VirtualWarehouseRole;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\Warehouse;
use App\Services\Inventory\VirtualWarehouseResolverService;
use App\Support\InventoryMovementService;
use App\Support\InventoryStockService;
use Database\Seeders\InventoryFoundationSeeder;
use Database\Seeders\InventoryVirtualWarehouseSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryVirtualBalanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(InventoryFoundationSeeder::class);
    }

    public function test_balances_can_be_queried_by_virtual_role(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $this->seed(InventoryVirtualWarehouseSeeder::class);

        $item = $this->sampleItem($company);
        $wip = app(VirtualWarehouseResolverService::class)->workInProgress($company->id);
        $this->assertNotNull($wip);

        $user = $this->actingUser($company);

        InventoryMovementService::record([
            'company_id' => $item->company_id,
            'branch_id' => $item->branch_id,
            'inventory_item_id' => $item->id,
            'warehouse_id' => $wip->id,
            'movement_type' => InventoryMovementType::Adjustment,
            'quantity' => 12,
            'unit_cost' => 4,
            'reference_type' => InventoryItem::class,
            'reference_id' => $item->id,
            'movement_date' => now()->toDateString(),
            'created_by' => $user->id,
        ]);

        $this->assertSame(12.0, InventoryStockService::getBalanceByVirtualRole(
            $item->id,
            $company->id,
            VirtualWarehouseRole::Wip,
        ));
    }

    public function test_existing_physical_warehouse_balances_remain_unchanged_after_virtual_seeding(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $physical = Warehouse::query()
            ->where('company_id', $company->id)
            ->where('branch_id', $branch->id)
            ->physical()
            ->firstOrFail();
        $item = $this->sampleItem($company);

        $user = $this->actingUser($company);

        InventoryMovementService::record([
            'company_id' => $item->company_id,
            'branch_id' => $item->branch_id,
            'inventory_item_id' => $item->id,
            'warehouse_id' => $physical->id,
            'movement_type' => InventoryMovementType::Receipt,
            'quantity' => 25,
            'unit_cost' => 8,
            'reference_type' => InventoryItem::class,
            'reference_id' => $item->id,
            'movement_date' => now()->toDateString(),
            'created_by' => $user->id,
        ]);

        $before = InventoryStockService::balance($item->id, $physical->id);

        $this->seed(InventoryVirtualWarehouseSeeder::class);

        $this->assertSame($before, InventoryStockService::balance($item->id, $physical->id));
    }

    public function test_virtual_warehouse_balances_summary_uses_inventory_movements(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $item = $this->sampleItem($company);
        $fg = app(VirtualWarehouseResolverService::class)->ensureDefaults($company->id)
            ->firstWhere('virtual_role', VirtualWarehouseRole::FinishedGoods);

        $user = $this->actingUser($company);

        InventoryMovementService::record([
            'company_id' => $item->company_id,
            'branch_id' => $item->branch_id,
            'inventory_item_id' => $item->id,
            'warehouse_id' => $fg->id,
            'movement_type' => InventoryMovementType::Adjustment,
            'quantity' => 6,
            'unit_cost' => 10,
            'reference_type' => InventoryItem::class,
            'reference_id' => $item->id,
            'movement_date' => now()->toDateString(),
            'created_by' => $user->id,
        ]);

        $rows = InventoryStockService::getVirtualWarehouseBalances($company->id);
        $fgRow = collect($rows)->firstWhere('role', VirtualWarehouseRole::FinishedGoods);

        $this->assertNotNull($fgRow);
        $this->assertSame(1, $fgRow['item_count']);
        $this->assertSame(60.0, $fgRow['total_value']);
    }

    public function test_company_stock_by_role_filters_items(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $physical = Warehouse::query()->where('company_id', $company->id)->physical()->firstOrFail();
        $category = \App\Models\Inventory\InventoryCategory::query()->where('company_id', $company->id)->firstOrFail();
        $unit = \App\Models\Inventory\UnitOfMeasure::query()->where('company_id', $company->id)->firstOrFail();

        $rawItem = InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'inventory_category_id' => $category->id,
            'unit_of_measure_id' => $unit->id,
            'stock_role' => InventoryStockRole::RawMaterial,
            'sku' => 'RAW-'.uniqid(),
        ]);

        $user = $this->actingUser($company);

        InventoryMovementService::record([
            'company_id' => $rawItem->company_id,
            'branch_id' => $rawItem->branch_id,
            'inventory_item_id' => $rawItem->id,
            'warehouse_id' => $physical->id,
            'movement_type' => InventoryMovementType::Receipt,
            'quantity' => 15,
            'unit_cost' => 2,
            'reference_type' => InventoryItem::class,
            'reference_id' => $rawItem->id,
            'movement_date' => now()->toDateString(),
            'created_by' => $user->id,
        ]);

        $rows = InventoryStockService::getCompanyStockByRole($company->id, InventoryStockRole::RawMaterial);

        $this->assertTrue($rows->contains(fn (array $row) => $row['item']->id === $rawItem->id && $row['balance'] === 15.0));
    }

    protected function sampleItem(Company $company): InventoryItem
    {
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $category = \App\Models\Inventory\InventoryCategory::query()->where('company_id', $company->id)->firstOrFail();
        $unit = \App\Models\Inventory\UnitOfMeasure::query()->where('company_id', $company->id)->firstOrFail();

        return InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'inventory_category_id' => $category->id,
            'unit_of_measure_id' => $unit->id,
            'sku' => 'IVB-'.uniqid(),
        ]);
    }

    protected function actingUser(Company $company): User
    {
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();

        return User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
    }
}
