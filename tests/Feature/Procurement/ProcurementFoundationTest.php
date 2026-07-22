<?php

namespace Tests\Feature\Procurement;

use App\Enums\PurchaseOrderStatus;
use App\Enums\PurchaseRequestStatus;
use App\Enums\VendorStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\Warehouse;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseRequest;
use App\Models\Procurement\Vendor;
use App\Models\User;
use App\Support\Procurement\GoodsReceiptService;
use App\Support\Procurement\PurchaseRequestService;
use Database\Seeders\GlAccountTypeSeeder;
use Database\Seeders\InventoryFoundationSeeder;
use Database\Seeders\JanaPrintsAccountingPeriodsSeeder;
use Database\Seeders\JanaPrintsChartOfAccountsSeeder;
use Database\Seeders\JanaPrintsPostingEngineSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProcurementFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(PlatformConfigurationSeeder::class);
    }

    public function test_company_isolation_for_vendors(): void
    {
        $companyA = Company::query()->where('code', 'JANA')->firstOrFail();
        $companyB = Company::factory()->create();
        $branchA = Branch::query()->where('company_id', $companyA->id)->firstOrFail();
        $user = $this->procurementUser($companyA, $branchA, ['procurement.vendors.view']);
        $vendorB = Vendor::factory()->create(['company_id' => $companyB->id]);

        $this->actingAs($user)->get(route('admin.procurement.vendors.show', $vendorB))->assertForbidden();
    }

    public function test_vendor_index_is_paginated(): void
    {
        [$company, $branch, $user] = $this->procurementContext(['procurement.vendors.view']);
        Vendor::factory()->count(20)->create(['company_id' => $company->id]);
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)->get(route('admin.procurement.vendors.index'));

        $response->assertOk();
        $response->assertViewHas('vendors', fn ($vendors) => method_exists($vendors, 'links'));
    }

    public function test_purchase_request_creation_and_approval(): void
    {
        [$company, $branch, $user, $item] = $this->procurementContext([
            'procurement.requests.view', 'procurement.requests.create', 'procurement.requests.edit', 'procurement.requests.approve',
        ]);
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)->post(route('admin.procurement.requests.store'), [
            'required_date' => now()->addWeek()->toDateString(),
            'reason' => 'Restock paper',
            'items' => [[
                'inventory_item_id' => $item->id,
                'description' => $item->item_name,
                'quantity' => 10,
                'estimated_unit_cost' => 100,
            ]],
        ]);

        $response->assertRedirect();
        $request = PurchaseRequest::query()->firstOrFail();
        $this->assertSame(PurchaseRequestStatus::Draft, $request->status);

        PurchaseRequestService::submit($request, $user->id);
        $this->assertSame(PurchaseRequestStatus::Approved, $request->fresh()->status);
    }

    public function test_purchase_order_creation_from_request(): void
    {
        [$company, $branch, $user, $item] = $this->procurementContext([
            'procurement.requests.view', 'procurement.requests.create', 'procurement.requests.edit', 'procurement.requests.approve', 'procurement.orders.create',
        ]);
        $vendor = Vendor::factory()->create(['company_id' => $company->id]);
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $request = PurchaseRequest::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'request_number' => 'PR-TEST-001',
            'requested_by' => $user->id,
            'status' => PurchaseRequestStatus::Approved,
        ]);
        $request->items()->create([
            'inventory_item_id' => $item->id,
            'description' => $item->item_name,
            'quantity' => 5,
            'estimated_unit_cost' => 50,
            'line_total' => 250,
        ]);

        $order = PurchaseRequestService::convertToPurchaseOrder($request, $vendor->id, $user->id, 'PO-TEST-001');

        $this->assertSame(PurchaseRequestStatus::ConvertedToPo, $request->fresh()->status);
        $this->assertSame(PurchaseOrderStatus::Draft, $order->status);
        $this->assertCount(1, $order->items);
    }

    public function test_goods_receipt_posts_to_inventory(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->procurementContext([
            'procurement.orders.view', 'procurement.orders.create', 'procurement.orders.receive', 'inventory.view',
        ]);
        $this->seed(GlAccountTypeSeeder::class);
        $this->seed(JanaPrintsChartOfAccountsSeeder::class);
        $this->seed(JanaPrintsAccountingPeriodsSeeder::class);
        $this->seed(JanaPrintsPostingEngineSeeder::class);
        $this->seed(InventoryFoundationSeeder::class);
        $vendor = Vendor::factory()->create(['company_id' => $company->id]);
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $order = PurchaseOrder::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'vendor_id' => $vendor->id,
            'po_number' => 'PO-GR-001',
            'order_date' => now()->toDateString(),
            'status' => PurchaseOrderStatus::Sent,
            'subtotal' => 500,
            'total_amount' => 500,
            'prepared_by' => $user->id,
        ]);
        $poItem = $order->items()->create([
            'inventory_item_id' => $item->id,
            'description' => $item->item_name,
            'quantity' => 10,
            'unit_cost' => 50,
            'line_total' => 500,
        ]);

        $response = $this->actingAs($user)->post(route('admin.procurement.orders.receive.store', $order), [
            'warehouse_id' => $warehouse->id,
            'receipt_date' => now()->toDateString(),
            'items' => [[
                'purchase_order_item_id' => $poItem->id,
                'quantity_received' => 10,
            ]],
        ]);

        $response->assertRedirect();
        $goodsReceipt = $order->goodsReceipts()->firstOrFail();
        GoodsReceiptService::post($goodsReceipt, $user->id);

        $goodsReceipt->refresh();
        $this->assertNotNull($goodsReceipt->stock_receipt_id);
        $this->assertSame(PurchaseOrderStatus::Received, $order->fresh()->status);
    }

    public function test_viewer_cannot_create_vendor(): void
    {
        [, , $user] = $this->procurementContext(['procurement.vendors.view']);

        $this->actingAs($user)->get(route('admin.procurement.vendors.create'))->assertForbidden();
    }

    public function test_vendor_store_from_modal_returns_success_marker(): void
    {
        [$company, $branch, $user] = $this->procurementContext([
            'procurement.vendors.create',
            'procurement.vendors.view',
        ]);
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)
            ->withHeader('Turbo-Frame', 'erp-form-modal')
            ->post(route('admin.procurement.vendors.store'), [
                'vendor_name' => 'Modal Vendor Ltd',
                'vendor_type' => 'supplier',
                'status' => 'active',
            ]);

        $response->assertOk();
        $response->assertSee('data-erp-modal-success', false);
        $response->assertSee(__('Vendor created.'), false);

        $this->assertDatabaseHas('vendors', [
            'vendor_name' => 'Modal Vendor Ltd',
            'company_id' => $company->id,
        ]);
    }

    public function test_vendor_update_from_modal_returns_success_marker(): void
    {
        [$company, $branch, $user] = $this->procurementContext([
            'procurement.vendors.create',
            'procurement.vendors.edit',
            'procurement.vendors.view',
        ]);
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $vendor = Vendor::factory()->create([
            'company_id' => $company->id,
            'vendor_name' => 'Original Vendor',
            'vendor_type' => 'supplier',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)
            ->withHeader('Turbo-Frame', 'erp-form-modal')
            ->put(route('admin.procurement.vendors.update', $vendor), [
                'vendor_name' => 'Updated Vendor',
                'vendor_type' => 'contractor',
                'status' => 'active',
            ]);

        $response->assertOk();
        $response->assertSee('data-erp-modal-success', false);
        $response->assertSee(__('Vendor updated.'), false);

        $this->assertDatabaseHas('vendors', [
            'id' => $vendor->id,
            'vendor_name' => 'Updated Vendor',
        ]);
    }

    /**
     * @param  list<string>  $permissions
     * @return array{0: Company, 1: Branch, 2: User, 3?: InventoryItem, 4?: Warehouse}
     */
    protected function procurementContext(array $permissions): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $user = $this->procurementUser($company, $branch, $permissions);

        $item = InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);
        $warehouse = Warehouse::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);

        return [$company, $branch, $user, $item, $warehouse];
    }

    /**
     * @param  list<string>  $permissions
     */
    protected function procurementUser(Company $company, Branch $branch, array $permissions): User
    {
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $role = Role::create(['name' => 'Procurement Tester '.uniqid(), 'guard_name' => 'web']);
        $role->syncPermissions($permissions);
        $user->assignRole($role);

        return $user;
    }
}
