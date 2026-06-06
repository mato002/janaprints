<?php

namespace Tests\Feature\Assets;

use App\Enums\AssetAcquisitionSource;
use App\Enums\AssetType;
use App\Enums\CapitalizationCandidateStatus;
use App\Enums\FixedAssetStatus;
use App\Enums\ProcurementItemClassification;
use App\Enums\PurchaseOrderStatus;
use App\Models\Assets\AssetCapitalizationCandidate;
use App\Models\Assets\AssetCategory;
use App\Models\Assets\AssetWarranty;
use App\Models\Assets\FixedAsset;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\Warehouse;
use App\Models\Procurement\GoodsReceipt;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\Vendor;
use App\Models\User;
use App\Services\Assets\AssetCapitalizationReconciliationService;
use App\Services\Assets\AssetCapitalizationService;
use App\Support\Procurement\GoodsReceiptService;
use Database\Seeders\GlAccountTypeSeeder;
use Database\Seeders\InventoryFoundationSeeder;
use Database\Seeders\JanaPrintsAccountingPeriodsSeeder;
use Database\Seeders\JanaPrintsChartOfAccountsSeeder;
use Database\Seeders\JanaPrintsPostingEngineSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetCapitalizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(PlatformConfigurationSeeder::class);
        $this->seed(GlAccountTypeSeeder::class);
        $this->seed(JanaPrintsChartOfAccountsSeeder::class);
        $this->seed(JanaPrintsAccountingPeriodsSeeder::class);
        $this->seed(JanaPrintsPostingEngineSeeder::class);
        $this->seed(InventoryFoundationSeeder::class);
    }

    public function test_capital_goods_receipt_creates_candidate_not_inventory(): void
    {
        [$user, $order, $poItem, $warehouse] = $this->capitalProcurementContext();

        $grn = $this->createGoodsReceipt($order, $poItem, $warehouse, $user, 1);

        GoodsReceiptService::post($grn, $user->id);

        $this->assertDatabaseHas('asset_capitalization_candidates', [
            'goods_receipt_item_id' => $grn->items()->first()->id,
            'status' => CapitalizationCandidateStatus::Ready->value,
        ]);
        $this->assertNull($grn->fresh()->stock_receipt_id);
    }

    public function test_single_asset_capitalization(): void
    {
        $user = $this->acquisitionUser();
        $candidate = $this->makeCandidate(['quantity' => 1, 'unit_cost' => 250000]);

        $assets = app(AssetCapitalizationService::class)->capitalize($candidate, [
            'quantity' => 1,
            'asset_category_id' => $candidate->asset_category_id,
            'asset_name' => 'Heidelberg Press',
            'branch_id' => $candidate->branch_id,
        ], $user->id, true);

        $this->assertCount(1, $assets);
        $this->assertSame('Heidelberg Press', $assets[0]->asset_name);
        $this->assertSame(CapitalizationCandidateStatus::Capitalized, $candidate->fresh()->status);
        $this->assertNotNull($assets[0]->posted_acquisition_journal_id);
    }

    public function test_bulk_capitalization_creates_multiple_assets(): void
    {
        $user = $this->acquisitionUser();
        [$_, $order, $poItem, $warehouse] = $this->capitalProcurementContext();
        $poItem->update(['quantity' => 3, 'line_total' => 240000, 'unit_cost' => 80000]);
        $grn = $this->createGoodsReceipt($order, $poItem, $warehouse, $user, 3);
        GoodsReceiptService::post($grn, $user->id);
        $candidate = AssetCapitalizationCandidate::query()->where('goods_receipt_id', $grn->id)->firstOrFail();

        $approver = User::factory()->create(['company_id' => $user->company_id, 'is_active' => true]);
        $approver->assignRole('Company Admin');
        app(AssetCapitalizationService::class)->approve($candidate, $approver->id);

        $assets = app(AssetCapitalizationService::class)->capitalize($candidate, [
            'quantity' => 3,
            'asset_category_id' => $candidate->asset_category_id,
            'asset_name' => 'Dell Laptop',
            'branch_id' => $candidate->branch_id,
        ], $user->id, false);

        $this->assertCount(3, $assets);
        $this->assertSame(3, FixedAsset::query()->where('capitalization_candidate_id', $candidate->id)->count());
    }

    public function test_vendor_and_procurement_links_on_asset(): void
    {
        $user = $this->acquisitionUser();
        $candidate = $this->makeCandidate();

        $assets = app(AssetCapitalizationService::class)->capitalize($candidate, [
            'quantity' => 1,
            'asset_category_id' => $candidate->asset_category_id,
            'asset_name' => 'Linked Asset',
            'branch_id' => $candidate->branch_id,
            'warranty_end' => now()->addYear()->toDateString(),
        ], $user->id, false);

        $asset = $assets[0]->fresh(['vendor', 'purchaseOrder', 'goodsReceipt', 'procurementDocuments']);
        $this->assertSame($candidate->vendor_id, $asset->vendor_id);
        $this->assertSame(AssetAcquisitionSource::Procurement, $asset->acquisition_source);
        $this->assertNotEmpty($asset->procurementDocuments);
        $this->assertDatabaseHas('asset_warranties', ['fixed_asset_id' => $asset->id]);
    }

    public function test_journal_posting_is_idempotent(): void
    {
        $user = $this->acquisitionUser();
        $candidate = $this->makeCandidate(['unit_cost' => 50000]);

        $assets = app(AssetCapitalizationService::class)->capitalize($candidate, [
            'quantity' => 1,
            'asset_category_id' => $candidate->asset_category_id,
            'asset_name' => 'Posted Asset',
            'branch_id' => $candidate->branch_id,
        ], $user->id, true);

        $journalId = $assets[0]->fresh()->posted_acquisition_journal_id;
        $journal = app(\App\Support\Accounting\AssetAcquisitionPostingService::class)
            ->postAcquisition($assets[0]->fresh(), $user->id);

        $this->assertSame($journalId, $journal->id);
    }

    public function test_reconciliation_detects_pending_candidates(): void
    {
        $user = $this->acquisitionUser();
        $this->makeCandidate(['line_amount' => 100000]);

        $record = app(AssetCapitalizationReconciliationService::class)->run($user->company_id, $user->id);

        $this->assertGreaterThan(0, $record->received_not_capitalized_count);
        $this->assertSame(100000.0, (float) $record->procurement_received_value);
    }

    public function test_acquisition_dashboard_requires_permission(): void
    {
        $user = User::factory()->create([
            'company_id' => Company::query()->first()->id,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('admin.assets.acquisitions.dashboard'))
            ->assertForbidden();
    }

    public function test_tenant_isolation_on_workbench(): void
    {
        $user = $this->acquisitionUser();
        $otherCompany = Company::query()->create(['name' => 'Other', 'code' => 'OTH3', 'is_active' => true]);
        $otherBranch = Branch::query()->create([
            'company_id' => $otherCompany->id,
            'code' => 'OB',
            'name' => 'Other Branch',
            'is_active' => true,
        ]);
        $otherUser = User::factory()->create(['company_id' => $otherCompany->id, 'is_active' => true]);
        $otherUser->assignRole('Company Admin');
        $vendor = Vendor::factory()->create(['company_id' => $otherCompany->id]);
        $category = AssetCategory::query()->create([
            'company_id' => $otherCompany->id,
            'name' => 'Other Cat',
            'code' => 'OC',
            'asset_type' => AssetType::Other->value,
            'is_active' => true,
        ]);
        $order = PurchaseOrder::query()->create([
            'company_id' => $otherCompany->id,
            'branch_id' => $otherBranch->id,
            'vendor_id' => $vendor->id,
            'po_number' => 'PO-OTH',
            'order_date' => now(),
            'status' => PurchaseOrderStatus::Sent,
            'subtotal' => 1000,
            'total_amount' => 1000,
            'prepared_by' => $otherUser->id,
        ]);
        $poItem = $order->items()->create([
            'description' => 'Foreign',
            'quantity' => 1,
            'unit_cost' => 1000,
            'line_total' => 1000,
            'item_classification' => ProcurementItemClassification::FixedAsset,
            'asset_category_id' => $category->id,
        ]);
        $warehouse = Warehouse::query()->create([
            'company_id' => $otherCompany->id,
            'branch_id' => $otherBranch->id,
            'code' => 'OW',
            'name' => 'Other WH',
            'is_active' => true,
        ]);
        $grn = GoodsReceipt::query()->create([
            'company_id' => $otherCompany->id,
            'branch_id' => $otherBranch->id,
            'purchase_order_id' => $order->id,
            'warehouse_id' => $warehouse->id,
            'receipt_number' => 'GRN-OTH',
            'receipt_date' => now(),
            'status' => \App\Enums\GoodsReceiptStatus::Posted,
            'received_by' => $otherUser->id,
        ]);
        $grnItem = $grn->items()->create([
            'purchase_order_item_id' => $poItem->id,
            'item_classification' => ProcurementItemClassification::FixedAsset,
            'quantity_received' => 1,
            'unit_cost' => 1000,
        ]);
        $candidate = AssetCapitalizationCandidate::query()->create([
            'company_id' => $otherCompany->id,
            'branch_id' => $otherBranch->id,
            'candidate_number' => 'CAP-X',
            'goods_receipt_id' => $grn->id,
            'goods_receipt_item_id' => $grnItem->id,
            'purchase_order_id' => $order->id,
            'purchase_order_item_id' => $poItem->id,
            'quantity' => 1,
            'unit_cost' => 1000,
            'line_amount' => 1000,
            'status' => CapitalizationCandidateStatus::Ready,
            'received_date' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('admin.assets.acquisitions.workbench', $candidate))
            ->assertForbidden();
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function makeCandidate(array $overrides = []): AssetCapitalizationCandidate
    {
        [$user, $order, $poItem, $warehouse] = $this->capitalProcurementContext();
        $qty = (float) ($overrides['quantity'] ?? 1);
        $unitCost = (float) ($overrides['unit_cost'] ?? 100000);

        $grn = $this->createGoodsReceipt($order, $poItem, $warehouse, $user, $qty);
        GoodsReceiptService::post($grn, $user->id);

        $candidate = AssetCapitalizationCandidate::query()->where('goods_receipt_id', $grn->id)->firstOrFail();

        if (isset($overrides['line_amount'])) {
            $candidate->update(['line_amount' => $overrides['line_amount'], 'unit_cost' => $unitCost, 'quantity' => $qty]);
        }

        return $candidate->fresh();
    }

    /**
     * @return array{0: User, 1: PurchaseOrder, 2: \App\Models\Procurement\PurchaseOrderItem, 3: Warehouse}
     */
    protected function capitalProcurementContext(): array
    {
        $user = $this->acquisitionUser();
        $company = Company::query()->first();
        $branch = Branch::query()->where('company_id', $company->id)->first();
        $category = $this->makeCategory();
        $vendor = Vendor::factory()->create(['company_id' => $company->id]);
        $warehouse = Warehouse::query()->where('company_id', $company->id)->first();

        $order = PurchaseOrder::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'vendor_id' => $vendor->id,
            'po_number' => 'PO-CAP-'.uniqid(),
            'order_date' => now()->toDateString(),
            'status' => PurchaseOrderStatus::Sent,
            'subtotal' => 250000,
            'total_amount' => 250000,
            'prepared_by' => $user->id,
        ]);

        $poItem = $order->items()->create([
            'description' => 'Production Machine',
            'quantity' => 1,
            'unit_cost' => 250000,
            'line_total' => 250000,
            'item_classification' => ProcurementItemClassification::FixedAsset,
            'asset_category_id' => $category->id,
        ]);

        return [$user, $order, $poItem, $warehouse];
    }

    protected function createGoodsReceipt(PurchaseOrder $order, $poItem, Warehouse $warehouse, User $user, float $qty): GoodsReceipt
    {
        $grn = GoodsReceipt::query()->create([
            'company_id' => $order->company_id,
            'branch_id' => $order->branch_id,
            'purchase_order_id' => $order->id,
            'warehouse_id' => $warehouse->id,
            'receipt_number' => 'GRN-CAP-'.uniqid(),
            'receipt_date' => now()->toDateString(),
            'status' => \App\Enums\GoodsReceiptStatus::Draft,
            'received_by' => $user->id,
        ]);

        $grn->items()->create([
            'purchase_order_item_id' => $poItem->id,
            'item_classification' => ProcurementItemClassification::FixedAsset,
            'asset_category_id' => $poItem->asset_category_id,
            'quantity_received' => $qty,
            'unit_cost' => $poItem->unit_cost,
        ]);

        return $grn->fresh(['items']);
    }

    protected function makeCategory(): AssetCategory
    {
        return AssetCategory::query()->create([
            'company_id' => Company::query()->first()->id,
            'name' => 'Machinery',
            'code' => 'MCH-'.uniqid(),
            'asset_type' => AssetType::Machine->value,
            'useful_life_years' => 5,
            'depreciation_method' => 'straight_line',
            'default_gl_code' => '1530',
            'is_active' => true,
        ]);
    }

    protected function acquisitionUser(): User
    {
        $user = User::factory()->create([
            'company_id' => Company::query()->first()->id,
            'is_active' => true,
        ]);
        $user->assignRole('Company Admin');

        return $user;
    }
}
