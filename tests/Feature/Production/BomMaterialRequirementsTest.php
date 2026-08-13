<?php

namespace Tests\Feature\Production;

use App\Enums\InventoryDocumentStatus;
use App\Enums\MaterialRequirementStatus;
use App\Enums\StockReceiptSource;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\ProductionMaterialConsumption;
use App\Models\Inventory\StockReceipt;
use App\Models\Inventory\Warehouse;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionMaterialRequirement;
use App\Models\Production\ProductBom;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderItem;
use App\Models\User;
use App\Support\InventoryStockService;
use App\Support\Production\JobCostingService;
use App\Support\Production\MaterialReadinessService;
use App\Support\Production\MaterialRequirementsService;
use App\Support\Production\ProductBomService;
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

class BomMaterialRequirementsTest extends TestCase
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

    public function test_bom_creation_with_waste_factor(): void
    {
        [$company, $branch, $user, $finished, $paper, $ink] = $this->materialContext([
            'production.bom.view', 'production.bom.create',
        ]);

        $service = app(ProductBomService::class);
        $bom = $service->create($company->id, $branch->id, $user->id, [
            'finished_item_id' => $finished->id,
            'name' => 'Test Business Cards BOM',
            'is_active' => true,
        ], [
            ['inventory_item_id' => $paper->id, 'quantity_per_unit' => 0.25, 'waste_factor_percent' => 5],
            ['inventory_item_id' => $ink->id, 'quantity_per_unit' => 0.02, 'waste_factor_percent' => 3],
        ]);

        $this->assertDatabaseHas('product_boms', [
            'finished_item_id' => $finished->id,
            'name' => 'Test Business Cards BOM',
        ]);
        $this->assertCount(2, $bom->lines);

        $calc = $service->requirementsForQuantity($bom, 1000);
        $paperReq = $calc->firstWhere(fn ($row) => $row['line']->inventory_item_id === $paper->id);

        $this->assertEqualsWithDelta(262.5, $paperReq['required_quantity'], 0.01);
    }

    public function test_bom_create_from_job_suggests_specification_materials(): void
    {
        [$company, $branch, $user, $finished, $paper, $ink] = $this->materialContext([
            'production.view', 'production.bom.view', 'production.bom.create',
        ]);

        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'inventory_item_id' => $finished->id,
            'created_by' => $user->id,
        ]);

        \App\Models\Production\ProductionSpecification::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $jobCard->customer_id,
            'production_job_card_id' => $jobCard->id,
            'paper_inventory_item_id' => $paper->id,
            'quantity' => 1000,
            'estimated_sheets' => 250,
            'waste_allowance_percent' => 5,
            'created_by' => $user->id,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.production.boms.create', [
                'finished_item_id' => $finished->id,
                'job_card_id' => $jobCard->getRouteKey(),
            ]))
            ->assertOk()
            ->assertDontSee('PROD-FLYER', false)
            ->assertSee($paper->sku, false)
            ->assertSee(__('Lines below are suggested from the job specification'), false);

        $suggested = app(ProductBomService::class)->suggestedLinesForJobCard($jobCard->fresh());
        $this->assertSame((string) $paper->id, $suggested[0]['inventory_item_id']);
        $this->assertEqualsWithDelta(0.25, (float) $suggested[0]['quantity_per_unit'], 0.0001);
        $this->assertContains((string) $ink->id, array_column($suggested, 'inventory_item_id'));
    }

    public function test_bom_index_requires_permission(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['production.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.production.boms.index', ['embedded' => 1]))
            ->assertForbidden();
    }

    public function test_preview_material_readiness_before_job_card_exists(): void
    {
        [$company, $branch, $user, $finished, $paper, $ink, $warehouse, $jobCard] = $this->jobWithBom();
        $salesOrder = $jobCard->salesOrder->fresh(['items']);
        $jobCard->delete();

        $preview = app(MaterialReadinessService::class)->previewForSalesOrder($salesOrder);

        $this->assertFalse($preview['ready']);
        $this->assertTrue($preview['has_requirements']);
        $this->assertGreaterThan(0, $preview['short_count']);
    }

    public function test_material_requirement_generation_from_sales_order(): void
    {
        [$company, $branch, $user, $finished, $paper, $ink, $warehouse, $jobCard] = $this->jobWithBom();

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.production.job-cards.materials.generate', $jobCard), [
                'warehouse_id' => $warehouse->id,
            ])
            ->assertRedirect();

        $requirements = ProductionMaterialRequirement::query()
            ->where('production_job_card_id', $jobCard->id)
            ->get();

        $this->assertGreaterThanOrEqual(2, $requirements->count());

        $paperRequirement = $requirements->firstWhere('inventory_item_id', $paper->id);
        $this->assertNotNull($paperRequirement);
        $this->assertEqualsWithDelta(131.25, (float) $paperRequirement->required_quantity, 0.01);
    }

    public function test_stock_availability_and_shortfall_detection(): void
    {
        [$company, $branch, $user, $finished, $paper, $ink, $warehouse, $jobCard] = $this->jobWithBom();

        $service = app(MaterialRequirementsService::class);
        $service->generate($jobCard, $warehouse->id, $user->id);

        $requirement = ProductionMaterialRequirement::query()
            ->where('production_job_card_id', $jobCard->id)
            ->where('inventory_item_id', $paper->id)
            ->firstOrFail();

        $rows = $service->panelRows($jobCard);
        $paperRow = $rows->firstWhere(fn ($row) => $row['requirement']->inventory_item_id === $paper->id);

        $this->assertEquals(0, $paperRow['available']);
        $this->assertGreaterThan(0, $paperRow['shortfall']);
        $this->assertSame(MaterialRequirementStatus::Shortfall, $requirement->fresh()->status);
    }

    public function test_reserve_and_consume_integration_with_job_costing(): void
    {
        [$company, $branch, $user, $finished, $paper, $ink, $warehouse, $jobCard] = $this->jobWithBom();

        $this->postReceipt($company, $branch, $user, $paper, $warehouse, 500);
        $this->postReceipt($company, $branch, $user, $ink, $warehouse, 50);

        $service = app(MaterialRequirementsService::class);
        $requirements = $service->generate($jobCard, $warehouse->id, $user->id);

        $paperRequirement = $requirements->firstWhere('inventory_item_id', $paper->id);
        $service->reserve($paperRequirement, $user->id);

        $this->assertSame(MaterialRequirementStatus::Reserved, $paperRequirement->fresh()->status);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.production.job-cards.materials.consume', [$jobCard, $paperRequirement]))
            ->assertRedirect();

        $paperRequirement->refresh();
        $this->assertGreaterThan(0, (float) $paperRequirement->consumed_quantity);
        $this->assertSame(MaterialRequirementStatus::Fulfilled, $paperRequirement->status);

        $costSheet = JobCostingService::buildOrRefresh($jobCard->fresh());
        $this->assertGreaterThan(0, (float) $costSheet->material_cost);
    }

    public function test_consume_all_remaining_records_consumption_for_stocked_lines(): void
    {
        [$company, $branch, $user, $finished, $paper, $ink, $warehouse, $jobCard] = $this->jobWithBom();

        $this->postReceipt($company, $branch, $user, $paper, $warehouse, 500);
        $this->postReceipt($company, $branch, $user, $ink, $warehouse, 50);

        $service = app(MaterialRequirementsService::class);
        $service->generate($jobCard, $warehouse->id, $user->id);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'materials']))
            ->assertOk()
            ->assertSee(__('Consume all remaining'), false)
            ->assertSee('id="materials-consume"', false);

        $this->actingAs($user)
            ->post(route('admin.production.job-cards.materials.consume-all', $jobCard))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame(
            0,
            ProductionMaterialRequirement::query()
                ->where('production_job_card_id', $jobCard->id)
                ->get()
                ->filter(fn ($row) => $row->remainingQuantity() > 0)
                ->count()
        );
        $this->assertGreaterThanOrEqual(
            2,
            ProductionMaterialConsumption::query()->where('production_job_card_id', $jobCard->id)->count()
        );
    }

    public function test_manual_consumption_is_capped_by_open_requirement(): void
    {
        [$company, $branch, $user, $finished, $paper, $ink, $warehouse, $jobCard] = $this->jobWithBom();

        $this->postReceipt($company, $branch, $user, $paper, $warehouse, 2000);

        $warehouse = Warehouse::query()
            ->where('company_id', $company->id)
            ->where('branch_id', $branch->id)
            ->physical()
            ->where('is_active', true)
            ->firstOrFail();

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $service = app(MaterialRequirementsService::class);
        $requirements = $service->generate($jobCard, $warehouse->id, $user->id);
        $paperRequirement = $requirements->firstWhere('inventory_item_id', $paper->id);
        $requiredQty = (float) $paperRequirement->required_quantity;

        $this->actingAs($user)
            ->post(route('admin.inventory.production.consume', $jobCard), [
                'inventory_item_id' => $paper->id,
                'warehouse_id' => $warehouse->id,
                'quantity' => $requiredQty / 2,
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $paperRequirement->refresh();
        $this->assertEqualsWithDelta($requiredQty / 2, (float) $paperRequirement->consumed_quantity, 0.01);
        $this->assertEqualsWithDelta($requiredQty / 2, $paperRequirement->remainingQuantity(), 0.01);

        $this->actingAs($user)
            ->post(route('admin.production.job-cards.materials.consume', [$jobCard, $paperRequirement]), [
                'quantity' => $requiredQty,
            ])
            ->assertSessionHasErrors('quantity');

        $this->assertSame(1, ProductionMaterialConsumption::query()->where('production_job_card_id', $jobCard->id)->count());
    }

    public function test_viewer_cannot_create_bom(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['production.bom.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.production.boms.create'))
            ->assertForbidden();
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

    /**
     * @param  list<string>  $permissions
     * @return array{0: Company, 1: Branch, 2: User, 3: InventoryItem, 4: InventoryItem, 5: InventoryItem}
     */
    protected function materialContext(array $permissions): array
    {
        [$company, $branch, $user] = $this->tenantUser($permissions);
        $category = \App\Models\Inventory\InventoryCategory::query()->where('company_id', $company->id)->first();
        $unit = \App\Models\Inventory\UnitOfMeasure::query()->where('company_id', $company->id)->first();

        $finished = InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'inventory_category_id' => $category->id,
            'unit_of_measure_id' => $unit->id,
            'sku' => 'FIN-'.uniqid(),
            'stock_role' => \App\Enums\InventoryStockRole::FinishedGood,
        ]);
        $paper = InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'inventory_category_id' => $category->id,
            'unit_of_measure_id' => $unit->id,
            'sku' => 'RAW-PAPER-'.uniqid(),
            'item_name' => 'Art Paper',
            'standard_cost' => 10,
            'stock_role' => \App\Enums\InventoryStockRole::RawMaterial,
        ]);
        $ink = InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'inventory_category_id' => $category->id,
            'unit_of_measure_id' => $unit->id,
            'sku' => 'RAW-INK-'.uniqid(),
            'item_name' => 'CMYK Ink',
            'standard_cost' => 20,
            'stock_role' => \App\Enums\InventoryStockRole::RawMaterial,
        ]);

        return [$company, $branch, $user, $finished, $paper, $ink];
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User, 3: InventoryItem, 4: InventoryItem, 5: InventoryItem, 6: Warehouse, 7: ProductionJobCard}
     */
    protected function jobWithBom(): array
    {
        [$company, $branch, $user, $finished, $paper, $ink] = $this->materialContext([
            'production.view',
            'production.bom.view',
            'production.bom.create',
            'production.materials.generate',
            'production.materials.reserve',
            'production.materials.consume',
            'inventory.view',
            'inventory.receive',
            'inventory.issue',
            'production.costing.view',
        ]);

        app(ProductBomService::class)->create($company->id, $branch->id, $user->id, [
            'finished_item_id' => $finished->id,
            'name' => 'Cards BOM',
            'is_active' => true,
        ], [
            ['inventory_item_id' => $paper->id, 'quantity_per_unit' => 0.25, 'waste_factor_percent' => 5],
            ['inventory_item_id' => $ink->id, 'quantity_per_unit' => 0.02, 'waste_factor_percent' => 3],
        ]);

        $warehouse = Warehouse::query()
            ->where('company_id', $company->id)
            ->where('branch_id', $branch->id)
            ->physical()
            ->where('is_active', true)
            ->firstOrFail();

        $salesOrder = SalesOrder::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);

        SalesOrderItem::query()->create([
            'sales_order_id' => $salesOrder->id,
            'inventory_item_id' => $finished->id,
            'item_name' => $finished->item_name,
            'quantity' => 500,
            'unit_price' => 100,
            'line_total' => 50000,
        ]);

        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'sales_order_id' => $salesOrder->id,
            'created_by' => $user->id,
        ]);

        return [$company, $branch, $user, $finished, $paper, $ink, $warehouse, $jobCard];
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
