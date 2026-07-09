<?php

namespace Tests\Feature\Security;

use App\Enums\ArtworkRequestStatus;
use App\Enums\Dispatch\DeliveryNoteStatus;
use App\Enums\FixedAssetStatus;
use App\Enums\InventoryDocumentStatus;
use App\Enums\AssetType;
use App\Enums\MaintenancePriority;
use App\Enums\MaintenanceType;
use App\Enums\MaintenanceWorkOrderStatus;
use App\Enums\ProductionJobCardStatus;
use App\Enums\ProductionSpecificationApprovalStatus;
use App\Enums\SalesOrderStatus;
use App\Enums\StockIssueDestination;
use App\Enums\StockReceiptSource;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Assets\AssetCategory;
use App\Models\Assets\FixedAsset;
use App\Models\Assets\MaintenanceWorkOrder;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\StockAdjustment;
use App\Models\Inventory\StockIssue;
use App\Models\Inventory\StockReceipt;
use App\Models\Inventory\Warehouse;
use App\Models\Production\PrintProductTemplate;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionQueue;
use App\Models\Production\ProductionSpecification;
use App\Models\Production\QualityCheck;
use App\Models\Production\WorkCenter;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderItem;
use App\Models\User;
use App\Support\Production\ProductionQueueService;
use App\Support\PublicHash\PublicHashGenerator;
use Database\Seeders\CrmFoundationSeeder;
use Database\Seeders\InventoryFoundationSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\ProductionFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class PublicHashTierTwoTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Branch $branch;

    protected User $user;

    protected PublicHashGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(CrmFoundationSeeder::class);
        $this->seed(ProductionFoundationSeeder::class);
        $this->seed(InventoryFoundationSeeder::class);

        $this->company = Company::query()->where('code', 'JANA')->firstOrFail();
        $this->branch = Branch::query()
            ->where('company_id', $this->company->id)
            ->where('code', 'HQ')
            ->firstOrFail();

        $this->user = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $this->user->assignRole('Company Admin');

        session([
            'active_company_id' => $this->company->id,
            'active_branch_id' => $this->branch->id,
        ]);

        $this->generator = app(PublicHashGenerator::class);
    }

    public function test_production_specification_edit_route_accepts_hash(): void
    {
        [$order, $item, $spec] = $this->salesOrderWithSpecification();

        $this->actingAs($this->user)
            ->get(route('admin.sales-orders.items.specification.edit', [
                'salesOrder' => $order,
                'salesOrderItem' => $item,
                'specification' => $spec->public_id,
            ]))
            ->assertOk();
    }

    public function test_print_product_template_show_route_accepts_hash(): void
    {
        $template = PrintProductTemplate::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'created_by' => $this->user->id,
        ]);

        $this->actingAs($this->user)
            ->get(route('admin.production.print-templates.show', $template->public_id))
            ->assertOk();
    }

    public function test_work_center_show_route_accepts_hash(): void
    {
        $workCenter = WorkCenter::query()->where('company_id', $this->company->id)->firstOrFail();

        $this->actingAs($this->user)
            ->get(route('admin.production.work-centers.show', $workCenter->public_id))
            ->assertOk();
    }

    public function test_production_queue_nested_route_accepts_hash(): void
    {
        $jobCard = $this->makeJobCard();
        $workCenter = WorkCenter::query()->where('company_id', $this->company->id)->firstOrFail();
        $entry = app(ProductionQueueService::class)->enqueue($jobCard, $workCenter->id, 1);

        $this->actingAs($this->user)
            ->put(route('admin.production.queues.update', [$jobCard, $entry->public_id]), [
                'queue_position' => 2,
                'status' => $entry->status->value,
            ])
            ->assertRedirect();
    }

    public function test_artwork_request_show_route_accepts_hash(): void
    {
        $request = ArtworkRequest::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'requested_by' => $this->user->id,
            'status' => ArtworkRequestStatus::Requested,
        ]);

        $this->actingAs($this->user)
            ->get(route('admin.artwork.show', $request->public_id))
            ->assertOk();
    }

    public function test_delivery_note_show_route_accepts_hash(): void
    {
        $note = $this->makeDeliveryNote();

        $this->actingAs($this->user)
            ->get(route('admin.dispatch.delivery-notes.show', $note->public_id))
            ->assertOk();
    }

    public function test_inventory_item_show_route_accepts_hash(): void
    {
        $item = $this->makeInventoryItem();

        $this->actingAs($this->user)
            ->get(route('admin.inventory.items.show', $item->public_id))
            ->assertOk();
    }

    public function test_warehouse_show_route_accepts_hash(): void
    {
        $warehouse = Warehouse::query()
            ->where('company_id', $this->company->id)
            ->where('branch_id', $this->branch->id)
            ->firstOrFail();

        $this->actingAs($this->user)
            ->get(route('admin.inventory.warehouses.show', $warehouse->public_id))
            ->assertOk();
    }

    public function test_stock_receipt_show_route_accepts_hash(): void
    {
        $receipt = $this->makeStockReceipt();

        $this->actingAs($this->user)
            ->get(route('admin.inventory.receipts.show', $receipt->public_id))
            ->assertOk();
    }

    public function test_stock_issue_show_route_accepts_hash(): void
    {
        $issue = $this->makeStockIssue();

        $this->actingAs($this->user)
            ->get(route('admin.inventory.issues.show', $issue->public_id))
            ->assertOk();
    }

    public function test_stock_transfer_show_route_accepts_hash(): void
    {
        $transfer = $this->makeStockTransfer();

        $this->actingAs($this->user)
            ->get(route('admin.inventory.transfers.show', $transfer->public_id))
            ->assertOk();
    }

    public function test_stock_adjustment_show_route_accepts_hash(): void
    {
        $adjustment = $this->makeStockAdjustment();

        $this->actingAs($this->user)
            ->get(route('admin.inventory.adjustments.show', $adjustment->public_id))
            ->assertOk();
    }

    public function test_fixed_asset_show_route_accepts_hash(): void
    {
        $asset = $this->makeFixedAsset();

        $this->actingAs($this->user)
            ->get(route('admin.assets.show', $asset->public_id))
            ->assertOk();
    }

    public function test_maintenance_work_order_show_route_accepts_hash(): void
    {
        $order = $this->makeMaintenanceWorkOrder();

        $this->actingAs($this->user)
            ->get(route('admin.assets.maintenance.work-orders.show', $order->public_id))
            ->assertOk();
    }

    public function test_numeric_fallback_still_works_for_tier_two_routes(): void
    {
        $item = $this->makeInventoryItem();

        Config::set('public_hashes.numeric_fallback_enabled', true);

        $this->actingAs($this->user)
            ->get(route('admin.inventory.items.show', $item->id))
            ->assertOk();
    }

    public function test_unknown_hash_returns_not_found(): void
    {
        $this->actingAs($this->user)
            ->get(route('admin.inventory.items.show', 'aaaaaaaaaaaaaaaa'))
            ->assertNotFound();
    }

    public function test_cross_tenant_hash_is_blocked(): void
    {
        $otherCompany = Company::factory()->create(['code' => 'OTHER2']);
        $otherBranch = Branch::factory()->create(['company_id' => $otherCompany->id, 'code' => 'OB2']);

        $foreignItem = InventoryItem::factory()->create([
            'company_id' => $otherCompany->id,
            'branch_id' => $otherBranch->id,
        ]);

        $this->actingAs($this->user)
            ->get(route('admin.inventory.items.show', $foreignItem->public_id))
            ->assertNotFound();
    }

    public function test_new_tier_two_records_auto_generate_public_id(): void
    {
        $models = [
            PrintProductTemplate::factory()->create([
                'company_id' => $this->company->id,
                'branch_id' => $this->branch->id,
                'created_by' => $this->user->id,
            ]),
            ArtworkRequest::factory()->create([
                'company_id' => $this->company->id,
                'branch_id' => $this->branch->id,
                'requested_by' => $this->user->id,
            ]),
            $this->makeDeliveryNote(),
        ];

        foreach ($models as $model) {
            $this->assertNotNull($model->public_id);
            $this->assertTrue($this->generator->isValid($model->public_id));
        }
    }

    public function test_backfill_fills_missing_tier_two_public_ids(): void
    {
        $warehouse = Warehouse::withoutEvents(function () {
            return Warehouse::query()->forceCreate([
                'company_id' => $this->company->id,
                'branch_id' => $this->branch->id,
                'code' => 'WH-NOHASH',
                'name' => 'No Hash Warehouse',
                'is_active' => true,
                'public_id' => null,
            ]);
        });

        Artisan::call('public-hash:backfill', [
            '--model' => Warehouse::class,
        ]);

        $warehouse->refresh();

        $this->assertNotNull($warehouse->public_id);
        $this->assertTrue($this->generator->isValid($warehouse->public_id));
        $this->assertStringContainsString('Backfilled 1 row(s)', Artisan::output());
    }

    public function test_generated_show_links_emit_hashes_for_tier_two_models(): void
    {
        $item = $this->makeInventoryItem();
        $note = $this->makeDeliveryNote();
        $warehouse = Warehouse::query()
            ->where('company_id', $this->company->id)
            ->where('branch_id', $this->branch->id)
            ->firstOrFail();

        $this->assertShowRouteUsesPublicHash(route('admin.inventory.items.show', $item), $item);
        $this->assertShowRouteUsesPublicHash(route('admin.dispatch.delivery-notes.show', $note), $note);
        $this->assertShowRouteUsesPublicHash(route('admin.inventory.warehouses.show', $warehouse), $warehouse);
    }

    /**
     * @return array{0: SalesOrder, 1: SalesOrderItem, 2: ProductionSpecification}
     */
    protected function salesOrderWithSpecification(): array
    {
        $customer = Customer::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
        ]);

        $order = SalesOrder::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $customer->id,
            'created_by' => $this->user->id,
            'status' => SalesOrderStatus::Draft,
        ]);

        $item = SalesOrderItem::query()->create([
            'sales_order_id' => $order->id,
            'line_number' => 1,
            'item_name' => 'Brochure',
            'quantity' => 500,
            'unit_price' => 10,
            'line_total' => 5000,
        ]);

        $spec = ProductionSpecification::factory()
            ->forSalesOrderItem($item)
            ->create([
                'approval_status' => ProductionSpecificationApprovalStatus::Draft,
                'created_by' => $this->user->id,
            ]);

        return [$order, $item, $spec];
    }

    protected function makeJobCard(): ProductionJobCard
    {
        $customer = Customer::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
        ]);

        $order = SalesOrder::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $customer->id,
            'created_by' => $this->user->id,
        ]);

        return ProductionJobCard::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'sales_order_id' => $order->id,
            'customer_id' => $customer->id,
            'status' => ProductionJobCardStatus::Draft,
            'created_by' => $this->user->id,
        ]);
    }

    protected function makeDeliveryNote(): DeliveryNote
    {
        $jobCard = $this->makeJobCard();

        return DeliveryNote::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'delivery_note_number' => 'DN-PH-'.uniqid(),
            'production_job_card_id' => $jobCard->id,
            'sales_order_id' => $jobCard->sales_order_id,
            'customer_id' => $jobCard->customer_id,
            'delivery_date' => now()->toDateString(),
            'status' => DeliveryNoteStatus::Draft,
        ]);
    }

    protected function makeInventoryItem(): InventoryItem
    {
        $category = \App\Models\Inventory\InventoryCategory::query()
            ->where('company_id', $this->company->id)
            ->firstOrFail();
        $unit = \App\Models\Inventory\UnitOfMeasure::query()
            ->where('company_id', $this->company->id)
            ->firstOrFail();

        return InventoryItem::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'inventory_category_id' => $category->id,
            'unit_of_measure_id' => $unit->id,
        ]);
    }

    protected function warehouse(): Warehouse
    {
        return Warehouse::query()
            ->where('company_id', $this->company->id)
            ->where('branch_id', $this->branch->id)
            ->firstOrFail();
    }

    protected function makeStockReceipt(): StockReceipt
    {
        return StockReceipt::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->warehouse()->id,
            'receipt_number' => 'SR-PH-'.uniqid(),
            'source' => StockReceiptSource::Purchase,
            'receipt_date' => now()->toDateString(),
            'status' => InventoryDocumentStatus::Draft,
            'received_by' => $this->user->id,
        ]);
    }

    protected function makeStockIssue(): StockIssue
    {
        return StockIssue::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->warehouse()->id,
            'issue_number' => 'SI-PH-'.uniqid(),
            'destination' => StockIssueDestination::InternalUse,
            'issue_date' => now()->toDateString(),
            'status' => InventoryDocumentStatus::Draft,
            'issued_by' => $this->user->id,
        ]);
    }

    protected function makeStockTransfer(): StockIssue
    {
        $warehouses = Warehouse::query()
            ->where('company_id', $this->company->id)
            ->where('branch_id', $this->branch->id)
            ->limit(2)
            ->get();

        $from = $warehouses->first();
        $to = $warehouses->count() > 1 ? $warehouses->last() : $from;

        return StockIssue::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $from->id,
            'to_warehouse_id' => $to->id,
            'issue_number' => 'ST-PH-'.uniqid(),
            'destination' => StockIssueDestination::Transfer,
            'issue_date' => now()->toDateString(),
            'status' => InventoryDocumentStatus::Draft,
            'issued_by' => $this->user->id,
        ]);
    }

    protected function makeStockAdjustment(): StockAdjustment
    {
        return StockAdjustment::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->warehouse()->id,
            'adjustment_number' => 'SA-PH-'.uniqid(),
            'adjustment_date' => now()->toDateString(),
            'status' => InventoryDocumentStatus::Draft,
            'reason' => 'Cycle count variance',
            'adjusted_by' => $this->user->id,
        ]);
    }

    protected function makeFixedAsset(): FixedAsset
    {
        $category = AssetCategory::query()->firstOrCreate(
            ['company_id' => $this->company->id, 'code' => 'MACH'],
            [
                'name' => 'Machinery',
                'asset_type' => AssetType::Machine->value,
                'useful_life_months' => 60,
                'useful_life_years' => 5,
                'is_active' => true,
            ],
        );

        return FixedAsset::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'asset_category_id' => $category->id,
            'asset_number' => 'AST-PH-'.uniqid(),
            'asset_name' => 'Test Press',
            'acquisition_date' => now()->toDateString(),
            'acquisition_cost' => 100000,
            'status' => FixedAssetStatus::Active,
        ]);
    }

    protected function makeMaintenanceWorkOrder(): MaintenanceWorkOrder
    {
        $asset = $this->makeFixedAsset();

        return MaintenanceWorkOrder::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'fixed_asset_id' => $asset->id,
            'work_order_no' => 'MWO-PH-'.uniqid(),
            'maintenance_type' => MaintenanceType::Corrective,
            'priority' => MaintenancePriority::Normal,
            'status' => MaintenanceWorkOrderStatus::Draft,
            'description' => 'Routine inspection',
        ]);
    }

    protected function assertShowRouteUsesPublicHash(string $url, Model $model): void
    {
        $this->assertStringContainsString((string) $model->public_id, $url);
        $this->assertDoesNotMatchRegularExpression(
            '#/'.preg_quote((string) $model->id, '#').'(?:\?|$)#',
            $url,
        );
    }
}
