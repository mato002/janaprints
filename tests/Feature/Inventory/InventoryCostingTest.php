<?php

namespace Tests\Feature\Inventory;

use App\Enums\InventoryCostingMethod;
use App\Enums\InventoryMovementType;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Inventory\InventoryCostLayer;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryMovement;
use App\Models\Inventory\StockReceipt;
use App\Models\Inventory\Warehouse;
use App\Models\User;
use App\Support\Inventory\CompanyCostingSettings;
use App\Support\Inventory\InventoryCostingService;
use App\Support\Inventory\InventoryValuationService;
use Database\Seeders\InventoryFoundationSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryCostingTest extends TestCase
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

    public function test_fifo_consumes_oldest_layers_first(): void
    {
        [$company, $branch, $item, $warehouse] = $this->inventoryContext();

        $this->receipt($company, $branch, $item, $warehouse, 100, 10);
        $this->receipt($company, $branch, $item, $warehouse, 100, 12);

        CompanyCostingSettings::setCostingMethod($company, InventoryCostingMethod::Fifo);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
        ]);

        $unitCost = InventoryCostingService::resolveIssueUnitCost(
            $company->id,
            $branch->id,
            $item->id,
            $warehouse->id,
            150,
        );

        $this->assertEquals(10.67, $unitCost);

        $movement = InventoryMovement::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'inventory_item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'movement_type' => InventoryMovementType::Issue,
            'quantity' => -150,
            'unit_cost' => $unitCost,
            'movement_date' => now()->toDateString(),
            'created_by' => $user->id,
            'reference_type' => StockReceipt::class,
            'reference_id' => 1,
        ]);

        InventoryCostingService::processIssue($movement);

        $remaining = InventoryCostLayer::query()
            ->where('inventory_item_id', $item->id)
            ->sum('quantity_remaining');

        $this->assertEquals(50, (float) $remaining);

        $lastLayer = InventoryCostLayer::query()
            ->where('inventory_item_id', $item->id)
            ->where('quantity_remaining', '>', 0)
            ->first();

        $this->assertEquals(12.0, (float) $lastLayer->unit_cost);
    }

    public function test_weighted_average_issue_uses_running_average(): void
    {
        [$company, $branch, $item, $warehouse] = $this->inventoryContext();

        $this->receipt($company, $branch, $item, $warehouse, 100, 10);
        $this->receipt($company, $branch, $item, $warehouse, 100, 12);

        CompanyCostingSettings::setCostingMethod($company, InventoryCostingMethod::WeightedAverage);

        $avgCost = InventoryCostingService::resolveIssueUnitCost(
            $company->id,
            $branch->id,
            $item->id,
            $warehouse->id,
            50,
        );

        $this->assertEquals(11.0, $avgCost);
    }

    public function test_valuation_dashboard_totals(): void
    {
        [$company, $branch, $item, $warehouse] = $this->inventoryContext();

        $this->receipt($company, $branch, $item, $warehouse, 10, 100);

        $totals = InventoryValuationService::dashboardTotals($company->id, $branch->id);

        $this->assertGreaterThan(0, $totals['fifo_total']);
        $this->assertGreaterThan(0, $totals['average_total']);
    }

    private function receipt(Company $company, Branch $branch, InventoryItem $item, Warehouse $warehouse, float $qty, float $cost): InventoryMovement
    {
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
        ]);

        $movement = InventoryMovement::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'inventory_item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'movement_type' => InventoryMovementType::Receipt,
            'quantity' => $qty,
            'unit_cost' => $cost,
            'movement_date' => now()->toDateString(),
            'created_by' => $user->id,
            'reference_type' => StockReceipt::class,
            'reference_id' => 1,
        ]);

        InventoryCostingService::processReceipt($movement);

        return $movement;
    }

    /**
     * @return array{0: Company, 1: Branch, 2: InventoryItem, 3: Warehouse}
     */
    private function inventoryContext(): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $item = InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);
        $warehouse = Warehouse::query()->where('company_id', $company->id)->firstOrFail();

        return [$company, $branch, $item, $warehouse];
    }
}
