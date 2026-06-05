<?php

namespace Tests\Feature\Inventory;

use App\Enums\DocumentType;
use App\Enums\InventoryDocumentStatus;
use App\Enums\InventoryReconciliationStatus;
use App\Enums\StockCountType;
use App\Enums\StockReceiptSource;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryReconciliation;
use App\Models\Inventory\StockReceipt;
use App\Models\Inventory\Warehouse;
use App\Models\Platform\NumberingSequence;
use App\Models\User;
use App\Support\Inventory\InventoryReconciliationService;
use App\Support\Inventory\StockCountService;
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

class InventoryReconciliationTest extends TestCase
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

    public function test_pending_variances_visible_after_approval(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->context();
        $this->seedNumbering($company, $branch);
        $this->postReceipt($company, $branch, $user, $item, $warehouse, 40);

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
            'counted_quantity' => 38,
        ]], $user->id);
        StockCountService::submit($count->fresh(), $user->id);
        StockCountService::approve($count->fresh(), $user->id);

        $pending = InventoryReconciliation::query()
            ->where('status', InventoryReconciliationStatus::Pending)
            ->where('stock_count_id', $count->id)
            ->first();

        $this->assertNotNull($pending);
    }

    public function test_approval_required_before_posting_from_pending(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->context();
        $this->seedNumbering($company, $branch);
        $this->postReceipt($company, $branch, $user, $item, $warehouse, 40);

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
            'counted_quantity' => 38,
        ]], $user->id);
        StockCountService::submit($count->fresh(), $user->id);
        StockCountService::approve($count->fresh(), $user->id);

        $reconciliation = $count->fresh()->reconciliation;
        InventoryReconciliationService::approve($reconciliation, $user->id);
        InventoryReconciliationService::post($reconciliation->fresh(), $user->id);

        $this->assertEquals(InventoryReconciliationStatus::Closed, $reconciliation->fresh()->status);
        $this->assertNotNull($reconciliation->fresh()->stock_adjustment_id);
    }

    public function test_duplicate_posting_blocked(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->context();
        $this->seedNumbering($company, $branch);
        $this->postReceipt($company, $branch, $user, $item, $warehouse, 40);

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
            'counted_quantity' => 38,
        ]], $user->id);
        StockCountService::submit($count->fresh(), $user->id);
        StockCountService::approve($count->fresh(), $user->id);

        $reconciliation = $count->fresh()->reconciliation;
        InventoryReconciliationService::approve($reconciliation, $user->id);
        InventoryReconciliationService::post($reconciliation->fresh(), $user->id);

        $this->expectException(ValidationException::class);
        InventoryReconciliationService::post($reconciliation->fresh(), $user->id);
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User, 3: InventoryItem, 4: Warehouse}
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
            'inventory.count.view', 'inventory.count.create', 'inventory.count.edit',
            'inventory.count.submit', 'inventory.count.approve',
            'inventory.reconcile.view', 'inventory.reconcile.approve', 'inventory.reconcile.post',
            'inventory.receive', 'inventory.adjust',
        ]);
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
            'sku' => 'REC-'.uniqid(),
            'standard_cost' => 10,
        ]);

        return [$company, $branch, $user, $item, $warehouse];
    }

    protected function seedNumbering(Company $company, Branch $branch): void
    {
        foreach ([DocumentType::StockCount, DocumentType::StockAdjustment, DocumentType::InventoryReconciliation] as $type) {
            NumberingSequence::query()->updateOrCreate(
                ['company_id' => $company->id, 'branch_id' => $branch->id, 'document_type' => $type->value],
                ['format_template' => $type->typeCode().'-{number}', 'next_number' => 1, 'padding' => 5, 'include_year' => false, 'include_branch_code' => false],
            );
        }
    }

    protected function postReceipt(Company $company, Branch $branch, User $user, InventoryItem $item, Warehouse $warehouse, float $qty): void
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
        $receipt->items()->create(['inventory_item_id' => $item->id, 'quantity' => $qty, 'unit_cost' => 10]);
        StockReceiptService::post($receipt, $user->id);
    }
}
