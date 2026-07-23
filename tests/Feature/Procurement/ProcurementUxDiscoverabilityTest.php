<?php

namespace Tests\Feature\Procurement;

use App\Enums\AssetType;
use App\Enums\ProcurementItemClassification;
use App\Enums\PurchaseRequestStatus;
use App\Models\Assets\AssetCategory;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Inventory\InventoryItem;
use App\Models\Procurement\PurchaseRequest;
use App\Models\Procurement\Rfq;
use App\Models\Procurement\Vendor;
use App\Models\User;
use App\Support\Procurement\ProcurementJourneyPresenter;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProcurementUxDiscoverabilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_procurement_section_lists_purchase_requests_first(): void
    {
        $user = $this->companyAdmin();

        $response = $this->actingAs($user)->get(route('admin.workspaces.supply-chain.section', ['section' => 'procurement']));

        $response->assertOk();
        $response->assertSeeInOrder([
            route('admin.procurement.requests.index'),
            route('admin.procurement.vendors.index'),
            route('admin.procurement.rfqs.index'),
            route('admin.procurement.orders.index'),
            route('admin.procurement.receipts.index'),
            route('admin.procurement.approvals.index'),
        ], false);
        $response->assertSee(__('Requests'), false);
        $response->assertSee(__('Suppliers'), false);
        $response->assertSee(__('RFQs'), false);
        $response->assertDontSee(__('Supplier Performance'), false);
        $response->assertDontSee(__('Vendor Comparison'), false);
        $response->assertDontSee(__('Supplier Quotations'), false);
    }

    public function test_purchase_request_navigation_requires_permission(): void
    {
        [$company, $branch, $user] = $this->procurementContext(['procurement.orders.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.procurement.requests.index'))
            ->assertForbidden();
    }

    public function test_purchase_request_index_is_accessible_with_view_permission(): void
    {
        [$company, $branch, $user] = $this->procurementContext(['procurement.requests.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.procurement.requests.index'))
            ->assertOk()
            ->assertSee(__('Purchase Requests'), false);
    }

    public function test_purchase_request_form_captures_classification_fields(): void
    {
        [$company, $branch, $user, $item, $category] = $this->procurementContext([
            'procurement.requests.view',
            'procurement.requests.create',
            'procurement.requests.edit',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)->post(route('admin.procurement.requests.store'), [
            'required_date' => now()->addWeek()->toDateString(),
            'reason' => 'New press equipment',
            'items' => [[
                'inventory_item_id' => $item->id,
                'item_classification' => ProcurementItemClassification::FixedAsset->value,
                'asset_category_id' => $category->id,
                'capitalization_required' => '1',
                'description' => 'Digital press',
                'quantity' => 1,
                'estimated_unit_cost' => 250000,
            ]],
        ]);

        $response->assertRedirect();

        $request = PurchaseRequest::query()->firstOrFail();
        $line = $request->items()->firstOrFail();

        $this->assertSame(ProcurementItemClassification::FixedAsset, $line->item_classification);
        $this->assertSame($category->id, $line->asset_category_id);
        $this->assertTrue($line->capitalization_required);
    }

    public function test_purchase_request_show_renders_journey_and_classification(): void
    {
        [$company, $branch, $user, $item, $category] = $this->procurementContext([
            'procurement.requests.view',
            'procurement.requests.create',
        ]);
        $vendor = Vendor::factory()->create(['company_id' => $company->id, 'vendor_name' => 'Alpha Supplies']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $request = PurchaseRequest::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'request_number' => 'PR-UX-001',
            'requested_by' => $user->id,
            'status' => PurchaseRequestStatus::Approved,
            'reason' => 'Store replenishment',
        ]);
        $request->items()->create([
            'inventory_item_id' => $item->id,
            'item_classification' => ProcurementItemClassification::Consumable,
            'asset_category_id' => null,
            'capitalization_required' => false,
            'description' => $item->item_name,
            'quantity' => 5,
            'estimated_unit_cost' => 20,
            'line_total' => 100,
        ]);

        $rfq = Rfq::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'purchase_request_id' => $request->id,
            'rfq_number' => 'RFQ-UX-001',
            'issue_date' => now()->toDateString(),
            'status' => \App\Enums\RfqStatus::Open,
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('admin.procurement.requests.show', $request));

        $response->assertOk();
        $response->assertSee(__('Procurement journey'), false);
        $response->assertSee(__('Approval status'), false);
        $response->assertSee(__('Conversion status'), false);
        $response->assertSee(__('Classification'), false);
        $response->assertSee(__('Consumable'), false);
        $response->assertSee(route('admin.procurement.rfqs.show', $rfq), false);
        $response->assertSee('RFQ-UX-001', false);
        $response->assertSee(__('Purchase Request'), false);
        $response->assertSee(__('Inventory / Asset'), false);
    }

    public function test_journey_presenter_marks_direct_conversion_path(): void
    {
        [$company, $branch, $user, $item] = $this->procurementContext(['procurement.requests.view']);
        $vendor = Vendor::factory()->create(['company_id' => $company->id]);

        $request = PurchaseRequest::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'request_number' => 'PR-UX-002',
            'requested_by' => $user->id,
            'status' => PurchaseRequestStatus::ConvertedToPo,
        ]);
        $request->items()->create([
            'inventory_item_id' => $item->id,
            'item_classification' => ProcurementItemClassification::InventoryItem,
            'description' => $item->item_name,
            'quantity' => 2,
            'estimated_unit_cost' => 50,
            'line_total' => 100,
        ]);

        $order = $request->purchaseOrder()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'vendor_id' => $vendor->id,
            'po_number' => 'PO-UX-002',
            'order_date' => now()->toDateString(),
            'status' => \App\Enums\PurchaseOrderStatus::Draft,
            'subtotal' => 100,
            'total_amount' => 100,
            'prepared_by' => $user->id,
        ]);

        $journey = app(ProcurementJourneyPresenter::class)->present($request->fresh(['items', 'purchaseOrder', 'rfqs']));

        $this->assertSame(__('Direct conversion'), $journey['conversion_path']);
        $this->assertSame('skipped', collect($journey['steps'])->firstWhere('key', 'rfq')['state']);
        $this->assertSame('complete', collect($journey['steps'])->firstWhere('key', 'po')['state']);
        $this->assertSame($order->po_number, collect($journey['steps'])->firstWhere('key', 'po')['document']);
    }

    /**
     * @param  list<string>  $permissions
     * @return array{0: Company, 1: Branch, 2: User, 3: InventoryItem, 4: AssetCategory}
     */
    protected function procurementContext(array $permissions): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $role = Role::create(['name' => 'Procurement UX Tester '.uniqid(), 'guard_name' => 'web']);
        $role->syncPermissions($permissions);
        $user->assignRole($role);

        $item = InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);

        $category = AssetCategory::query()->create([
            'company_id' => $company->id,
            'name' => 'Production Equipment',
            'code' => 'PE-'.uniqid(),
            'asset_type' => AssetType::Machine->value,
            'useful_life_years' => 5,
            'depreciation_method' => 'straight_line',
            'default_gl_code' => '1530',
            'is_active' => true,
        ]);

        return [$company, $branch, $user, $item, $category];
    }

    protected function companyAdmin(): User
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $user->assignRole('Company Admin');

        return $user;
    }
}
