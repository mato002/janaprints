<?php

namespace Tests\Feature\Production;

use App\Enums\InventoryMovementType;
use App\Enums\InventoryStockRole;
use App\Enums\MaterialRequirementStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Inventory\InventoryItem;
use App\Enums\InventoryDocumentStatus;
use App\Enums\StockReceiptSource;
use App\Models\Inventory\StockReceipt;
use App\Models\Inventory\Warehouse;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionMaterialIssue;
use App\Models\Production\ProductionMaterialRequirement;
use App\Models\User;
use App\Support\Production\MaterialQuantityFormulaService;
use App\Support\Production\MaterialRequirementsService;
use App\Support\Production\ProductBomService;
use App\Support\Production\ProductionMaterialCostVisibilityService;
use App\Support\Production\ProductionMaterialIssueService;
use App\Support\InventoryStockService;
use App\Support\StockReceiptService;
use Database\Seeders\InventoryFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductionMaterialC5Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(InventoryFoundationSeeder::class);
    }

    public function test_formula_evaluates_job_quantity_multiplier(): void
    {
        $service = app(MaterialQuantityFormulaService::class);

        $this->assertEqualsWithDelta(10.0, $service->evaluate('JOB_QTY * 0.01', 1000, 1), 0.001);
        $this->assertEqualsWithDelta(500.0, $service->evaluate('JOB_QTY / 2', 1000, 1), 0.001);
        $this->assertEqualsWithDelta(25.0, $service->evaluate(null, 100, 0.25), 0.001);
    }

    public function test_requirement_snapshot_on_job_card_bootstrap(): void
    {
        [$company, $branch, $user, $finished, $paper, $warehouse, $jobCard] = $this->materialJobContext();

        $requirements = ProductionMaterialRequirement::query()
            ->where('production_job_card_id', $jobCard->id)
            ->get();

        $this->assertGreaterThanOrEqual(1, $requirements->count());
        $this->assertDatabaseHas('production_material_requirements', [
            'production_job_card_id' => $jobCard->id,
            'inventory_item_id' => $paper->id,
        ]);
    }

    public function test_material_availability_and_shortfall_display(): void
    {
        [$company, $branch, $user, , $paper, $warehouse, $jobCard] = $this->materialJobContext();

        $service = app(MaterialRequirementsService::class);
        $rows = $service->panelRows($jobCard);
        $paperRow = $rows->first(fn ($r) => $r['requirement']->inventory_item_id === $paper->id);

        $this->assertNotNull($paperRow);
        $this->assertArrayHasKey('required', $paperRow);
        $this->assertArrayHasKey('available', $paperRow);
        $this->assertArrayHasKey('shortfall', $paperRow);
        $this->assertGreaterThan(0, $paperRow['shortfall']);
    }

    public function test_issue_materials_creates_production_issue_movement(): void
    {
        [$company, $branch, $user, , $paper, $warehouse, $jobCard] = $this->materialJobContext();

        $this->seedStock($company->id, $branch->id, $paper->id, $warehouse->id, 500, $user->id);

        $requirement = ProductionMaterialRequirement::query()
            ->where('production_job_card_id', $jobCard->id)
            ->where('inventory_item_id', $paper->id)
            ->firstOrFail();

        app(ProductionMaterialIssueService::class)->issueFromRequirement($requirement, $user->id, 50);

        $this->assertDatabaseHas('production_material_issues', [
            'production_job_card_id' => $jobCard->id,
            'inventory_item_id' => $paper->id,
            'quantity' => 50,
        ]);

        $this->assertDatabaseHas('inventory_movements', [
            'inventory_item_id' => $paper->id,
            'movement_type' => InventoryMovementType::ProductionIssue->value,
        ]);

        $this->assertEqualsWithDelta(50.0, (float) $requirement->fresh()->issued_quantity, 0.01);
    }

    public function test_cost_visibility_summary(): void
    {
        [$company, $branch, $user, , $paper, $warehouse, $jobCard] = $this->materialJobContext();

        $summary = app(ProductionMaterialCostVisibilityService::class)->summary($jobCard);

        $this->assertArrayHasKey('estimated_material_cost', $summary);
        $this->assertArrayHasKey('issued_material_cost', $summary);
        $this->assertArrayHasKey('consumed_material_cost', $summary);
        $this->assertArrayHasKey('waste_cost', $summary);
    }

    public function test_tenant_isolation_on_material_requirements(): void
    {
        $companyA = Company::factory()->create();
        $branchA = Branch::factory()->create(['company_id' => $companyA->id]);
        $companyB = Company::factory()->create();
        $branchB = Branch::factory()->create(['company_id' => $companyB->id]);

        ProductionMaterialRequirement::query()->create([
            'company_id' => $companyA->id,
            'branch_id' => $branchA->id,
            'production_job_card_id' => ProductionJobCard::factory()->create([
                'company_id' => $companyA->id,
                'branch_id' => $branchA->id,
            ])->id,
            'inventory_item_id' => InventoryItem::factory()->create([
                'company_id' => $companyA->id,
                'branch_id' => $branchA->id,
            ])->id,
            'warehouse_id' => Warehouse::factory()->create([
                'company_id' => $companyA->id,
                'branch_id' => $branchA->id,
            ])->id,
            'required_quantity' => 10,
            'status' => MaterialRequirementStatus::Planned,
        ]);

        app()->instance(\App\Support\TenantContext::class, new \App\Support\TenantContext($companyB, $branchB));

        $this->assertEquals(0, ProductionMaterialRequirement::query()->forTenant()->count());
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User, 3: InventoryItem, 4: InventoryItem, 5: Warehouse, 6: ProductionJobCard}
     */
    protected function materialJobContext(): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $user = $this->productionUser($company, $branch);

        $finished = InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'stock_role' => InventoryStockRole::FinishedGood,
            'is_active' => true,
        ]);
        $paper = InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'stock_role' => InventoryStockRole::RawMaterial,
            'is_active' => true,
        ]);

        $warehouse = Warehouse::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);

        app(ProductBomService::class)->create($company->id, $branch->id, $user->id, [
            'finished_item_id' => $finished->id,
            'name' => 'Flyer BOM',
            'is_active' => true,
        ], [
            ['inventory_item_id' => $paper->id, 'quantity_per_unit' => 0.015, 'quantity_formula' => 'JOB_QTY * 0.01', 'waste_factor_percent' => 0],
        ]);

        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'inventory_item_id' => $finished->id,
            'created_by' => $user->id,
        ]);

        app(MaterialRequirementsService::class)->snapshotForJobCard($jobCard, $user->id);

        return [$company, $branch, $user, $finished, $paper, $warehouse, $jobCard->fresh()];
    }

    protected function seedStock(int $companyId, int $branchId, int $itemId, int $warehouseId, float $qty, int $userId): void
    {
        $receipt = StockReceipt::query()->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'warehouse_id' => $warehouseId,
            'receipt_number' => 'SR-'.uniqid(),
            'source' => StockReceiptSource::Purchase,
            'receipt_date' => now()->toDateString(),
            'status' => InventoryDocumentStatus::Draft,
            'received_by' => $userId,
        ]);
        $receipt->items()->create([
            'inventory_item_id' => $itemId,
            'quantity' => $qty,
            'unit_cost' => 10,
        ]);
        StockReceiptService::post($receipt, $userId);
        InventoryStockService::forgetBalanceCache($itemId, $warehouseId);
    }

    protected function productionUser(Company $company, Branch $branch): User
    {
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $role = Role::findByName('Production', 'web');
        $role->syncPermissions([
            'production.view', 'production.materials.generate', 'production.materials.issue',
            'production.materials.consume', 'inventory.issue',
        ]);
        $user->assignRole('Production');

        return $user;
    }
}
