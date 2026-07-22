<?php

namespace Tests\Feature\Dispatch\Concerns;

use App\Enums\ArtworkApprovalDecision;
use App\Enums\ArtworkRequestStatus;
use App\Enums\InventoryDocumentStatus;
use App\Enums\ProductionJobCardStatus;
use App\Enums\SalesOrderStatus;
use App\Enums\StockReceiptSource;
use App\Models\Artwork\ArtworkApproval;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Artwork\ArtworkVersion;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\StockReceipt;
use App\Models\Inventory\Warehouse;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Services\Dispatch\DeliveryNoteService;
use App\Services\Production\ProductionCompletionService;
use App\Support\InventoryStockService;
use App\Support\ProductionMaterialConsumptionService;
use App\Support\StockReceiptService;
use Database\Seeders\GlAccountTypeSeeder;
use Database\Seeders\InventoryFoundationSeeder;
use Database\Seeders\InventoryVirtualWarehouseSeeder;
use Database\Seeders\JanaPrintsAccountingPeriodsSeeder;
use Database\Seeders\JanaPrintsChartOfAccountsSeeder;
use Database\Seeders\JanaPrintsPostingEngineSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\ProductionFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Tests\Feature\Production\Concerns\InteractsWithProductionCompletion;

trait InteractsWithDispatchInventory
{
    use InteractsWithProductionCompletion;

    protected function seedDispatchInventoryEnvironment(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(PlatformConfigurationSeeder::class);
        $this->seed(ProductionFoundationSeeder::class);
        $this->seedProductionCompletionEnvironment();
    }

    /**
     * @return array{0: DeliveryNote, 1: InventoryItem, 2: User, 3: ProductionJobCard}
     */
    protected function readyDispatchedDeliveryNote(): array
    {
        [$note, $finishedItem, $user, $jobCard] = $this->prepareDraftNoteWithFg();
        $service = app(DeliveryNoteService::class);
        $service->markPackaged($note, $user->id, ['package_count' => 1]);
        $service->dispatch($note->fresh(), $user->id, ['courier_key' => 'in_house']);

        return [$note->fresh(['items']), $finishedItem, $user, $jobCard->fresh()];
    }

    /**
     * @return array{0: DeliveryNote, 1: InventoryItem, 2: User, 3: ProductionJobCard}
     */
    protected function prepareDraftNoteWithFg(): array
    {
        [$jobCard, $user, $finishedItem] = $this->createEligibleDispatchJobWithFg();

        $note = app(DeliveryNoteService::class)->createDraftFromJobCard($jobCard->fresh());

        return [$note, $finishedItem, $user, $jobCard->fresh()];
    }

    /**
     * @return array{0: ProductionJobCard, 1: User, 2: InventoryItem}
     */
    protected function createEligibleDispatchJobWithFg(): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);

        $permissions = [
            'production.view', 'production.outputs.post',
            'dispatch.view', 'dispatch.create', 'dispatch.dispatch', 'dispatch.deliver',
            'inventory.view', 'inventory.receive', 'inventory.issue',
        ];
        \Spatie\Permission\Models\Role::findByName('Production', 'web')->syncPermissions($permissions);
        $user->assignRole('Production');
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $category = \App\Models\Inventory\InventoryCategory::query()->where('company_id', $company->id)->firstOrFail();
        $unit = \App\Models\Inventory\UnitOfMeasure::query()->where('company_id', $company->id)->firstOrFail();
        $warehouse = Warehouse::query()->where('company_id', $company->id)->where('branch_id', $branch->id)->where('is_virtual', false)->firstOrFail();

        $rawItem = InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'inventory_category_id' => $category->id,
            'unit_of_measure_id' => $unit->id,
            'sku' => 'RAW-DISP-'.uniqid(),
        ]);
        $finishedItem = InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'inventory_category_id' => $category->id,
            'unit_of_measure_id' => $unit->id,
            'sku' => 'FG-DISP-'.uniqid(),
            'stock_role' => \App\Enums\InventoryStockRole::FinishedGood,
        ]);

        $this->postRawStockForConsumption($company, $branch, $user, $rawItem, $warehouse, 100);

        $customer = Customer::factory()->create(['company_id' => $company->id, 'branch_id' => $branch->id]);
        $quotation = Quotation::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'prepared_by' => $user->id,
        ]);
        $artwork = ArtworkRequest::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'quotation_id' => $quotation->id,
            'requested_by' => $user->id,
            'status' => ArtworkRequestStatus::Approved,
            'current_version' => 1,
        ]);
        ArtworkVersion::query()->create([
            'artwork_request_id' => $artwork->id,
            'version_number' => 1,
            'file_path' => 'test.pdf',
            'original_name' => 'test.pdf',
            'uploaded_by' => $user->id,
        ]);
        ArtworkApproval::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'artwork_request_id' => $artwork->id,
            'artwork_version_id' => $artwork->versions()->first()->id,
            'approved_by' => $user->id,
            'decision' => ArtworkApprovalDecision::Approved,
        ]);
        $salesOrder = SalesOrder::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'quotation_id' => $quotation->id,
            'artwork_request_id' => $artwork->id,
            'created_by' => $user->id,
            'status' => SalesOrderStatus::Confirmed,
        ]);

        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'sales_order_id' => $salesOrder->id,
            'customer_id' => $customer->id,
            'status' => ProductionJobCardStatus::InProduction,
            'created_by' => $user->id,
        ]);

        ProductionMaterialConsumptionService::consume($jobCard, $rawItem, $warehouse->id, 10, $user->id);
        app(ProductionCompletionService::class)->post($jobCard, [
            'finished_inventory_item_id' => $finishedItem->id,
            'quantity_completed' => 5,
        ], $user->id);

        $jobCard->update(['status' => ProductionJobCardStatus::ReadyForDispatch]);

        return [$jobCard->fresh(), $user, $finishedItem];
    }

    protected function postRawStockForConsumption(
        Company $company,
        Branch $branch,
        User $user,
        InventoryItem $item,
        Warehouse $warehouse,
        float $qty = 100,
    ): void {
        $receipt = StockReceipt::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'warehouse_id' => $warehouse->id,
            'receipt_number' => 'SR-DISP-'.uniqid(),
            'source' => StockReceiptSource::Purchase,
            'receipt_date' => now()->toDateString(),
            'status' => InventoryDocumentStatus::Draft,
            'received_by' => $user->id,
        ]);
        $receipt->items()->create([
            'inventory_item_id' => $item->id,
            'quantity' => $qty,
            'unit_cost' => 20,
        ]);
        StockReceiptService::post($receipt, $user->id);
        InventoryStockService::forgetBalanceCache($item->id, $warehouse->id);
    }
}
