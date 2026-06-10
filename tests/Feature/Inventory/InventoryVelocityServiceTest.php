<?php

namespace Tests\Feature\Inventory;

use App\Enums\InventoryRiskLevel;
use App\Enums\InventoryVelocityClass;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryMovement;
use App\Models\Inventory\Warehouse;
use App\Models\User;
use App\Services\Inventory\InventoryVelocityService;
use App\Support\InventoryMovementService;
use App\Support\InventoryStockService;
use Database\Seeders\GlAccountTypeSeeder;
use Database\Seeders\InventoryFoundationSeeder;
use Database\Seeders\JanaPrintsAccountingPeriodsSeeder;
use Database\Seeders\JanaPrintsChartOfAccountsSeeder;
use Database\Seeders\JanaPrintsPostingEngineSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InventoryVelocityServiceTest extends TestCase
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

    public function test_calculates_average_daily_consumption(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->context();

        $this->recordReceipt($company, $branch, $user, $item, $warehouse, 100);
        $this->recordIssue($company, $branch, $user, $item, $warehouse, 30, now()->subDays(5));
        $this->recordIssue($company, $branch, $user, $item, $warehouse, 30, now()->subDays(2));

        $service = app(InventoryVelocityService::class);
        $metrics = $service->calculateMetrics($item, $warehouse, now()->subDays(30), today(), 30);

        $this->assertEqualsWithDelta(2.0, (float) $metrics['average_daily_consumption'], 0.01);
        $this->assertEqualsWithDelta(14.0, (float) $metrics['average_weekly_consumption'], 0.01);
    }

    public function test_calculates_days_to_depletion(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->context();

        $this->recordReceipt($company, $branch, $user, $item, $warehouse, 100);
        $this->recordIssue($company, $branch, $user, $item, $warehouse, 30, now()->subDays(3));

        $service = app(InventoryVelocityService::class);
        $metrics = $service->calculateMetrics($item, $warehouse, now()->subDays(30), today(), 30);

        $this->assertNotNull($metrics['days_to_depletion']);
        $this->assertGreaterThan(0, (float) $metrics['days_to_depletion']);
    }

    public function test_classifies_critical_high_medium_low_risk(): void
    {
        $service = app(InventoryVelocityService::class);

        $critical = $this->metricsForRiskScenario(6, 60, 30);
        $this->assertSame(InventoryRiskLevel::Critical, $critical['risk_level']);

        $high = $this->metricsForRiskScenario(14, 60, 30);
        $this->assertSame(InventoryRiskLevel::High, $high['risk_level']);

        $medium = $this->metricsForRiskScenario(28, 60, 30);
        $this->assertSame(InventoryRiskLevel::Medium, $medium['risk_level']);

        $low = $this->metricsForRiskScenario(100, 30, 30);
        $this->assertSame(InventoryRiskLevel::Low, $low['risk_level']);
    }

    public function test_classifies_no_data_and_new_item(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->context();

        $service = app(InventoryVelocityService::class);
        $noData = $service->calculateMetrics($item, $warehouse, now()->subDays(30), today(), 30);
        $this->assertSame(InventoryVelocityClass::NoData, $noData['velocity_class']);

        $item->forceFill(['created_at' => now()])->save();
        $newItem = $service->calculateMetrics($item->fresh(), $warehouse, now()->subDays(30), today(), 30);
        $this->assertSame(InventoryVelocityClass::NewItem, $newItem['velocity_class']);
    }

    public function test_does_not_mutate_inventory_quantities(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->context();

        $this->recordReceipt($company, $branch, $user, $item, $warehouse, 50);
        $beforeMovements = InventoryMovement::query()->count();
        $beforeBalance = InventoryStockService::balance($item->id, $warehouse->id);

        app(InventoryVelocityService::class)->generateSnapshots(
            companyId: $company->id,
            branchId: $branch->id,
            windows: 30,
        );

        $this->assertSame($beforeMovements, InventoryMovement::query()->count());
        $this->assertEqualsWithDelta($beforeBalance, InventoryStockService::balance($item->id, $warehouse->id), 0.001);
    }

    /**
     * @return array<string, mixed>
     */
    protected function metricsForRiskScenario(float $balance, float $totalOut, int $window): array
    {
        [$company, $branch, $user, $item, $warehouse] = $this->context();
        $this->recordReceipt($company, $branch, $user, $item, $warehouse, $balance + $totalOut);
        $this->recordIssue($company, $branch, $user, $item, $warehouse, $totalOut, now()->subDay());

        return app(InventoryVelocityService::class)->calculateMetrics(
            $item,
            $warehouse,
            now()->subDays($window),
            today(),
            $window,
        );
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User, 3: InventoryItem, 4: Warehouse}
     */
    protected function context(): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        Role::findByName('Storekeeper', 'web')->syncPermissions(['inventory.view', 'inventory.receive']);

        $warehouse = Warehouse::query()->where('company_id', $company->id)->where('branch_id', $branch->id)->firstOrFail();
        $category = \App\Models\Inventory\InventoryCategory::query()->where('company_id', $company->id)->first();
        $unit = \App\Models\Inventory\UnitOfMeasure::query()->where('company_id', $company->id)->first();

        $item = InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'inventory_category_id' => $category->id,
            'unit_of_measure_id' => $unit->id,
            'sku' => 'VEL-'.uniqid(),
            'standard_cost' => 10,
            'created_at' => now()->subMonths(3),
        ]);

        return [$company, $branch, $user, $item, $warehouse];
    }

    protected function recordReceipt(Company $company, Branch $branch, User $user, InventoryItem $item, Warehouse $warehouse, float $qty): void
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
            'movement_date' => now()->subDays(20)->toDateString(),
            'created_by' => $user->id,
        ]);
    }

    protected function recordIssue(Company $company, Branch $branch, User $user, InventoryItem $item, Warehouse $warehouse, float $qty, \Illuminate\Support\Carbon $date): void
    {
        InventoryMovementService::record([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'inventory_item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'movement_type' => \App\Enums\InventoryMovementType::Issue,
            'quantity' => InventoryMovementService::issueQuantity($qty),
            'unit_cost' => 10,
            'reference_type' => InventoryItem::class,
            'reference_id' => $item->id,
            'movement_date' => $date->toDateString(),
            'created_by' => $user->id,
        ]);
    }
}
