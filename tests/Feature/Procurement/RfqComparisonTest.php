<?php

namespace Tests\Feature\Procurement;

use App\Enums\PurchaseRequestStatus;
use App\Enums\RfqStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Inventory\InventoryItem;
use App\Models\Procurement\PurchaseRequest;
use App\Models\Procurement\Rfq;
use App\Models\Procurement\Vendor;
use App\Models\User;
use App\Support\Procurement\RFQService;
use App\Support\Procurement\VendorComparisonService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RfqComparisonTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(PlatformConfigurationSeeder::class);
    }

    public function test_rfq_workflow_and_vendor_comparison(): void
    {
        [$company, $branch, $user, $item] = $this->context();
        $vendorA = Vendor::factory()->create(['company_id' => $company->id, 'vendor_name' => 'Vendor A']);
        $vendorB = Vendor::factory()->create(['company_id' => $company->id, 'vendor_name' => 'Vendor B']);

        $request = PurchaseRequest::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'request_number' => 'PR-RFQ-TEST',
            'requested_by' => $user->id,
            'status' => PurchaseRequestStatus::Approved,
        ]);
        $request->items()->create([
            'inventory_item_id' => $item->id,
            'description' => $item->item_name,
            'quantity' => 10,
            'estimated_unit_cost' => 50,
            'line_total' => 500,
        ]);

        $rfq = RFQService::createFromPurchaseRequest(
            $request,
            'RFQ-TEST-001',
            $user->id,
            now()->addWeek()->toDateString(),
            [$vendorA->id, $vendorB->id],
        );

        RFQService::issue($rfq);
        $this->assertEquals(RfqStatus::Open, $rfq->fresh()->status);

        $rfqVendorA = $rfq->vendors()->where('vendor_id', $vendorA->id)->firstOrFail();
        $rfqVendorB = $rfq->vendors()->where('vendor_id', $vendorB->id)->firstOrFail();

        $linesA = $rfq->items->map(fn ($rfqItem) => [
            'rfq_item_id' => $rfqItem->id,
            'quoted_price' => 45,
            'lead_time_days' => 5,
        ])->all();

        $linesB = $rfq->items->map(fn ($rfqItem) => [
            'rfq_item_id' => $rfqItem->id,
            'quoted_price' => 48,
            'lead_time_days' => 3,
        ])->all();

        RFQService::recordVendorResponse($rfqVendorA, $linesA);
        RFQService::recordVendorResponse($rfqVendorB, $linesB);

        RFQService::close($rfq->fresh());
        $comparison = VendorComparisonService::buildMatrix($rfq->fresh());

        $this->assertCount(2, $comparison['matrix']);
        $this->assertNotNull($comparison['recommended_vendor_id']);

        VendorComparisonService::persistComparison($rfq->fresh(), $user->id);
        RFQService::award($rfq->fresh(), $vendorA->id);

        $order = RFQService::convertToPurchaseOrder($rfq->fresh(), 'PO-RFQ-001', $user->id);

        $this->assertEquals(RfqStatus::ConvertedToPo, $rfq->fresh()->status);
        $this->assertEquals(45.0, (float) $order->items->first()->unit_cost);
        $this->assertDatabaseHas('purchase_orders', ['po_number' => 'PO-RFQ-001']);
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User, 3: InventoryItem}
     */
    private function context(): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
        ]);
        $user->assignRole('Company Admin');

        $item = InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);

        return [$company, $branch, $user, $item];
    }
}
