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
use App\Support\Procurement\RfqAwardService;
use App\Support\Procurement\RFQService;
use App\Support\Procurement\VendorComparisonService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class VendorComparisonAwardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(PlatformConfigurationSeeder::class);
    }

    public function test_comparison_workspace_generates_scored_matrix(): void
    {
        [$rfq, $vendorA] = $this->seedComparableRfq();

        $workspace = VendorComparisonService::buildWorkspace($rfq->fresh());

        $this->assertCount(2, $workspace['matrix']);
        $this->assertNotNull($workspace['recommended_vendor_id']);
        $this->assertSame(40, $workspace['weights']['price']);
        $this->assertArrayHasKey('total_cost', $workspace['matrix'][0]);
        $this->assertArrayHasKey('supplier_rating', $workspace['matrix'][0]);
        $this->assertSame($vendorA->id, $workspace['recommended_vendor_id']);
    }

    public function test_supplier_scoring_respects_custom_weights(): void
    {
        [$rfq] = $this->seedComparableRfq();

        $workspace = VendorComparisonService::buildWorkspace($rfq->fresh(), [
            'price' => 10,
            'performance' => 10,
            'lead_time' => 70,
            'quality' => 10,
        ]);

        $this->assertSame(70, $workspace['weights']['lead_time']);
        $this->assertGreaterThan(0, $workspace['matrix'][0]['score']);
    }

    public function test_award_supplier_generates_purchase_order_automatically(): void
    {
        [$rfq, $vendorA, $user] = $this->seedComparableRfq();
        RFQService::close($rfq->fresh());

        $result = RfqAwardService::awardFull($rfq->fresh(), $vendorA->id, $user->id, true);

        $this->assertEquals(RfqStatus::ConvertedToPo, $result['rfq']->status);
        $this->assertCount(1, $result['purchase_orders']);
        $this->assertEquals(45.0, (float) $result['purchase_orders']->first()->items->first()->unit_cost);
        $this->assertDatabaseHas('rfq_award_lines', ['rfq_id' => $rfq->id, 'vendor_id' => $vendorA->id]);
    }

    public function test_split_award_generates_multiple_purchase_orders(): void
    {
        [$rfq, $vendorA, $user, $vendorB] = $this->seedComparableRfq();
        RFQService::close($rfq->fresh());
        $item = $rfq->items()->firstOrFail();

        $result = RfqAwardService::splitAward($rfq->fresh(), [
            ['vendor_id' => $vendorA->id, 'rfq_item_id' => $item->id, 'quantity' => 6],
            ['vendor_id' => $vendorB->id, 'rfq_item_id' => $item->id, 'quantity' => 4],
        ], $user->id, true);

        $this->assertEquals(RfqStatus::ConvertedToPo, $result['rfq']->status);
        $this->assertCount(2, $result['purchase_orders']);
        $this->assertEquals('split', $result['rfq']->award_type);
    }

    public function test_workspace_requires_view_permission(): void
    {
        [$rfq, , $user] = $this->seedComparableRfq(['procurement.rfq.view']);

        $this->actingAs($user)
            ->get(route('admin.procurement.vendor-comparison.index'))
            ->assertForbidden();
    }

    public function test_award_action_requires_award_permission(): void
    {
        [$rfq, $vendorA, $user] = $this->seedComparableRfq(['procurement.vendor_comparison.view']);

        RFQService::close($rfq->fresh());

        $this->actingAs($user)
            ->post(route('admin.procurement.vendor-comparison.award', $rfq), [
                'vendor_id' => $vendorA->id,
            ])
            ->assertForbidden();
    }

    public function test_comparison_workspace_page_loads_with_permission(): void
    {
        [$rfq, , $user] = $this->seedComparableRfq(['procurement.vendor_comparison.view']);

        RFQService::close($rfq->fresh());

        $this->actingAs($user)
            ->get(route('admin.procurement.vendor-comparison.show', $rfq))
            ->assertOk()
            ->assertSee(__('Supplier comparison grid'), false)
            ->assertSee(__('RFQ requirements'), false);
    }

    /**
     * @return array{0: Rfq, 1: Vendor, 2: User, 3: Vendor}
     */
    protected function seedComparableRfq(?array $permissions = null): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
        ]);

        $permissions ??= [
            'procurement.vendor_comparison.view',
            'procurement.vendor_comparison.award',
            'procurement.vendor_comparison.manage',
        ];

        $role = Role::findByName('Company Admin', 'web');
        $role->syncPermissions($permissions);
        $user->assignRole('Company Admin');

        $item = InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);

        $vendorA = Vendor::factory()->create(['company_id' => $company->id, 'vendor_name' => 'Alpha Supplies']);
        $vendorB = Vendor::factory()->create(['company_id' => $company->id, 'vendor_name' => 'Beta Materials']);

        $request = PurchaseRequest::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'request_number' => 'PR-VC-001',
            'requested_by' => $user->id,
            'status' => PurchaseRequestStatus::Approved,
            'required_date' => now()->addWeek()->toDateString(),
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
            'RFQ-VC-001',
            $user->id,
            now()->addWeek()->toDateString(),
            [$vendorA->id, $vendorB->id],
        );

        RFQService::issue($rfq);

        $rfqVendorA = $rfq->vendors()->where('vendor_id', $vendorA->id)->firstOrFail();
        $rfqVendorB = $rfq->vendors()->where('vendor_id', $vendorB->id)->firstOrFail();

        RFQService::recordVendorResponse($rfqVendorA, $rfq->items->map(fn ($rfqItem) => [
            'rfq_item_id' => $rfqItem->id,
            'quoted_price' => 45,
            'lead_time_days' => 5,
            'warranty' => '12 months',
            'delivery_terms' => 'Ex-works',
        ])->all());

        RFQService::recordVendorResponse($rfqVendorB, $rfq->items->map(fn ($rfqItem) => [
            'rfq_item_id' => $rfqItem->id,
            'quoted_price' => 48,
            'lead_time_days' => 3,
            'warranty' => '6 months',
            'delivery_terms' => 'Delivered',
        ])->all());

        return [$rfq->fresh(['items', 'vendors']), $vendorA, $user, $vendorB];
    }
}
