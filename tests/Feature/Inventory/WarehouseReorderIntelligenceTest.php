<?php

namespace Tests\Feature\Inventory;

use App\Enums\InventoryDocumentStatus;
use App\Enums\ReplenishmentRecommendation;
use App\Enums\ReorderAlertStatus;
use App\Enums\StockReceiptSource;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryItemWarehouseReorderSetting;
use App\Models\Inventory\InventoryReorderAlert;
use App\Models\Inventory\StockReceipt;
use App\Models\Inventory\Warehouse;
use App\Models\User;
use App\Support\Inventory\WarehouseReorderIntelligenceService;
use App\Support\InventoryStockService;
use App\Support\StockReceiptService;
use Database\Seeders\GlAccountTypeSeeder;
use Database\Seeders\InventoryFoundationSeeder;
use Database\Seeders\JanaPrintsAccountingPeriodsSeeder;
use Database\Seeders\JanaPrintsChartOfAccountsSeeder;
use Database\Seeders\JanaPrintsPostingEngineSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WarehouseReorderIntelligenceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(GlAccountTypeSeeder::class);
        $this->seed(JanaPrintsChartOfAccountsSeeder::class);
        $this->seed(JanaPrintsAccountingPeriodsSeeder::class);
        $this->seed(JanaPrintsPostingEngineSeeder::class);
        $this->seed(InventoryFoundationSeeder::class);
    }

    public function test_warehouse_level_alert_when_one_store_empty_and_another_has_stock(): void
    {
        [$company, $branch, $user, $item, $warehouseA, $warehouseB] = $this->multiWarehouseContext();

        $item->update(['reorder_level' => 20, 'reorder_quantity' => 15]);

        $this->postReceipt($company, $branch, $user, $item, $warehouseA, 5);
        $this->postReceipt($company, $branch, $user, $item, $warehouseB, 100);

        $alert = InventoryReorderAlert::query()
            ->where('inventory_item_id', $item->id)
            ->where('warehouse_id', $warehouseA->id)
            ->whereIn('status', [ReorderAlertStatus::Open, ReorderAlertStatus::Acknowledged])
            ->first();

        $this->assertNotNull($alert);
        $this->assertNull(InventoryReorderAlert::query()
            ->where('inventory_item_id', $item->id)
            ->where('warehouse_id', $warehouseB->id)
            ->whereIn('status', [ReorderAlertStatus::Open, ReorderAlertStatus::Acknowledged])
            ->first());
        $this->assertEqualsWithDelta(5, (float) $alert->current_quantity, 0.01);
        $this->assertSame(ReorderAlertStatus::Open, $alert->status);

        $branchBalance = InventoryStockService::branchBalance($item->id, $company->id, $branch->id);
        $this->assertGreaterThan(20, $branchBalance);
    }

    public function test_transfer_recommendation_when_sibling_warehouse_has_excess(): void
    {
        [$company, $branch, $user, $item, $warehouseA, $warehouseB] = $this->multiWarehouseContext();

        $item->update(['reorder_level' => 20, 'reorder_quantity' => 15]);
        $this->postReceipt($company, $branch, $user, $item, $warehouseA, 5);
        $this->postReceipt($company, $branch, $user, $item, $warehouseB, 80);

        $alert = InventoryReorderAlert::query()
            ->where('inventory_item_id', $item->id)
            ->where('warehouse_id', $warehouseA->id)
            ->firstOrFail();

        $this->assertSame(ReplenishmentRecommendation::Transfer, $alert->replenishment_action);
        $this->assertSame($warehouseB->id, $alert->source_warehouse_id);
        $this->assertGreaterThan(0, (float) $alert->recommended_quantity);
    }

    public function test_purchase_recommendation_when_no_transfer_source_available(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->singleWarehouseContext();

        $item->update(['reorder_level' => 20, 'reorder_quantity' => 12]);
        $this->postReceipt($company, $branch, $user, $item, $warehouse, 4);

        $alert = InventoryReorderAlert::query()->where('inventory_item_id', $item->id)->firstOrFail();

        $this->assertSame(ReplenishmentRecommendation::Purchase, $alert->replenishment_action);
        $this->assertNull($alert->source_warehouse_id);
        $this->assertEqualsWithDelta(12, (float) $alert->recommended_quantity, 0.01);
    }

    public function test_warehouse_reorder_configuration_overrides_item_defaults(): void
    {
        [$company, $branch, $user, $item, $warehouseA, $warehouseB] = $this->multiWarehouseContext([
            'inventory.view', 'inventory.receive', 'inventory.reorder.configure',
        ]);

        $item->update(['reorder_level' => 50, 'reorder_quantity' => 40]);

        InventoryItemWarehouseReorderSetting::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'warehouse_id' => $warehouseA->id,
            'inventory_item_id' => $item->id,
            'min_level' => 10,
            'max_level' => 100,
            'reorder_quantity' => 8,
            'safety_stock' => 2,
            'is_active' => true,
        ]);

        $this->postReceipt($company, $branch, $user, $item, $warehouseA, 12);
        $this->postReceipt($company, $branch, $user, $item, $warehouseB, 100);

        $alertA = InventoryReorderAlert::query()
            ->where('inventory_item_id', $item->id)
            ->where('warehouse_id', $warehouseA->id)
            ->first();

        $this->assertNull($alertA);

        InventoryStockService::syncReorderAlerts($item->fresh(), $warehouseA->id);
        $alertA = InventoryReorderAlert::query()
            ->where('inventory_item_id', $item->id)
            ->where('warehouse_id', $warehouseA->id)
            ->first();

        $this->assertNull($alertA);

        $this->postReceipt($company, $branch, $user, $item, $warehouseA, -3);

        $alertA = InventoryReorderAlert::query()
            ->where('inventory_item_id', $item->id)
            ->where('warehouse_id', $warehouseA->id)
            ->firstOrFail();

        $this->assertEqualsWithDelta(10, (float) $alertA->reorder_level, 0.01);
        $this->assertEqualsWithDelta(2, (float) $alertA->safety_stock, 0.01);
    }

    public function test_reorder_permissions_enforced(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->singleWarehouseContext([
            'inventory.view', 'inventory.receive',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.inventory.reorder-settings.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('admin.inventory.reorder-settings.store'), [
                'warehouse_id' => $warehouse->id,
                'inventory_item_id' => $item->id,
                'min_level' => 5,
                'reorder_quantity' => 10,
                'safety_stock' => 1,
            ])
            ->assertForbidden();
    }

    public function test_intelligence_dashboard_aggregates_per_warehouse_health(): void
    {
        [$company, $branch, $user, $item, $warehouseA, $warehouseB] = $this->multiWarehouseContext([
            'inventory.view', 'inventory.receive', 'inventory.reorder.view',
        ]);

        $item->update(['reorder_level' => 20]);
        $this->postReceipt($company, $branch, $user, $item, $warehouseA, 0);
        $this->postReceipt($company, $branch, $user, $item, $warehouseB, 60);

        $service = app(WarehouseReorderIntelligenceService::class);
        $health = $service->warehouseStockHealth($company->id, $branch->id);
        $critical = $service->criticalShortages($company->id, $branch->id);

        $this->assertNotEmpty($health);
        $this->assertTrue($critical->contains(fn ($row) => $row->inventory_item_id === $item->id && $row->warehouse_id === $warehouseA->id));
    }

    /**
     * @param  list<string>  $permissions
     * @return array{0: Company, 1: Branch, 2: User, 3: InventoryItem, 4: Warehouse, 5: Warehouse}
     */
    protected function multiWarehouseContext(array $permissions = ['inventory.view', 'inventory.receive']): array
    {
        [$company, $branch, $user, $item, $warehouseA] = $this->singleWarehouseContext($permissions);

        $warehouseB = Warehouse::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'code' => 'WH-B',
            'name' => 'Secondary Store',
        ]);

        return [$company, $branch, $user, $item, $warehouseA, $warehouseB];
    }

    /**
     * @param  list<string>  $permissions
     * @return array{0: Company, 1: Branch, 2: User, 3: InventoryItem, 4: Warehouse}
     */
    protected function singleWarehouseContext(array $permissions = ['inventory.view', 'inventory.receive']): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        Role::findByName('Storekeeper', 'web')->syncPermissions($permissions);
        $user->assignRole('Storekeeper');

        $warehouse = Warehouse::query()
            ->where('company_id', $company->id)
            ->where('branch_id', $branch->id)
            ->firstOrFail();

        $category = \App\Models\Inventory\InventoryCategory::query()->where('company_id', $company->id)->first();
        $unit = \App\Models\Inventory\UnitOfMeasure::query()->where('company_id', $company->id)->first();
        $item = InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'inventory_category_id' => $category->id,
            'unit_of_measure_id' => $unit->id,
            'sku' => 'WR-'.uniqid(),
            'standard_cost' => 10,
            'reorder_level' => 10,
        ]);

        InventoryReorderAlert::query()
            ->where('company_id', $company->id)
            ->where('branch_id', $branch->id)
            ->where('inventory_item_id', $item->id)
            ->delete();

        return [$company, $branch, $user, $item, $warehouse];
    }

    protected function postReceipt(Company $company, Branch $branch, User $user, InventoryItem $item, Warehouse $warehouse, float $qty): StockReceipt
    {
        if ($qty < 0) {
            \App\Support\InventoryMovementService::record([
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'inventory_item_id' => $item->id,
                'warehouse_id' => $warehouse->id,
                'movement_type' => \App\Enums\InventoryMovementType::Issue,
                'quantity' => \App\Support\InventoryMovementService::issueQuantity(abs($qty)),
                'unit_cost' => 10,
                'reference_type' => InventoryItem::class,
                'reference_id' => $item->id,
                'movement_date' => now()->toDateString(),
                'created_by' => $user->id,
            ]);

            return StockReceipt::query()->make();
        }

        $receipt = StockReceipt::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'warehouse_id' => $warehouse->id,
            'receipt_number' => 'SR-'.uniqid(),
            'source' => StockReceiptSource::Purchase,
            'receipt_date' => now()->toDateString(),
            'status' => InventoryDocumentStatus::Draft,
            'received_by' => $user->id,
        ]);
        $receipt->items()->create([
            'inventory_item_id' => $item->id,
            'quantity' => $qty,
            'unit_cost' => 10,
        ]);
        StockReceiptService::post($receipt, $user->id);

        return $receipt;
    }
}
