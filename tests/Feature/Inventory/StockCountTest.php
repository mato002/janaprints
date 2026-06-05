<?php

namespace Tests\Feature\Inventory;

use App\Enums\DocumentType;
use App\Enums\InventoryDocumentStatus;
use App\Enums\InventoryMovementType;
use App\Enums\StockCountStatus;
use App\Enums\StockCountType;
use App\Enums\StockReceiptSource;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\StockCount;
use App\Models\Inventory\StockReceipt;
use App\Models\Inventory\Warehouse;
use App\Models\Platform\NumberingSequence;
use App\Models\User;
use App\Support\Inventory\StockCountService;
use App\Support\InventoryStockService;
use App\Support\StockAdjustmentService;
use App\Support\StockReceiptService;
use Database\Seeders\GlAccountTypeSeeder;
use Database\Seeders\InventoryFoundationSeeder;
use Database\Seeders\JanaPrintsAccountingPeriodsSeeder;
use Database\Seeders\JanaPrintsChartOfAccountsSeeder;
use Database\Seeders\JanaPrintsPostingEngineSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StockCountTest extends TestCase
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
    }

    public function test_create_count_snapshots_system_quantities(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->inventoryContext();
        $this->seedNumbering($company, $branch);
        $this->postReceipt($company, $branch, $user, $item, $warehouse, 50);

        $count = StockCountService::create(
            companyId: $company->id,
            branchId: $branch->id,
            warehouseId: $warehouse->id,
            countType: StockCountType::Full,
            countDate: now()->toDateString(),
            userId: $user->id,
        );

        $line = $count->items()->where('inventory_item_id', $item->id)->first();
        $this->assertNotNull($line);
        $this->assertEquals(50, (float) $line->system_quantity);
    }

    public function test_submit_approve_and_post_adjustment(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->inventoryContext([
            'inventory.count.view', 'inventory.count.create', 'inventory.count.edit',
            'inventory.count.submit', 'inventory.count.approve', 'inventory.count.post',
            'inventory.reconcile.view', 'inventory.reconcile.post',
            'inventory.receive', 'inventory.adjust',
        ]);
        $this->seedNumbering($company, $branch);
        $this->postReceipt($company, $branch, $user, $item, $warehouse, 50);

        $count = StockCountService::create(
            companyId: $company->id,
            branchId: $branch->id,
            warehouseId: $warehouse->id,
            countType: StockCountType::Full,
            countDate: now()->toDateString(),
            userId: $user->id,
        );

        StockCountService::updateCountedQuantities($count, [[
            'inventory_item_id' => $item->id,
            'counted_quantity' => 45,
            'reason' => 'Shrinkage',
        ]], $user->id);

        StockCountService::submit($count->fresh(), $user->id);
        StockCountService::approve($count->fresh(), $user->id);
        StockCountService::post($count->fresh(), $user->id);

        $count->refresh();
        $this->assertEquals(StockCountStatus::Closed, $count->status);
        $this->assertNotNull($count->stock_adjustment_id);
        $this->assertEquals(45, InventoryStockService::balance($item->id, $warehouse->id));
        $this->assertDatabaseHas('inventory_movements', [
            'inventory_item_id' => $item->id,
            'movement_type' => InventoryMovementType::Adjustment->value,
        ]);
    }

    public function test_posted_count_cannot_be_edited(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->inventoryContext();
        $this->seedNumbering($company, $branch);
        $this->postReceipt($company, $branch, $user, $item, $warehouse, 10);

        $count = StockCountService::create(
            companyId: $company->id,
            branchId: $branch->id,
            warehouseId: $warehouse->id,
            countType: StockCountType::Full,
            countDate: now()->toDateString(),
            userId: $user->id,
        );

        StockCountService::updateCountedQuantities($count, [[
            'inventory_item_id' => $item->id,
            'counted_quantity' => 8,
        ]], $user->id);
        StockCountService::submit($count->fresh(), $user->id);
        StockCountService::approve($count->fresh(), $user->id);
        StockCountService::post($count->fresh(), $user->id);

        $this->expectException(ValidationException::class);
        StockCountService::updateCountedQuantities($count->fresh(), [[
            'inventory_item_id' => $item->id,
            'counted_quantity' => 5,
        ]], $user->id);
    }

    public function test_duplicate_post_blocked(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->inventoryContext();
        $this->seedNumbering($company, $branch);
        $this->postReceipt($company, $branch, $user, $item, $warehouse, 10);

        $count = StockCountService::create(
            companyId: $company->id,
            branchId: $branch->id,
            warehouseId: $warehouse->id,
            countType: StockCountType::Full,
            countDate: now()->toDateString(),
            userId: $user->id,
        );

        StockCountService::updateCountedQuantities($count, [[
            'inventory_item_id' => $item->id,
            'counted_quantity' => 8,
        ]], $user->id);
        StockCountService::submit($count->fresh(), $user->id);
        StockCountService::approve($count->fresh(), $user->id);
        StockCountService::post($count->fresh(), $user->id);

        $this->expectException(ValidationException::class);
        StockCountService::post($count->fresh(), $user->id);
    }

    public function test_unauthorized_user_blocked_from_create(): void
    {
        [$company, $branch, $user] = $this->inventoryContext(['inventory.count.view']);
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.inventory.stock-counts.create'))
            ->assertForbidden();
    }

    public function test_permitted_user_can_view_index(): void
    {
        [$company, $branch, $user] = $this->inventoryContext(['inventory.count.view']);
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.inventory.stock-counts.index'))
            ->assertOk();
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User, 3: InventoryItem, 4: Warehouse}
     */
    protected function inventoryContext(array $permissions = []): array
    {
        $defaults = [
            'inventory.count.view', 'inventory.count.create', 'inventory.count.edit',
            'inventory.count.submit', 'inventory.count.approve', 'inventory.count.post',
            'inventory.reconcile.view', 'inventory.reconcile.approve', 'inventory.reconcile.post',
            'inventory.receive', 'inventory.adjust',
        ];
        $permissions = $permissions ?: $defaults;

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

        $this->seed(InventoryFoundationSeeder::class);

        $warehouse = Warehouse::query()->where('company_id', $company->id)->where('branch_id', $branch->id)->first();
        $category = \App\Models\Inventory\InventoryCategory::query()->where('company_id', $company->id)->first();
        $unit = \App\Models\Inventory\UnitOfMeasure::query()->where('company_id', $company->id)->first();

        $item = InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'inventory_category_id' => $category->id,
            'unit_of_measure_id' => $unit->id,
            'sku' => 'SC-TEST-'.uniqid(),
            'standard_cost' => 10,
        ]);

        return [$company, $branch, $user, $item, $warehouse];
    }

    protected function seedNumbering(Company $company, Branch $branch): void
    {
        foreach ([DocumentType::StockCount, DocumentType::StockAdjustment, DocumentType::InventoryReconciliation] as $type) {
            NumberingSequence::query()->updateOrCreate(
                [
                    'company_id' => $company->id,
                    'branch_id' => $branch->id,
                    'document_type' => $type->value,
                ],
                [
                    'format_template' => $type->typeCode().'-{number}',
                    'next_number' => 1,
                    'padding' => 5,
                    'include_year' => false,
                    'include_branch_code' => false,
                ],
            );
        }
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
