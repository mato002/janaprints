<?php

namespace Tests\Feature\Inventory;

use App\Enums\DocumentType;
use App\Enums\InventoryDocumentStatus;
use App\Enums\ReorderAlertStatus;
use App\Enums\StockReceiptSource;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryReorderAlert;
use App\Models\Inventory\StockReceipt;
use App\Models\Inventory\Warehouse;
use App\Models\Platform\NumberingSequence;
use App\Enums\GoodsReceiptStatus;
use App\Enums\PurchaseOrderStatus;
use App\Models\Procurement\GoodsReceipt;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\Vendor;
use App\Models\User;
use App\Support\InventoryStockService;
use App\Support\Inventory\ReorderAlertService;
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

class ReorderAlertWorkflowTest extends TestCase
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

    public function test_alert_generation_on_low_stock_sync(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->context();

        $item->update(['reorder_level' => 20, 'reorder_quantity' => 15]);

        $this->postReceipt($company, $branch, $user, $item, $warehouse, 10);

        $alert = InventoryReorderAlert::query()
            ->where('inventory_item_id', $item->id)
            ->first();

        $this->assertNotNull($alert);
        $this->assertSame(ReorderAlertStatus::Open, $alert->status);
        $this->assertEquals(10, (float) $alert->current_quantity);
        $this->assertEquals(20, (float) $alert->reorder_level);
        $this->assertEquals($warehouse->id, $alert->warehouse_id);
    }

    public function test_acknowledge_and_resolve_workflow(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->context(['inventory.view', 'inventory.edit', 'inventory.receive']);
        $service = app(ReorderAlertService::class);

        $item->update(['reorder_level' => 20]);
        $this->postReceipt($company, $branch, $user, $item, $warehouse, 8);

        $alert = InventoryReorderAlert::query()->where('inventory_item_id', $item->id)->firstOrFail();

        $service->acknowledge($alert, $user->id);
        $alert->refresh();

        $this->assertSame(ReorderAlertStatus::Acknowledged, $alert->status);
        $this->assertSame($user->id, $alert->acknowledged_by);

        $service->resolve($alert, $user->id);
        $alert->refresh();

        $this->assertSame(ReorderAlertStatus::Resolved, $alert->status);
        $this->assertSame($user->id, $alert->resolved_by);
        $this->assertNotNull($alert->resolved_at);
    }

    public function test_create_purchase_request_from_alert(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->context([
            'inventory.view', 'inventory.edit', 'inventory.receive', 'procurement.requests.create',
        ]);
        $this->seedNumbering($company, $branch, [DocumentType::PurchaseRequest]);

        $item->update(['reorder_level' => 30, 'reorder_quantity' => 25, 'standard_cost' => 12]);
        $this->postReceipt($company, $branch, $user, $item, $warehouse, 5);

        $alert = InventoryReorderAlert::query()->where('inventory_item_id', $item->id)->firstOrFail();

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)
            ->post(route('admin.inventory.alerts.purchase-request', $alert));

        $purchaseRequest = \App\Models\Procurement\PurchaseRequest::query()->latest('id')->firstOrFail();

        $response->assertRedirect(route('admin.procurement.requests.show', $purchaseRequest));
        $this->assertSame('draft', $purchaseRequest->status->value);
        $this->assertCount(1, $purchaseRequest->items);
        $this->assertSame($item->id, $purchaseRequest->items->first()->inventory_item_id);
        $this->assertEquals(25, (float) $purchaseRequest->items->first()->quantity);
    }

    public function test_store_dashboard_shows_clickable_alert_widgets(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->context(['inventory.view', 'inventory.receive']);
        $item->update(['reorder_level' => 50]);
        $this->postReceipt($company, $branch, $user, $item, $warehouse, 0);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.inventory.store.dashboard'))
            ->assertOk()
            ->assertSee(route('admin.inventory.alerts.index', ['critical_only' => 1], false), false)
            ->assertSee(route('admin.inventory.alerts.index', absolute: false), false);
    }

    public function test_receipt_pages_show_cross_links(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->context(['inventory.view', 'inventory.receive', 'procurement.orders.view']);

        $stockReceipt = StockReceipt::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'warehouse_id' => $warehouse->id,
            'receipt_number' => 'SR-LINK-001',
            'source' => StockReceiptSource::Purchase,
            'receipt_date' => now()->toDateString(),
            'status' => InventoryDocumentStatus::Posted,
            'received_by' => $user->id,
            'posted_at' => now(),
        ]);

        $vendor = Vendor::factory()->create(['company_id' => $company->id]);

        $order = PurchaseOrder::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'vendor_id' => $vendor->id,
            'po_number' => 'PO-LINK-001',
            'order_date' => now()->toDateString(),
            'status' => PurchaseOrderStatus::Sent,
            'subtotal' => 100,
            'total_amount' => 100,
            'prepared_by' => $user->id,
        ]);

        $goodsReceipt = GoodsReceipt::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'purchase_order_id' => $order->id,
            'warehouse_id' => $warehouse->id,
            'stock_receipt_id' => $stockReceipt->id,
            'receipt_number' => 'GRN-LINK-001',
            'receipt_date' => now()->toDateString(),
            'status' => GoodsReceiptStatus::Posted,
            'received_by' => $user->id,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.procurement.receipts.show', $goodsReceipt))
            ->assertOk()
            ->assertSee('SR-LINK-001', false)
            ->assertSee('PO-LINK-001', false);

        $this->actingAs($user)
            ->get(route('admin.inventory.receipts.show', $stockReceipt))
            ->assertOk()
            ->assertSee('GRN-LINK-001', false)
            ->assertSee('PO-LINK-001', false);
    }

    public function test_receiving_labels_clarify_paths(): void
    {
        [$company, $branch, $user] = $this->context([
            'inventory.view', 'procurement.orders.view', 'procurement.vendors.view',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.inventory.receipts.index'))
            ->assertRedirect(route('admin.store.desk', ['view' => 'receipts']));

        $this->actingAs($user)
            ->get(route('admin.store.desk', ['view' => 'receipts']))
            ->assertOk()
            ->assertSee(__('Stock receipts'), false);

        $this->actingAs($user)
            ->get(route('admin.procurement.receipts.index'))
            ->assertOk()
            ->assertSee(__('Goods Receipts'), false);
    }

    /**
     * @param  list<string>  $permissions
     * @return array{0: Company, 1: Branch, 2: User, 3: InventoryItem, 4: Warehouse}
     */
    protected function context(array $permissions = ['inventory.view', 'inventory.receive', 'inventory.reorder.view']): array
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

        $warehouse = Warehouse::query()->where('company_id', $company->id)->where('branch_id', $branch->id)->first();
        $category = \App\Models\Inventory\InventoryCategory::query()->where('company_id', $company->id)->first();
        $unit = \App\Models\Inventory\UnitOfMeasure::query()->where('company_id', $company->id)->first();
        $item = InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'inventory_category_id' => $category->id,
            'unit_of_measure_id' => $unit->id,
            'sku' => 'RA-'.uniqid(),
            'standard_cost' => 10,
            'reorder_level' => 10,
        ]);

        return [$company, $branch, $user, $item, $warehouse];
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

    /**
     * @param  list<DocumentType>  $types
     */
    protected function seedNumbering(Company $company, Branch $branch, array $types): void
    {
        foreach ($types as $type) {
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
}
