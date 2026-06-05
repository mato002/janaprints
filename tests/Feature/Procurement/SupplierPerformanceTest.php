<?php

namespace Tests\Feature\Procurement;

use App\Enums\GoodsReceiptStatus;
use App\Enums\PurchaseOrderStatus;
use App\Jobs\Commercial\ProcessCommercialReportExportJob;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Inventory\Warehouse;
use App\Models\Procurement\GoodsReceipt;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\Vendor;
use App\Models\User;
use App\Support\Procurement\Performance\SupplierPerformanceQueries;
use App\Support\Procurement\Performance\SupplierPerformanceScope;
use App\Support\Procurement\Performance\SupplierPerformanceScoreCalculator;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SupplierPerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_supplier_performance_requires_permission(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['procurement.orders.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.procurement.supplier-performance.index'))
            ->assertForbidden();
    }

    public function test_supplier_performance_index_loads(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['procurement.performance.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.procurement.supplier-performance.index'))
            ->assertOk()
            ->assertSee(__('Supplier Performance'), false)
            ->assertSee(__('Supplier Scorecard'), false)
            ->assertSee(__('Delivery Analysis'), false)
            ->assertSee(__('Quality Analysis'), false)
            ->assertSee(__('Rankings'), false);
    }

    public function test_score_calculation_assigns_grade_from_historical_metrics(): void
    {
        [$company, $branch, $user, $vendor] = $this->seedOnTimeDeliveryScenario();

        $scope = new SupplierPerformanceScope(
            companyId: $company->id,
            branchId: $branch->id,
            fromDate: '2026-06-01',
            toDate: '2026-06-30',
        );

        $queries = app(SupplierPerformanceQueries::class);
        $procScope = new \App\Support\Procurement\Reports\ProcurementReportScope(
            companyId: $company->id,
            branchId: $branch->id,
            fromDate: '2026-06-01',
            toDate: '2026-06-30',
            supplierId: $vendor->id,
        );

        $this->assertSame(100.0, app(\App\Support\Procurement\Reports\ProcurementReportQueries::class)->onTimeDeliveryPercent($procScope));

        $rows = $queries->supplierScorecardRows($scope);
        $row = $rows->firstWhere('supplier', $vendor->vendor_name);

        $this->assertNotNull($row, json_encode($rows->all()));
        $this->assertSame(1, $row['purchase_count']);
        $this->assertNotNull($row['overall_score']);
        $this->assertContains($row['grade'], ['A', 'B', 'C', 'D', 'F']);
        $this->assertSame(100.0, $row['on_time_percent']);
    }

    public function test_delivery_performance_tracks_variance(): void
    {
        [$company, $branch, $user, $vendor] = $this->seedLateDeliveryScenario();

        $scope = new SupplierPerformanceScope(
            companyId: $company->id,
            branchId: $branch->id,
            fromDate: '2026-06-01',
            toDate: '2026-06-30',
        );

        $rows = app(SupplierPerformanceQueries::class)->deliveryAnalysisRows($scope);
        $row = $rows->first();

        $this->assertNotNull($row);
        $this->assertSame($vendor->vendor_name, $row['supplier']);
        $this->assertSame('3', $row['days_late']);
        $this->assertSame('0', $row['days_early']);
    }

    public function test_quality_performance_uses_received_vs_ordered_quantities(): void
    {
        [$company, $branch, $user, $vendor] = $this->seedPartialReceiptScenario();

        $scope = new SupplierPerformanceScope(
            companyId: $company->id,
            branchId: $branch->id,
            fromDate: now()->startOfMonth()->toDateString(),
            toDate: now()->toDateString(),
        );

        $rows = app(SupplierPerformanceQueries::class)->qualityAnalysisRows($scope);
        $row = $rows->firstWhere('supplier', $vendor->vendor_name);

        $this->assertNotNull($row);
        $this->assertSame(8.0, $row['items_received']);
        $this->assertSame(2.0, $row['items_rejected']);
        $this->assertSame(20.0, $row['defect_rate']);
    }

    public function test_rankings_surface_highest_spend_supplier(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['procurement.performance.view']);
        $vendorA = Vendor::factory()->create(['company_id' => $company->id, 'vendor_name' => 'Alpha Supplies']);
        $vendorB = Vendor::factory()->create(['company_id' => $company->id, 'vendor_name' => 'Beta Supplies']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->createPurchaseOrder($company, $branch, $vendorA, $user, 'PO-SP-001', 25000);
        $this->createPurchaseOrder($company, $branch, $vendorB, $user, 'PO-SP-002', 90000);

        $scope = new SupplierPerformanceScope(
            companyId: $company->id,
            branchId: $branch->id,
            fromDate: now()->startOfMonth()->toDateString(),
            toDate: now()->toDateString(),
        );

        $rankings = app(SupplierPerformanceQueries::class)->rankings($scope);

        $this->assertSame('Beta Supplies', $rankings['highest_spend'][0]['supplier']);
        $this->assertSame(90000.0, $rankings['highest_spend'][0]['spend']);
    }

    public function test_export_requires_permission(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['procurement.performance.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.procurement.supplier-performance.export', ['format' => 'csv']))
            ->assertForbidden();
    }

    public function test_export_queues_job(): void
    {
        Queue::fake();

        [$company, $branch, $user] = $this->tenantUser([
            'procurement.performance.view',
            'procurement.performance.export',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.procurement.supplier-performance.export', [
                'format' => 'csv',
                'tab' => 'scorecard',
            ]))
            ->assertRedirect(route('admin.procurement.supplier-performance.index', [
                'format' => 'csv',
                'tab' => 'scorecard',
            ]));

        $this->assertDatabaseHas('commercial_report_exports', [
            'company_id' => $company->id,
            'module' => 'supplier_performance',
            'tab' => 'scorecard',
            'format' => 'csv',
        ]);

        Queue::assertPushed(ProcessCommercialReportExportJob::class);
    }

    public function test_supply_chain_workspace_links_supplier_performance(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $user->assignRole('Company Admin');

        $response = $this->actingAs($user)->get(route('admin.workspaces.supply-chain.section', ['section' => 'procurement']));

        $response->assertOk();
        $response->assertSee(route('admin.procurement.supplier-performance.index'), false);
        $response->assertSee(__('Supplier Performance'), false);
    }

    public function test_score_calculator_grade_thresholds(): void
    {
        $calculator = app(SupplierPerformanceScoreCalculator::class);

        $this->assertSame('A', $calculator->grade(92.0));
        $this->assertSame('B', $calculator->grade(85.0));
        $this->assertSame('C', $calculator->grade(74.0));
        $this->assertSame('D', $calculator->grade(63.0));
        $this->assertSame('F', $calculator->grade(42.0));
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User, 3: Vendor}
     */
    protected function seedOnTimeDeliveryScenario(): array
    {
        [$company, $branch, $user] = $this->tenantUser(['procurement.performance.view']);
        $vendor = Vendor::factory()->create(['company_id' => $company->id, 'vendor_name' => 'Reliable Vendor']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $order = $this->createPurchaseOrder($company, $branch, $vendor, $user, 'PO-ONTIME-001', 40000, '2026-06-02');
        $order->update(['expected_delivery_date' => '2026-06-04', 'status' => PurchaseOrderStatus::Received]);

        $warehouse = Warehouse::factory()->create(['company_id' => $company->id, 'branch_id' => $branch->id]);

        GoodsReceipt::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'purchase_order_id' => $order->id,
            'warehouse_id' => $warehouse->id,
            'receipt_number' => 'GR-ONTIME-001',
            'receipt_date' => '2026-06-04',
            'status' => GoodsReceiptStatus::Posted,
            'received_by' => $user->id,
        ]);

        return [$company, $branch, $user, $vendor];
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User, 3: Vendor}
     */
    protected function seedLateDeliveryScenario(): array
    {
        [$company, $branch, $user] = $this->tenantUser(['procurement.performance.view']);
        $vendor = Vendor::factory()->create(['company_id' => $company->id, 'vendor_name' => 'Late Vendor']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $order = $this->createPurchaseOrder($company, $branch, $vendor, $user, 'PO-LATE-001', 30000, '2026-06-02');
        $order->update([
            'expected_delivery_date' => '2026-06-02',
            'status' => PurchaseOrderStatus::Received,
        ]);

        $warehouse = Warehouse::factory()->create(['company_id' => $company->id, 'branch_id' => $branch->id]);

        GoodsReceipt::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'purchase_order_id' => $order->id,
            'warehouse_id' => $warehouse->id,
            'receipt_number' => 'GR-LATE-001',
            'receipt_date' => '2026-06-05',
            'status' => GoodsReceiptStatus::Posted,
            'received_by' => $user->id,
        ]);

        return [$company, $branch, $user, $vendor];
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User, 3: Vendor}
     */
    protected function seedPartialReceiptScenario(): array
    {
        [$company, $branch, $user] = $this->tenantUser(['procurement.performance.view']);
        $vendor = Vendor::factory()->create(['company_id' => $company->id, 'vendor_name' => 'Partial Vendor']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $order = $this->createPurchaseOrder($company, $branch, $vendor, $user, 'PO-PARTIAL-001', 20000);
        $poItem = $order->items()->firstOrFail();
        $poItem->update(['quantity' => 10, 'quantity_received' => 8]);
        $order->update(['status' => PurchaseOrderStatus::Received]);

        $warehouse = Warehouse::factory()->create(['company_id' => $company->id, 'branch_id' => $branch->id]);

        $receipt = GoodsReceipt::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'purchase_order_id' => $order->id,
            'warehouse_id' => $warehouse->id,
            'receipt_number' => 'GR-PARTIAL-001',
            'receipt_date' => now()->toDateString(),
            'status' => GoodsReceiptStatus::Posted,
            'received_by' => $user->id,
        ]);

        $receipt->items()->create([
            'purchase_order_item_id' => $poItem->id,
            'quantity_received' => 8,
            'unit_cost' => 50,
        ]);

        return [$company, $branch, $user, $vendor];
    }

    protected function createPurchaseOrder(
        Company $company,
        Branch $branch,
        Vendor $vendor,
        User $user,
        string $poNumber,
        float $total,
        ?string $orderDate = null,
    ): PurchaseOrder {
        $order = PurchaseOrder::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'vendor_id' => $vendor->id,
            'po_number' => $poNumber,
            'order_date' => $orderDate ?? now()->toDateString(),
            'status' => PurchaseOrderStatus::Sent,
            'subtotal' => $total,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => $total,
            'prepared_by' => $user->id,
        ]);

        $order->items()->create([
            'description' => 'Test item',
            'quantity' => 10,
            'unit_cost' => $total / 10,
            'line_total' => $total,
        ]);

        return $order;
    }

    /**
     * @param  list<string>  $permissions
     * @return array{0: Company, 1: Branch, 2: User}
     */
    protected function tenantUser(array $permissions): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);

        $role = Role::create(['name' => 'supplier-performance-tester-'.uniqid(), 'guard_name' => 'web']);
        $role->givePermissionTo($permissions);
        $user->assignRole($role);

        return [$company, $branch, $user];
    }
}
