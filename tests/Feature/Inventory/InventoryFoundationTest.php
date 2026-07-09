<?php

namespace Tests\Feature\Inventory;

use App\Enums\InventoryDocumentStatus;
use App\Enums\InventoryMovementType;
use App\Enums\StockAdjustmentDirection;
use App\Enums\StockIssueDestination;
use App\Enums\StockReceiptSource;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryMovement;
use App\Models\Inventory\ProductionMaterialConsumption;
use App\Models\Inventory\StockIssue;
use App\Models\Inventory\StockReceipt;
use App\Models\Inventory\Warehouse;
use App\Models\Production\ProductionJobCard;
use App\Models\User;
use App\Support\InventoryStockService;
use App\Support\StockAdjustmentService;
use App\Support\StockIssueService;
use App\Support\StockReceiptService;
use Database\Seeders\InventoryFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InventoryFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_company_isolation_for_inventory_items(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $branchA = Branch::factory()->create(['company_id' => $companyA->id]);
        $branchB = Branch::factory()->create(['company_id' => $companyB->id]);

        $user = $this->storekeeper($companyA, $branchA, ['inventory.view']);
        $itemB = InventoryItem::factory()->create(['company_id' => $companyB->id, 'branch_id' => $branchB->id]);

        $this->actingAs($user)->get(route('admin.inventory.items.show', $itemB))->assertNotFound();
    }

    public function test_viewer_cannot_create_inventory_item(): void
    {
        [, , $user] = $this->inventoryContext(['inventory.view']);

        $this->actingAs($user)->get(route('admin.inventory.items.create'))->assertForbidden();
    }

    public function test_stock_receipt_creates_movements(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->inventoryContext([
            'inventory.view', 'inventory.receive',
        ]);
        $this->seed(InventoryFoundationSeeder::class);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $receipt = StockReceipt::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'warehouse_id' => $warehouse->id,
            'receipt_number' => 'SR-00001',
            'source' => StockReceiptSource::Purchase,
            'receipt_date' => now()->toDateString(),
            'status' => InventoryDocumentStatus::Draft,
            'received_by' => $user->id,
        ]);
        $receipt->items()->create([
            'inventory_item_id' => $item->id,
            'quantity' => 100,
            'unit_cost' => 10,
        ]);

        StockReceiptService::post($receipt, $user->id);

        $this->assertEquals(InventoryDocumentStatus::Posted, $receipt->fresh()->status);
        $this->assertEquals(100, InventoryStockService::balance($item->id, $warehouse->id));
        $this->assertDatabaseHas('inventory_movements', [
            'inventory_item_id' => $item->id,
            'movement_type' => InventoryMovementType::Receipt->value,
        ]);
    }

    public function test_stock_issue_prevents_negative_stock(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->inventoryContext([
            'inventory.view', 'inventory.receive', 'inventory.issue',
        ]);

        $receipt = $this->postReceipt($company, $branch, $user, $item, $warehouse, 10);

        $issue = \App\Models\Inventory\StockIssue::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'warehouse_id' => $warehouse->id,
            'issue_number' => 'SI-00001',
            'destination' => StockIssueDestination::Production,
            'issue_date' => now()->toDateString(),
            'status' => InventoryDocumentStatus::Draft,
            'issued_by' => $user->id,
        ]);
        $issue->items()->create([
            'inventory_item_id' => $item->id,
            'quantity' => 50,
            'unit_cost' => 10,
        ]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        StockIssueService::post($issue, $user->id);
    }

    public function test_stock_adjustment_decrease_respects_balance(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->inventoryContext([
            'inventory.view', 'inventory.receive', 'inventory.adjust',
        ]);

        $this->postReceipt($company, $branch, $user, $item, $warehouse, 20);

        $adjustment = \App\Models\Inventory\StockAdjustment::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'warehouse_id' => $warehouse->id,
            'adjustment_number' => 'SA-00001',
            'adjustment_date' => now()->toDateString(),
            'status' => InventoryDocumentStatus::Draft,
            'reason' => 'Cycle count correction',
            'adjusted_by' => $user->id,
        ]);
        $adjustment->items()->create([
            'inventory_item_id' => $item->id,
            'direction' => StockAdjustmentDirection::Decrease,
            'quantity' => 5,
            'unit_cost' => 10,
        ]);

        StockAdjustmentService::post($adjustment, $user->id);

        $this->assertEquals(15, InventoryStockService::balance($item->id, $warehouse->id));
    }

    public function test_production_consumption_links_job_card(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->inventoryContext([
            'inventory.view', 'inventory.receive', 'inventory.issue', 'production.view',
        ]);

        $this->postReceipt($company, $branch, $user, $item, $warehouse, 50);

        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)->post(route('admin.inventory.production.consume', $jobCard), [
            'inventory_item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 5,
        ])->assertRedirect();

        $this->assertDatabaseHas('production_material_consumptions', [
            'production_job_card_id' => $jobCard->id,
            'inventory_item_id' => $item->id,
        ]);
        $this->assertEquals(45, InventoryStockService::balance($item->id, $warehouse->id));
        $this->assertEquals(
            1,
            InventoryMovement::query()->where('movement_type', InventoryMovementType::ProductionConsumption)->count(),
        );
    }

    public function test_transfer_only_user_cannot_post_production_issue(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->inventoryContext([
            'inventory.view', 'inventory.transfer',
        ]);

        $this->postReceipt($company, $branch, $user, $item, $warehouse, 10);

        $issue = StockIssue::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'warehouse_id' => $warehouse->id,
            'issue_number' => 'SI-PROD-01',
            'destination' => StockIssueDestination::Production,
            'issue_date' => now()->toDateString(),
            'status' => InventoryDocumentStatus::Draft,
            'issued_by' => $user->id,
        ]);
        $issue->items()->create([
            'inventory_item_id' => $item->id,
            'quantity' => 1,
            'unit_cost' => 10,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.inventory.issues.post', $issue))
            ->assertForbidden();
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User, 3: InventoryItem, 4: Warehouse}
     */
    protected function inventoryContext(array $permissions): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $user = $this->storekeeper($company, $branch, $permissions);

        $this->seed(InventoryFoundationSeeder::class);

        $warehouse = Warehouse::query()->where('company_id', $company->id)->where('branch_id', $branch->id)->first();
        $category = \App\Models\Inventory\InventoryCategory::query()->where('company_id', $company->id)->first();
        $unit = \App\Models\Inventory\UnitOfMeasure::query()->where('company_id', $company->id)->first();

        $item = InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'inventory_category_id' => $category->id,
            'unit_of_measure_id' => $unit->id,
            'sku' => 'TEST-SKU-001',
            'reorder_level' => 20,
        ]);

        return [$company, $branch, $user, $item, $warehouse];
    }

    protected function storekeeper(Company $company, Branch $branch, array $permissions): User
    {
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        Role::findByName('Storekeeper', 'web')->syncPermissions($permissions);
        $user->assignRole('Storekeeper');

        return $user;
    }

    protected function postReceipt(Company $company, Branch $branch, User $user, InventoryItem $item, Warehouse $warehouse, float $qty): StockReceipt
    {
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
