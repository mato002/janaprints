<?php

namespace Tests\Feature\Inventory;

use App\Enums\DeadStockSuggestedAction;
use App\Enums\InventoryStockRole;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Inventory\InventoryItem;
use App\Models\User;
use App\Services\Inventory\DeadStockDetectionService;
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

class DeadStockDetectionServiceTest extends TestCase
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

    public function test_detects_stock_with_no_outbound_movement_for_sixty_plus_days(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->context();

        $this->recordReceipt($company, $branch, $user, $item, $warehouse, 25, now()->subDays(90));

        $rows = app(DeadStockDetectionService::class)->detect($company->id, ['branch_id' => $branch->id]);

        $this->assertTrue($rows->contains(fn ($row) => $row['item']->id === $item->id));
        $this->assertGreaterThanOrEqual(60, $rows->first()['days_inactive']);
    }

    public function test_ignores_zero_balance_items(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->context();

        $rows = app(DeadStockDetectionService::class)->detect($company->id, ['branch_id' => $branch->id]);

        $this->assertFalse($rows->contains(fn ($row) => $row['item']->id === $item->id));
    }

    public function test_prioritizes_finished_goods(): void
    {
        [$company, $branch, $user, $warehouse, $category, $unit] = $this->base();

        $raw = InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'inventory_category_id' => $category->id,
            'unit_of_measure_id' => $unit->id,
            'sku' => 'RAW-'.uniqid(),
            'stock_role' => InventoryStockRole::RawMaterial,
            'created_at' => now()->subMonths(6),
        ]);
        $fg = InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'inventory_category_id' => $category->id,
            'unit_of_measure_id' => $unit->id,
            'sku' => 'FG-'.uniqid(),
            'stock_role' => InventoryStockRole::FinishedGood,
            'standard_cost' => 5,
            'created_at' => now()->subMonths(6),
        ]);

        $this->recordReceipt($company, $branch, $user, $raw, $warehouse, 10, now()->subDays(90));
        $this->recordReceipt($company, $branch, $user, $fg, $warehouse, 10, now()->subDays(90));

        $rows = app(DeadStockDetectionService::class)->detect($company->id, ['branch_id' => $branch->id]);

        $this->assertGreaterThanOrEqual(2, $rows->count());
        $this->assertSame(InventoryStockRole::FinishedGood->value, $rows->first()['stock_role']);
    }

    public function test_returns_suggested_actions(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->context();
        $item->update(['stock_role' => InventoryStockRole::FinishedGood]);

        $this->recordReceipt($company, $branch, $user, $item, $warehouse, 15, now()->subDays(100));

        $row = app(DeadStockDetectionService::class)->detect($company->id, ['branch_id' => $branch->id])->first();

        $this->assertNotNull($row);
        $this->assertInstanceOf(DeadStockSuggestedAction::class, $row['suggested_action']);
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User, 3: InventoryItem, 4: \App\Models\Inventory\Warehouse}
     */
    protected function context(): array
    {
        [$company, $branch, $user, $warehouse, $category, $unit] = $this->base();

        $item = InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'inventory_category_id' => $category->id,
            'unit_of_measure_id' => $unit->id,
            'sku' => 'DS-'.uniqid(),
            'created_at' => now()->subMonths(6),
        ]);

        return [$company, $branch, $user, $item, $warehouse];
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User, 3: \App\Models\Inventory\Warehouse, 4: \App\Models\Inventory\InventoryCategory, 5: \App\Models\Inventory\UnitOfMeasure}
     */
    protected function base(): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $warehouse = \App\Models\Inventory\Warehouse::query()
            ->where('company_id', $company->id)
            ->where('branch_id', $branch->id)
            ->firstOrFail();
        $category = \App\Models\Inventory\InventoryCategory::query()->where('company_id', $company->id)->first();
        $unit = \App\Models\Inventory\UnitOfMeasure::query()->where('company_id', $company->id)->first();

        return [$company, $branch, $user, $warehouse, $category, $unit];
    }

    protected function recordReceipt(Company $company, Branch $branch, User $user, InventoryItem $item, $warehouse, float $qty, \Illuminate\Support\Carbon $date): void
    {
        InventoryMovementService::record([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'inventory_item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'movement_type' => \App\Enums\InventoryMovementType::Receipt,
            'quantity' => InventoryMovementService::receiptQuantity($qty),
            'unit_cost' => 10,
            'reference_type' => InventoryItem::class,
            'reference_id' => $item->id,
            'movement_date' => $date->toDateString(),
            'created_by' => $user->id,
        ]);
    }
}
