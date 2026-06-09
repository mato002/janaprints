<?php

namespace Tests\Feature\Production;

use App\Enums\InventoryDocumentStatus;
use App\Enums\ProductionWasteType;
use App\Enums\StockReceiptSource;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Employee;
use App\Enums\InventoryMovementType;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\ProductionMaterialConsumption;
use App\Models\Inventory\StockReceipt;
use App\Models\Inventory\Warehouse;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionWastageRecord;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Services\Production\JobProductionControlService;
use App\Support\InventoryStockService;
use App\Support\Production\JobCostingService;
use App\Support\Production\ProductionWastageService;
use App\Support\Production\Reports\CostingReportQueries;
use App\Support\Production\Reports\CostingReportScope;
use App\Support\InventoryMovementService;
use App\Support\StockReceiptService;
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

class ProductionWastageTest extends TestCase
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
        $this->seed(PlatformConfigurationSeeder::class);
        $this->seed(InventoryFoundationSeeder::class);
    }

    public function test_waste_capture_records_movement_and_row(): void
    {
        [$company, $branch, $user, $paper, $warehouse, $jobCard] = $this->wastageContext();

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.production.job-cards.wastage.store', $jobCard), [
                'inventory_item_id' => $paper->id,
                'warehouse_id' => $warehouse->id,
                'quantity' => 5,
                'waste_type' => ProductionWasteType::PrintError->value,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('production_wastage_records', [
            'production_job_card_id' => $jobCard->id,
            'inventory_item_id' => $paper->id,
            'flow_type' => 'wasted',
            'waste_type' => ProductionWasteType::PrintError->value,
            'quantity' => 5,
        ]);

        $this->assertDatabaseHas('inventory_movements', [
            'inventory_item_id' => $paper->id,
            'warehouse_id' => $warehouse->id,
            'reference_type' => ProductionJobCard::class,
            'reference_id' => $jobCard->id,
        ]);
    }

    public function test_yield_calculation_with_consumed_wasted_and_returned(): void
    {
        [$company, $branch, $user, $paper, $warehouse, $jobCard] = $this->wastageContext();

        $this->recordConsumption($jobCard, $paper, $warehouse, 80, $user->id);
        app(ProductionWastageService::class)->recordWaste($jobCard, [
            'inventory_item_id' => $paper->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 15,
            'waste_type' => ProductionWasteType::SetupWaste->value,
        ], $user->id);
        app(ProductionWastageService::class)->recordReturn($jobCard, [
            'inventory_item_id' => $paper->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 5,
        ], $user->id);

        $metrics = app(ProductionWastageService::class)->jobMetrics($jobCard);

        $this->assertEqualsWithDelta(90, $metrics['material_issued'], 0.01);
        $this->assertEqualsWithDelta(80, $metrics['material_consumed'], 0.01);
        $this->assertEqualsWithDelta(15, $metrics['material_wasted'], 0.01);
        $this->assertEqualsWithDelta(5, $metrics['material_returned'], 0.01);
        $this->assertEqualsWithDelta(16.67, $metrics['waste_percent'], 0.1);
        $this->assertEqualsWithDelta(88.89, $metrics['yield_percent'], 0.1);
        $this->assertEqualsWithDelta(84.21, $metrics['material_efficiency_percent'], 0.1);
    }

    public function test_wastage_cost_feeds_job_costing_profitability(): void
    {
        [$company, $branch, $user, $paper, $warehouse, $jobCard] = $this->wastageContext();

        $this->recordConsumption($jobCard, $paper, $warehouse, 50, $user->id);
        app(ProductionWastageService::class)->recordWaste($jobCard, [
            'inventory_item_id' => $paper->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 10,
            'waste_type' => ProductionWasteType::Damage->value,
        ], $user->id);

        $sheet = JobCostingService::buildOrRefresh($jobCard);

        $this->assertEqualsWithDelta(500, (float) $sheet->material_cost, 0.01);
        $this->assertEqualsWithDelta(100, (float) $sheet->wastage_cost, 0.01);
        $this->assertGreaterThanOrEqual(600, (float) $sheet->total_cost);
        $this->assertDatabaseHas('job_cost_lines', [
            'job_cost_sheet_id' => $sheet->id,
            'cost_category' => 'wastage',
            'quantity' => 10,
        ]);
    }

    public function test_wastage_reports_aggregate_by_job_and_material(): void
    {
        [$company, $branch, $user, $paper, $warehouse, $jobCard] = $this->wastageContext();

        app(ProductionWastageService::class)->recordWaste($jobCard, [
            'inventory_item_id' => $paper->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 8,
            'waste_type' => ProductionWasteType::QualityReject->value,
        ], $user->id);
        JobCostingService::buildOrRefresh($jobCard);

        $scope = new CostingReportScope(
            companyId: $company->id,
            branchId: $branch->id,
            fromDate: now()->subDay()->toDateString(),
            toDate: now()->addDay()->toDateString(),
            customerId: null,
            productionType: null,
            jobCardId: null,
            search: '',
            tab: 'waste_by_material',
            page: 1,
        );

        $queries = app(CostingReportQueries::class);
        $materialRows = $queries->paginateWasteByMaterial($scope)->items();
        $this->assertNotEmpty($materialRows);
        $this->assertSame($paper->sku, $materialRows[0]['sku']);

        $jobScope = new CostingReportScope(
            companyId: $company->id,
            branchId: $branch->id,
            fromDate: now()->subDay()->toDateString(),
            toDate: now()->addDay()->toDateString(),
            customerId: null,
            productionType: null,
            jobCardId: null,
            search: '',
            tab: 'waste_by_job',
            page: 1,
        );
        $jobRows = $queries->paginateWasteByJob($jobScope)->items();
        $this->assertNotEmpty($jobRows);
    }

    public function test_wastage_permissions_enforced(): void
    {
        [$company, $branch, $user, $paper, $warehouse, $jobCard] = $this->wastageContext([
            'production.view',
            'production.wastage.view',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.production.job-cards.wastage.store', $jobCard), [
                'inventory_item_id' => $paper->id,
                'warehouse_id' => $warehouse->id,
                'quantity' => 2,
                'waste_type' => ProductionWasteType::OperatorError->value,
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('production_wastage_records', 0);
    }

    public function test_wastage_summary_activated_in_production_control(): void
    {
        [$company, $branch, $user, $paper, $warehouse, $jobCard] = $this->wastageContext();

        ProductionWastageRecord::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'production_job_card_id' => $jobCard->id,
            'inventory_item_id' => $paper->id,
            'warehouse_id' => $warehouse->id,
            'flow_type' => 'wasted',
            'waste_type' => ProductionWasteType::SetupWaste->value,
            'quantity' => 3,
            'unit_cost' => 10,
            'line_cost' => 30,
            'recorded_by' => $user->id,
            'recorded_at' => now(),
        ]);

        $summary = app(JobProductionControlService::class)->wastageSummary($jobCard);

        $this->assertTrue($summary['activated']);
        $this->assertNull($summary['placeholder']);
        $this->assertEqualsWithDelta(3, $summary['total_quantity'], 0.01);
    }

    /**
     * @param  list<string>  $permissions
     * @return array{0: Company, 1: Branch, 2: User, 3: InventoryItem, 4: Warehouse, 5: ProductionJobCard}
     */
    protected function wastageContext(array $permissions = []): array
    {
        $permissions = $permissions ?: [
            'production.view',
            'production.wastage.view',
            'production.wastage.record',
            'production.wastage.report',
            'inventory.view',
            'inventory.receive',
            'inventory.issue',
            'production.costing.view',
            'reports.costing.view',
        ];

        [$company, $branch, $user] = $this->tenantUser($permissions);
        $category = \App\Models\Inventory\InventoryCategory::query()->where('company_id', $company->id)->first();
        $unit = \App\Models\Inventory\UnitOfMeasure::query()->where('company_id', $company->id)->first();

        $paper = InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'inventory_category_id' => $category->id,
            'unit_of_measure_id' => $unit->id,
            'sku' => 'RAW-P-'.uniqid(),
            'standard_cost' => 10,
        ]);

        $warehouse = Warehouse::query()
            ->where('company_id', $company->id)
            ->where('branch_id', $branch->id)
            ->firstOrFail();

        $this->postReceipt($company, $branch, $user, $paper, $warehouse, 200);

        $salesOrder = SalesOrder::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);

        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'sales_order_id' => $salesOrder->id,
            'created_by' => $user->id,
        ]);

        return [$company, $branch, $user, $paper, $warehouse, $jobCard];
    }

    /**
     * @param  list<string>  $permissions
     * @return array{0: Company, 1: Branch, 2: User}
     */
    protected function tenantUser(array $permissions): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        Role::findByName('Production', 'web')->syncPermissions($permissions);
        $user->assignRole('Production');

        return [$company, $branch, $user];
    }

    protected function recordConsumption(
        ProductionJobCard $jobCard,
        InventoryItem $item,
        Warehouse $warehouse,
        float $quantity,
        int $userId,
        float $unitCost = 10,
    ): ProductionMaterialConsumption {
        $movement = InventoryMovementService::record([
            'company_id' => $jobCard->company_id,
            'branch_id' => $jobCard->branch_id,
            'inventory_item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'movement_type' => InventoryMovementType::ProductionConsumption,
            'quantity' => InventoryMovementService::issueQuantity($quantity),
            'unit_cost' => $unitCost,
            'reference_type' => ProductionJobCard::class,
            'reference_id' => $jobCard->id,
            'movement_date' => now()->toDateString(),
            'created_by' => $userId,
        ]);

        return ProductionMaterialConsumption::query()->create([
            'company_id' => $jobCard->company_id,
            'branch_id' => $jobCard->branch_id,
            'production_job_card_id' => $jobCard->id,
            'inventory_item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'inventory_movement_id' => $movement->id,
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'consumed_by' => $userId,
            'consumed_at' => now(),
        ]);
    }

    protected function postReceipt(Company $company, Branch $branch, User $user, InventoryItem $item, Warehouse $warehouse, float $qty): void
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
        InventoryStockService::forgetBalanceCache($item->id, $warehouse->id);
    }
}
