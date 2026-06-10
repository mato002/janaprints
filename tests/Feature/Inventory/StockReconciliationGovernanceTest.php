<?php

namespace Tests\Feature\Inventory;

use App\Enums\DocumentType;
use App\Enums\InventoryDocumentStatus;
use App\Enums\InventoryMovementType;
use App\Enums\InventoryVarianceReasonCategory;
use App\Enums\StockCountType;
use App\Enums\StockReceiptSource;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryMovement;
use App\Models\Inventory\InventoryVarianceReasonCode;
use App\Models\Inventory\StockReceipt;
use App\Models\Inventory\Warehouse;
use App\Models\Platform\NumberingSequence;
use App\Models\User;
use App\Support\Accounting\InventoryAccountingPostingService;
use App\Support\Inventory\InventoryReconciliationService;
use App\Support\Inventory\StockCountService;
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
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StockReconciliationGovernanceTest extends TestCase
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

        $this->mock(InventoryAccountingPostingService::class, function ($mock): void {
            $mock->shouldReceive('postStockReceipt')->andReturn(null);
            $mock->shouldReceive('postStockAdjustment')->andReturn(null);
        });
    }

    public function test_variance_cannot_be_approved_without_reason(): void
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

        $this->expectException(ValidationException::class);
        StockCountService::approve($count->fresh(), $user->id);
    }

    public function test_variance_requiring_comment_cannot_be_approved_without_comment(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->context();
        $this->seedNumbering($company, $branch);
        $this->postReceipt($company, $branch, $user, $item, $warehouse, 40);

        $reasonCode = InventoryVarianceReasonCode::query()->create([
            'company_id' => $company->id,
            'code' => 'COUNT-ERR',
            'name' => 'Counting error',
            'category' => InventoryVarianceReasonCategory::CountingError,
            'requires_comment' => true,
            'is_active' => true,
        ]);

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
            'inventory_variance_reason_code_id' => $reasonCode->id,
        ]], $user->id);

        StockCountService::submit($count->fresh(), $user->id);

        $this->expectException(ValidationException::class);
        StockCountService::approve($count->fresh(), $user->id);
    }

    public function test_explained_variance_can_be_approved_and_posted_without_changing_unrelated_movements(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->context();
        $this->seedNumbering($company, $branch);
        $this->postReceipt($company, $branch, $user, $item, $warehouse, 40);

        $movementCountBefore = InventoryMovement::query()->count();

        $reasonCode = InventoryVarianceReasonCode::query()->create([
            'company_id' => $company->id,
            'code' => 'MACH-CAL',
            'name' => 'Machine calibration',
            'category' => InventoryVarianceReasonCategory::MachineCalibration,
            'requires_comment' => false,
            'is_active' => true,
        ]);

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
            'inventory_variance_reason_code_id' => $reasonCode->id,
        ]], $user->id);

        StockCountService::submit($count->fresh(), $user->id);
        StockCountService::approve($count->fresh(), $user->id);

        $reconciliation = $count->fresh()->reconciliation;
        InventoryReconciliationService::approve($reconciliation, $user->id);
        StockCountService::post($count->fresh(), $user->id);

        $this->assertEquals(38.0, InventoryStockService::balance($item->id, $warehouse->id));
        $this->assertGreaterThan($movementCountBefore, InventoryMovement::query()->count());
        $this->assertSame(
            InventoryMovementType::Adjustment,
            InventoryMovement::query()->latest('id')->first()?->movement_type,
        );
    }

    public function test_legacy_reason_text_still_allows_approval(): void
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
            'reason' => 'Legacy shrinkage note',
        ]], $user->id);

        StockCountService::submit($count->fresh(), $user->id);
        StockCountService::approve($count->fresh(), $user->id);

        $this->assertNotNull($count->fresh()->reconciliation);
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
            'inventory.count.submit', 'inventory.count.approve', 'inventory.count.post',
            'inventory.reconcile.view', 'inventory.reconcile.approve', 'inventory.reconcile.post',
            'inventory.receive', 'inventory.adjust',
        ]);
        $user->assignRole('Storekeeper');

        $this->seed(InventoryFoundationSeeder::class);

        $warehouse = Warehouse::query()->where('company_id', $company->id)->where('branch_id', $branch->id)->firstOrFail();
        $category = \App\Models\Inventory\InventoryCategory::query()->where('company_id', $company->id)->firstOrFail();
        $unit = \App\Models\Inventory\UnitOfMeasure::query()->where('company_id', $company->id)->firstOrFail();
        $item = InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'inventory_category_id' => $category->id,
            'unit_of_measure_id' => $unit->id,
            'sku' => 'GOV-'.uniqid(),
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
            'receipt_number' => 'SR-GOV-'.uniqid(),
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
    }
}
