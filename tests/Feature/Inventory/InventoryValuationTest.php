<?php

namespace Tests\Feature\Inventory;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryMovement;
use App\Models\Inventory\InventoryValuationSnapshot;
use App\Models\Inventory\Warehouse;
use App\Models\User;
use App\Support\Inventory\InventoryCostingService;
use App\Support\Inventory\InventoryValuationService;
use Database\Seeders\InventoryFoundationSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryValuationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(PlatformConfigurationSeeder::class);
        $this->seed(InventoryFoundationSeeder::class);
    }

    public function test_valuation_by_item_warehouse_and_category(): void
    {
        [$company, $branch, $item, $warehouse] = $this->context();
        $this->receipt($company, $branch, $item, $warehouse, 20, 50);

        $byItem = InventoryValuationService::byItem($company->id, $branch->id);
        $this->assertGreaterThan(0, $byItem->first()['fifo_value']);

        $byWarehouse = InventoryValuationService::byWarehouse($company->id, $branch->id);
        $this->assertEquals($byItem->first()['fifo_value'], $byWarehouse->first()['fifo_value']);

        $byCategory = InventoryValuationService::byCategory($company->id, $branch->id);
        $this->assertNotEmpty($byCategory);
    }

    public function test_valuation_snapshot_persists(): void
    {
        [$company, $branch, $item, $warehouse] = $this->context();
        $this->receipt($company, $branch, $item, $warehouse, 5, 100);

        InventoryValuationService::snapshot($company->id, $branch->id, '2026-06-01', 'branch');

        $this->assertSame(1, InventoryValuationSnapshot::query()
            ->where('company_id', $company->id)
            ->where('inventory_item_id', $item->id)
            ->whereDate('valuation_date', '2026-06-01')
            ->count());
    }

    public function test_dashboard_totals_include_top_items(): void
    {
        [$company, $branch, $item, $warehouse] = $this->context();
        $this->receipt($company, $branch, $item, $warehouse, 10, 200);

        $totals = InventoryValuationService::dashboardTotals($company->id, $branch->id);

        $this->assertEquals(2000, $totals['fifo_total']);
        $this->assertGreaterThanOrEqual(1, $totals['top_items']->count());
    }

    /**
     * @return array{0: Company, 1: Branch, 2: InventoryItem, 3: Warehouse}
     */
    private function context(): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $item = InventoryItem::factory()->create(['company_id' => $company->id, 'branch_id' => $branch->id]);
        $warehouse = Warehouse::query()->where('company_id', $company->id)->firstOrFail();

        return [$company, $branch, $item, $warehouse];
    }

    private function receipt(Company $company, Branch $branch, InventoryItem $item, Warehouse $warehouse, float $qty, float $cost): void
    {
        $user = User::factory()->create(['company_id' => $company->id, 'default_branch_id' => $branch->id]);

        $movement = InventoryMovement::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'inventory_item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'movement_type' => \App\Enums\InventoryMovementType::Receipt,
            'quantity' => $qty,
            'unit_cost' => $cost,
            'movement_date' => now()->toDateString(),
            'created_by' => $user->id,
            'reference_type' => \App\Models\Inventory\StockReceipt::class,
            'reference_id' => 1,
        ]);

        InventoryCostingService::processReceipt($movement);
    }
}
