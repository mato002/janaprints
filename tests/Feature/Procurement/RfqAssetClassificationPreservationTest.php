<?php

namespace Tests\Feature\Procurement;

use App\Enums\AssetAcquisitionSource;
use App\Enums\AssetType;
use App\Enums\CapitalizationCandidateStatus;
use App\Enums\DepreciationMethod;
use App\Enums\FixedAssetStatus;
use App\Enums\ProcurementItemClassification;
use App\Enums\PurchaseOrderStatus;
use App\Enums\PurchaseRequestStatus;
use App\Enums\RfqStatus;
use App\Models\Assets\AssetCapitalizationCandidate;
use App\Models\Assets\AssetCategory;
use App\Models\Assets\FixedAsset;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\Warehouse;
use App\Models\Procurement\GoodsReceipt;
use App\Models\Procurement\PurchaseRequest;
use App\Models\Procurement\Vendor;
use App\Models\User;
use App\Services\Assets\AssetCapitalizationService;
use App\Support\Procurement\GoodsReceiptService;
use App\Support\Procurement\RfqAwardService;
use App\Support\Procurement\RFQService;
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

class RfqAssetClassificationPreservationTest extends TestCase
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

    public function test_rfq_inventory_item_preserves_classification_through_po(): void
    {
        [$user, $vendor, $request, $item] = $this->procurementContext(
            ProcurementItemClassification::InventoryItem,
            capitalizationRequired: false,
        );

        $order = $this->convertRfqToPurchaseOrder($request, $vendor, $user);

        $poItem = $order->items->first();
        $this->assertSame(ProcurementItemClassification::InventoryItem, $poItem->item_classification);
        $this->assertFalse((bool) $poItem->capitalization_required);
        $this->assertNull($poItem->asset_category_id);
    }

    public function test_rfq_fixed_asset_preserves_classification_through_po(): void
    {
        [$user, $vendor, $request, $item, $category] = $this->procurementContext(
            ProcurementItemClassification::FixedAsset,
            capitalizationRequired: true,
        );

        $rfq = $this->prepareRfqForComparison($request, $vendor, $user, quotedPrice: 250000);
        $rfqItem = $rfq->items->first();

        $this->assertSame(ProcurementItemClassification::FixedAsset, $rfqItem->item_classification);
        $this->assertSame($category->id, $rfqItem->asset_category_id);
        $this->assertTrue($rfqItem->capitalization_required);
        $this->assertSame(60, $rfqItem->asset_useful_life);
        $this->assertSame('straight_line', $rfqItem->asset_depreciation_method);

        RFQService::award($rfq->fresh(), $vendor->id);
        $order = RFQService::convertToPurchaseOrder($rfq->fresh(), 'PO-RFQ-FA-'.uniqid(), $user->id);
        $poItem = $order->items->first();

        $this->assertSame(ProcurementItemClassification::FixedAsset, $poItem->item_classification);
        $this->assertSame($category->id, $poItem->asset_category_id);
        $this->assertTrue($poItem->capitalization_required);
        $this->assertSame(60, $poItem->asset_useful_life);
        $this->assertSame('straight_line', $poItem->asset_depreciation_method);
    }

    public function test_rfq_lease_asset_preserves_classification_via_award_service(): void
    {
        [$user, $vendor, $request, , $category] = $this->procurementContext(
            ProcurementItemClassification::LeaseAsset,
            capitalizationRequired: true,
            usefulLifeMonths: 36,
            depreciationMethod: 'declining_balance',
        );

        $rfq = $this->prepareRfqForComparison($request, $vendor, $user, quotedPrice: 120000);
        $result = RfqAwardService::awardFull($rfq, $vendor->id, $user->id);
        $poItem = $result['purchase_orders']->first()->items->first();

        $this->assertSame(ProcurementItemClassification::LeaseAsset, $poItem->item_classification);
        $this->assertSame($category->id, $poItem->asset_category_id);
        $this->assertTrue($poItem->capitalization_required);
        $this->assertSame(36, $poItem->asset_useful_life);
        $this->assertSame('declining_balance', $poItem->asset_depreciation_method);
    }

    public function test_grn_routes_fixed_asset_to_capitalization_not_inventory(): void
    {
        [$user, $vendor, $request, , $category] = $this->procurementContext(
            ProcurementItemClassification::FixedAsset,
            capitalizationRequired: true,
        );

        $order = $this->convertRfqToPurchaseOrder($request, $vendor, $user);
        $order->update(['status' => PurchaseOrderStatus::Sent]);
        $poItem = $order->items->first();
        $warehouse = Warehouse::query()->where('company_id', $order->company_id)->first();

        $grn = $this->createGoodsReceipt($order, $poItem, $warehouse, $user);
        $grnItem = $grn->items->first();

        $this->assertSame(ProcurementItemClassification::FixedAsset, $grnItem->item_classification);
        $this->assertSame($category->id, $grnItem->asset_category_id);
        $this->assertTrue($grnItem->capitalization_required);

        GoodsReceiptService::post($grn, $user->id);

        $this->assertNull($grn->fresh()->stock_receipt_id);
        $this->assertDatabaseHas('asset_capitalization_candidates', [
            'goods_receipt_item_id' => $grnItem->id,
            'asset_category_id' => $category->id,
            'capitalization_required' => true,
            'asset_useful_life' => 60,
            'asset_depreciation_method' => 'straight_line',
            'status' => CapitalizationCandidateStatus::Ready->value,
        ]);
    }

    public function test_grn_routes_inventory_item_to_stock_receipt(): void
    {
        [$user, $vendor, $request] = $this->procurementContext(
            ProcurementItemClassification::InventoryItem,
            capitalizationRequired: false,
            unitCost: 500,
        );

        $order = $this->convertRfqToPurchaseOrder($request, $vendor, $user);
        $order->update(['status' => PurchaseOrderStatus::Sent]);
        $poItem = $order->items->first();
        $warehouse = Warehouse::query()->where('company_id', $order->company_id)->first();

        $grn = $this->createGoodsReceipt($order, $poItem, $warehouse, $user);
        GoodsReceiptService::post($grn, $user->id);

        $this->assertNotNull($grn->fresh()->stock_receipt_id);
        $this->assertDatabaseMissing('asset_capitalization_candidates', [
            'goods_receipt_item_id' => $grn->items->first()->id,
        ]);
    }

    public function test_capitalization_candidate_and_asset_register_receive_preserved_attributes(): void
    {
        [$user, $vendor, $request, , $category] = $this->procurementContext(
            ProcurementItemClassification::FixedAsset,
            capitalizationRequired: true,
            usefulLifeMonths: 48,
            depreciationMethod: 'straight_line',
        );

        $order = $this->convertRfqToPurchaseOrder($request, $vendor, $user);
        $order->update(['status' => PurchaseOrderStatus::Sent]);
        $poItem = $order->items->first();
        $warehouse = Warehouse::query()->where('company_id', $order->company_id)->first();
        $grn = $this->createGoodsReceipt($order, $poItem, $warehouse, $user);
        GoodsReceiptService::post($grn, $user->id);

        $candidate = AssetCapitalizationCandidate::query()
            ->where('goods_receipt_id', $grn->id)
            ->firstOrFail();

        $this->assertSame(48, $candidate->asset_useful_life);
        $this->assertSame('straight_line', $candidate->asset_depreciation_method);

        $assets = app(AssetCapitalizationService::class)->capitalize($candidate, [
            'quantity' => 1,
            'asset_category_id' => $candidate->asset_category_id,
            'asset_name' => 'RFQ Press',
            'branch_id' => $candidate->branch_id,
        ], $user->id, false);

        $asset = $assets[0]->fresh();
        $this->assertSame(FixedAssetStatus::Active, $asset->status);
        $this->assertSame(AssetAcquisitionSource::Procurement, $asset->acquisition_source);
        $this->assertSame(4, $asset->useful_life_years);
        $this->assertSame(DepreciationMethod::StraightLine, $asset->depreciation_method);
        $this->assertSame($order->id, $asset->purchase_order_id);
    }

    /**
     * @return array{0: User, 1: Vendor, 2: PurchaseRequest, 3: InventoryItem, 4?: AssetCategory}
     */
    protected function procurementContext(
        ProcurementItemClassification $classification,
        bool $capitalizationRequired,
        ?int $usefulLifeMonths = null,
        ?string $depreciationMethod = null,
        float $unitCost = 250000,
    ): array {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $user->assignRole('Company Admin');

        $category = null;
        if ($classification->isCapitalizable()) {
            $category = AssetCategory::query()->create([
                'company_id' => $company->id,
                'name' => 'Production Equipment',
                'code' => 'PE-'.uniqid(),
                'asset_type' => AssetType::Machine->value,
                'useful_life_years' => 5,
                'depreciation_method' => $depreciationMethod ?? 'straight_line',
                'default_gl_code' => '1530',
                'is_active' => true,
            ]);
            $usefulLifeMonths ??= $category->usefulLifeMonths();
        }

        $item = InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);

        $request = PurchaseRequest::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'request_number' => 'PR-RFQ-CLS-'.uniqid(),
            'requested_by' => $user->id,
            'status' => PurchaseRequestStatus::Approved,
            'total_amount' => $unitCost,
        ]);

        $request->items()->create([
            'inventory_item_id' => $item->id,
            'item_classification' => $classification,
            'asset_category_id' => $category?->id,
            'capitalization_required' => $capitalizationRequired,
            'asset_useful_life' => $usefulLifeMonths,
            'asset_depreciation_method' => $depreciationMethod ?? $category?->depreciation_method,
            'description' => $item->item_name,
            'quantity' => 1,
            'estimated_unit_cost' => $unitCost,
            'line_total' => $unitCost,
        ]);

        $vendor = Vendor::factory()->create(['company_id' => $company->id]);

        return [$user, $vendor, $request->fresh(['items']), $item, $category];
    }

    protected function convertRfqToPurchaseOrder(
        PurchaseRequest $request,
        Vendor $vendor,
        User $user,
    ): \App\Models\Procurement\PurchaseOrder {
        $rfq = $this->prepareAwardedRfq($request, $vendor, $user, quotedPrice: (float) $request->total_amount);

        return RFQService::convertToPurchaseOrder($rfq->fresh(), 'PO-RFQ-CLS-'.uniqid(), $user->id);
    }

    protected function prepareRfqForComparison(
        PurchaseRequest $request,
        Vendor $vendor,
        User $user,
        float $quotedPrice,
    ): \App\Models\Procurement\Rfq {
        $rfq = RFQService::createFromPurchaseRequest(
            $request,
            'RFQ-CLS-'.uniqid(),
            $user->id,
            now()->addWeek()->toDateString(),
            [$vendor->id],
        );

        RFQService::issue($rfq, $user->id);
        $rfqVendor = $rfq->vendors()->where('vendor_id', $vendor->id)->firstOrFail();
        $lines = $rfq->items->map(fn ($rfqItem) => [
            'rfq_item_id' => $rfqItem->id,
            'quoted_price' => $quotedPrice,
        ])->all();
        RFQService::recordVendorResponse($rfqVendor, $lines);
        RFQService::close($rfq->fresh());

        return $rfq->fresh(['items']);
    }

    protected function prepareAwardedRfq(
        PurchaseRequest $request,
        Vendor $vendor,
        User $user,
        float $quotedPrice,
    ): \App\Models\Procurement\Rfq {
        $rfq = $this->prepareRfqForComparison($request, $vendor, $user, $quotedPrice);
        RFQService::award($rfq->fresh(), $vendor->id);

        return $rfq->fresh(['items']);
    }

    protected function createGoodsReceipt(
        \App\Models\Procurement\PurchaseOrder $order,
        \App\Models\Procurement\PurchaseOrderItem $poItem,
        Warehouse $warehouse,
        User $user,
    ): GoodsReceipt {
        $grn = GoodsReceipt::query()->create([
            'company_id' => $order->company_id,
            'branch_id' => $order->branch_id,
            'purchase_order_id' => $order->id,
            'warehouse_id' => $warehouse->id,
            'receipt_number' => 'GRN-RFQ-'.uniqid(),
            'receipt_date' => now()->toDateString(),
            'status' => \App\Enums\GoodsReceiptStatus::Draft,
            'received_by' => $user->id,
        ]);

        $grn->items()->create([
            'purchase_order_item_id' => $poItem->id,
            'inventory_item_id' => $poItem->inventory_item_id,
            'item_classification' => $poItem->item_classification,
            'asset_category_id' => $poItem->asset_category_id,
            'capitalization_required' => $poItem->capitalization_required,
            'asset_useful_life' => $poItem->asset_useful_life,
            'asset_depreciation_method' => $poItem->asset_depreciation_method,
            'quantity_received' => $poItem->quantity,
            'unit_cost' => $poItem->unit_cost,
        ]);

        return $grn->fresh(['items']);
    }
}
