<?php

namespace Tests\Feature\Inventory;

use App\Enums\ReorderAlertStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryReorderAlert;
use App\Models\User;
use App\Services\Inventory\InventoryVelocityService;
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

class VelocityAlertIntegrationTest extends TestCase
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

    public function test_creates_velocity_stockout_risk_alert_with_metadata(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->context();

        $this->recordReceipt($company, $branch, $user, $item, $warehouse, 80);
        $this->recordIssue($company, $branch, $user, $item, $warehouse, 60);

        app(InventoryVelocityService::class)->generateSnapshots(
            companyId: $company->id,
            branchId: $branch->id,
            windows: 30,
        );

        $alert = InventoryReorderAlert::query()
            ->where('inventory_item_id', $item->id)
            ->where('warehouse_id', $warehouse->id)
            ->where('alert_type', config('inventory_intelligence.velocity_alert_type'))
            ->first();

        $this->assertNotNull($alert);
        $this->assertSame(ReorderAlertStatus::Open, $alert->status);
        $this->assertNotNull($alert->metadata['days_to_depletion'] ?? null);
        $this->assertNotNull($alert->metadata['average_daily_consumption'] ?? null);
        $this->assertNotNull($alert->metadata['risk_level'] ?? null);
    }

    public function test_does_not_duplicate_existing_reorder_alerts(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->context();

        InventoryReorderAlert::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'inventory_item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'alert_type' => config('inventory_intelligence.reorder_alert_type'),
            'current_quantity' => 8,
            'reorder_level' => 20,
            'status' => ReorderAlertStatus::Open,
            'is_resolved' => false,
            'alerted_at' => now(),
        ]);

        $this->recordReceipt($company, $branch, $user, $item, $warehouse, 80);
        $this->recordIssue($company, $branch, $user, $item, $warehouse, 60);

        app(InventoryVelocityService::class)->generateSnapshots(
            companyId: $company->id,
            branchId: $branch->id,
            windows: 30,
        );

        $reorderAlert = InventoryReorderAlert::query()
            ->where('inventory_item_id', $item->id)
            ->where('warehouse_id', $warehouse->id)
            ->where('alert_type', config('inventory_intelligence.reorder_alert_type'))
            ->first();

        $velocityAlert = InventoryReorderAlert::query()
            ->where('inventory_item_id', $item->id)
            ->where('warehouse_id', $warehouse->id)
            ->where('alert_type', config('inventory_intelligence.velocity_alert_type'))
            ->first();

        $this->assertNotNull($reorderAlert);
        $this->assertNotNull($velocityAlert);
        $this->assertNotSame($reorderAlert->id, $velocityAlert->id);
        $this->assertSame(2, InventoryReorderAlert::query()->where('inventory_item_id', $item->id)->where('warehouse_id', $warehouse->id)->count());
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User, 3: InventoryItem, 4: \App\Models\Inventory\Warehouse}
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
        $warehouse = \App\Models\Inventory\Warehouse::query()->where('company_id', $company->id)->firstOrFail();
        $category = \App\Models\Inventory\InventoryCategory::query()->where('company_id', $company->id)->first();
        $unit = \App\Models\Inventory\UnitOfMeasure::query()->where('company_id', $company->id)->first();
        $item = InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'inventory_category_id' => $category->id,
            'unit_of_measure_id' => $unit->id,
            'sku' => 'VAL-'.uniqid(),
            'created_at' => now()->subMonths(3),
        ]);

        InventoryReorderAlert::query()
            ->where('inventory_item_id', $item->id)
            ->delete();

        return [$company, $branch, $user, $item, $warehouse];
    }

    protected function recordReceipt(Company $company, Branch $branch, User $user, InventoryItem $item, $warehouse, float $qty): void
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
            'movement_date' => now()->subDays(15)->toDateString(),
            'created_by' => $user->id,
        ]);
    }

    protected function recordIssue(Company $company, Branch $branch, User $user, InventoryItem $item, $warehouse, float $qty): void
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
            'movement_date' => now()->subDay()->toDateString(),
            'created_by' => $user->id,
        ]);
    }
}
