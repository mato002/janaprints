<?php

namespace Tests\Feature\Inventory;

use App\Enums\InventoryDocumentStatus;
use App\Enums\StockIssueDestination;
use App\Enums\StockReceiptSource;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\StockIssue;
use App\Models\Inventory\StockReceipt;
use App\Models\Inventory\Warehouse;
use App\Models\Production\ProductionJobCard;
use App\Models\User;
use App\Support\Inventory\ProductionConsumptionGovernance;
use App\Support\InventoryStockService;
use App\Support\Production\JobCostingService;
use App\Support\ProductionMaterialConsumptionService;
use App\Support\StockIssueService;
use App\Support\StockReceiptService;
use Illuminate\Validation\ValidationException;
use Database\Seeders\GlAccountTypeSeeder;
use Database\Seeders\InventoryFoundationSeeder;
use Database\Seeders\JanaPrintsAccountingPeriodsSeeder;
use Database\Seeders\JanaPrintsChartOfAccountsSeeder;
use Database\Seeders\JanaPrintsPostingEngineSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductionConsumptionGovernanceTest extends TestCase
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

    public function test_job_card_consumption_updates_job_costing(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->inventoryContext([
            'inventory.view', 'inventory.receive', 'inventory.issue', 'production.view', 'production.costing.view',
        ]);

        $this->postReceipt($company, $branch, $user, $item, $warehouse, 50);

        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'created_by' => $user->id,
        ]);

        ProductionMaterialConsumptionService::consume($jobCard, $item, $warehouse->id, 5, $user->id);

        $costSheet = JobCostingService::buildOrRefresh($jobCard->fresh());
        $this->assertGreaterThan(0, (float) $costSheet->material_cost);
        $this->assertEquals(45, InventoryStockService::balance($item->id, $warehouse->id));
    }

    public function test_generic_production_stock_issue_blocked_for_storekeeper(): void
    {
        [, , $user, $item, $warehouse] = $this->inventoryContext([
            'inventory.view', 'inventory.receive', 'inventory.issue',
        ]);

        $governance = app(ProductionConsumptionGovernance::class);

        $this->expectException(ValidationException::class);
        $governance->assertCanUseDestination(
            $user,
            StockIssueDestination::Production,
            $warehouse->id,
            null,
        );
    }

    public function test_warehouse_manager_override_allows_production_issue_with_reason(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->inventoryContext([
            'inventory.view', 'inventory.receive', 'inventory.issue', 'inventory.issue.production.override',
        ]);

        $this->assignWarehouseManager($warehouse, $user);
        $this->postReceipt($company, $branch, $user, $item, $warehouse, 10);

        app(ProductionConsumptionGovernance::class)->assertCanUseDestination(
            $user,
            StockIssueDestination::Production,
            $warehouse->id,
            'Emergency floor stock for breakdown job without card',
        );

        $issue = StockIssue::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'warehouse_id' => $warehouse->id,
            'issue_number' => 'SI-OVERRIDE-02',
            'destination' => StockIssueDestination::Production,
            'issue_date' => now()->toDateString(),
            'status' => InventoryDocumentStatus::Draft,
            'issued_by' => $user->id,
            'production_override_reason' => 'Emergency floor stock for breakdown job without card',
            'production_override_by' => $user->id,
            'production_override_at' => now(),
        ]);
        $issue->items()->create([
            'inventory_item_id' => $item->id,
            'quantity' => 2,
            'unit_cost' => 10,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.inventory.issues.post', $issue))
            ->assertRedirect();

        $this->assertEquals(8, InventoryStockService::balance($item->id, $warehouse->id));
    }

    public function test_override_without_reason_is_rejected(): void
    {
        [, , $user, , $warehouse] = $this->inventoryContext([
            'inventory.view', 'inventory.receive', 'inventory.issue', 'inventory.issue.production.override',
        ]);

        $this->assignWarehouseManager($warehouse, $user);

        $this->expectException(ValidationException::class);
        app(ProductionConsumptionGovernance::class)->assertCanUseDestination(
            $user,
            StockIssueDestination::Production,
            $warehouse->id,
            null,
        );
    }

    public function test_override_permission_without_manager_assignment_is_blocked(): void
    {
        [, , $user, , $warehouse] = $this->inventoryContext([
            'inventory.view', 'inventory.receive', 'inventory.issue', 'inventory.issue.production.override',
        ]);

        $this->expectException(ValidationException::class);
        app(ProductionConsumptionGovernance::class)->assertCanUseDestination(
            $user,
            StockIssueDestination::Production,
            $warehouse->id,
            'Should still fail without manager assignment',
        );
    }

    public function test_production_stock_issue_does_not_update_job_costing(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->inventoryContext([
            'inventory.view', 'inventory.receive', 'inventory.issue', 'inventory.issue.production.override', 'production.costing.view',
        ]);

        $this->assignWarehouseManager($warehouse, $user);
        $this->postReceipt($company, $branch, $user, $item, $warehouse, 20);

        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'created_by' => $user->id,
        ]);

        $issue = StockIssue::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'warehouse_id' => $warehouse->id,
            'issue_number' => 'SI-OVERRIDE-01',
            'destination' => StockIssueDestination::Production,
            'issue_date' => now()->toDateString(),
            'status' => InventoryDocumentStatus::Draft,
            'issued_by' => $user->id,
            'production_override_reason' => 'Breakdown stock',
            'production_override_by' => $user->id,
            'production_override_at' => now(),
        ]);
        $issue->items()->create([
            'inventory_item_id' => $item->id,
            'quantity' => 3,
            'unit_cost' => 10,
        ]);

        StockIssueService::post($issue, $user->id);

        $costSheet = JobCostingService::buildOrRefresh($jobCard->fresh());
        $this->assertEquals(0, (float) $costSheet->material_cost);
    }

    public function test_override_creates_audit_trail(): void
    {
        [$company, $branch, $user, , $warehouse] = $this->inventoryContext([
            'inventory.view', 'inventory.receive', 'inventory.issue', 'inventory.issue.production.override',
        ]);

        $this->assignWarehouseManager($warehouse, $user);

        $issue = StockIssue::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'warehouse_id' => $warehouse->id,
            'issue_number' => 'SI-AUDIT-01',
            'destination' => StockIssueDestination::Production,
            'issue_date' => now()->toDateString(),
            'status' => InventoryDocumentStatus::Draft,
            'issued_by' => $user->id,
        ]);

        $this->actingAs($user);
        app(ProductionConsumptionGovernance::class)->recordOverride($issue, $user, 'Audited emergency issue');

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'production_stock_issue_override',
            'model_type' => StockIssue::class,
            'model_id' => $issue->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_create_form_hides_production_destination_for_standard_users(): void
    {
        [$company, $branch, $user, , $warehouse] = $this->inventoryContext([
            'inventory.view', 'inventory.issue',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.inventory.issues.create', ['warehouse_id' => $warehouse->id]))
            ->assertOk()
            ->assertSee(__('Production consumption governance'), false)
            ->assertDontSee('value="production"', false);
    }

    /**
     * @param  list<string>  $permissions
     * @return array{0: Company, 1: Branch, 2: User, 3: InventoryItem, 4: Warehouse}
     */
    protected function inventoryContext(array $permissions): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        Role::findByName('Storekeeper', 'web')->syncPermissions($permissions);
        $user->assignRole('Storekeeper');

        $warehouse = Warehouse::query()->where('company_id', $company->id)->where('branch_id', $branch->id)->first();
        $category = \App\Models\Inventory\InventoryCategory::query()->where('company_id', $company->id)->first();
        $unit = \App\Models\Inventory\UnitOfMeasure::query()->where('company_id', $company->id)->first();

        $item = InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'inventory_category_id' => $category->id,
            'unit_of_measure_id' => $unit->id,
            'sku' => 'GOV-'.uniqid(),
        ]);

        return [$company, $branch, $user, $item, $warehouse];
    }

    protected function assignWarehouseManager(Warehouse $warehouse, User $user): void
    {
        DB::table('user_warehouse')->updateOrInsert(
            ['warehouse_id' => $warehouse->id, 'user_id' => $user->id],
            ['is_manager' => true, 'created_at' => now(), 'updated_at' => now()],
        );
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
