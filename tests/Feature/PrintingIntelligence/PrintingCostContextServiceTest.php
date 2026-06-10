<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryMovement;
use App\Models\Inventory\Warehouse;
use App\Models\User;
use App\Services\PrintingIntelligence\PrintingCostContextService;
use App\Support\InventoryMovementService;
use Database\Seeders\GlAccountTypeSeeder;
use Database\Seeders\InventoryFoundationSeeder;
use Database\Seeders\JanaPrintsAccountingPeriodsSeeder;
use Database\Seeders\JanaPrintsChartOfAccountsSeeder;
use Database\Seeders\JanaPrintsPostingEngineSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrintingCostContextServiceTest extends TestCase
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
        $this->seed(InventoryFoundationSeeder::class);
    }

    public function test_returns_trusted_inventory_cost_without_mutation(): void
    {
        [$company, $branch, $item, $warehouse, $user] = $this->context();
        $beforeMovements = InventoryMovement::query()->count();

        $this->recordReceipt($company, $branch, $item, $warehouse, $user, 20, 12.5);

        $service = app(PrintingCostContextService::class);
        $cost = $service->getInventoryCost($item->id, $warehouse->id);

        $this->assertGreaterThan(0, $cost['standard_cost']);
        $this->assertSame('inventory_costing', $cost['source']);
        $this->assertSame($beforeMovements + 1, InventoryMovement::query()->count());
    }

    public function test_get_paper_cost_delegates_to_material_cost(): void
    {
        [$company, $branch, $item, $warehouse, $user] = $this->context();
        $this->recordReceipt($company, $branch, $item, $warehouse, $user, 10, 8);

        $service = app(PrintingCostContextService::class);
        $paper = $service->getPaperCost($item->id, $warehouse->id);
        $material = $service->getMaterialCost($item->id, $warehouse->id);

        $this->assertSame($material['standard_cost'], $paper['standard_cost']);
    }

    /**
     * @return array{0: Company, 1: Branch, 2: InventoryItem, 3: Warehouse, 4: User}
     */
    protected function context(): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
        ]);
        $warehouse = Warehouse::query()->where('company_id', $company->id)->firstOrFail();
        $category = \App\Models\Inventory\InventoryCategory::query()->where('company_id', $company->id)->first();
        $unit = \App\Models\Inventory\UnitOfMeasure::query()->where('company_id', $company->id)->first();
        $item = InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'inventory_category_id' => $category->id,
            'unit_of_measure_id' => $unit->id,
            'standard_cost' => 10,
        ]);

        return [$company, $branch, $item, $warehouse, $user];
    }

    protected function recordReceipt(Company $company, Branch $branch, InventoryItem $item, Warehouse $warehouse, User $user, float $qty, float $unitCost): void
    {
        InventoryMovementService::record([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'inventory_item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'movement_type' => \App\Enums\InventoryMovementType::Receipt,
            'quantity' => InventoryMovementService::receiptQuantity($qty),
            'unit_cost' => $unitCost,
            'reference_type' => InventoryItem::class,
            'reference_id' => $item->id,
            'movement_date' => now()->toDateString(),
            'created_by' => $user->id,
        ]);
    }
}
