<?php

namespace Tests\Feature\Inventory;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Inventory\InventoryItem;
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
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InventoryIntelligenceDashboardTest extends TestCase
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

    public function test_dashboard_overview_loads_with_counts(): void
    {
        [$company, $branch, $user] = $this->actingUser(['inventory.intelligence.view']);

        app(InventoryVelocityService::class)->generateSnapshots(
            companyId: $company->id,
            branchId: $branch->id,
            windows: 30,
        );

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->get(route('admin.inventory.intelligence.overview'))
            ->assertOk()
            ->assertSee(__('Inventory Intelligence'));
    }

    public function test_stockout_risk_page_loads(): void
    {
        [$company, $branch, $user] = $this->actingUser(['inventory.intelligence.view']);

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->get(route('admin.inventory.intelligence.stockout-risk'))
            ->assertOk()
            ->assertSee(__('Stockout Risk'));
    }

    public function test_dead_stock_page_loads(): void
    {
        [$company, $branch, $user] = $this->actingUser(['inventory.intelligence.view']);

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->get(route('admin.inventory.intelligence.dead-stock'))
            ->assertOk()
            ->assertSee(__('Dead Stock'));
    }

    public function test_permissions_enforced(): void
    {
        [$company, $branch, $user] = $this->actingUser(['inventory.view']);

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->get(route('admin.inventory.intelligence.overview'))
            ->assertForbidden();
    }

    /**
     * @param  list<string>  $permissions
     * @return array{0: Company, 1: Branch, 2: User}
     */
    protected function actingUser(array $permissions): array
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

        $this->seedSnapshotData($company, $branch, $user);

        return [$company, $branch, $user];
    }

    protected function seedSnapshotData(Company $company, Branch $branch, User $user): void
    {
        $warehouse = \App\Models\Inventory\Warehouse::query()->where('company_id', $company->id)->firstOrFail();
        $category = \App\Models\Inventory\InventoryCategory::query()->where('company_id', $company->id)->first();
        $unit = \App\Models\Inventory\UnitOfMeasure::query()->where('company_id', $company->id)->first();
        $item = InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'inventory_category_id' => $category->id,
            'unit_of_measure_id' => $unit->id,
            'sku' => 'DASH-'.uniqid(),
            'created_at' => now()->subMonths(2),
        ]);

        InventoryMovementService::record([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'inventory_item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'movement_type' => \App\Enums\InventoryMovementType::Receipt,
            'quantity' => InventoryMovementService::receiptQuantity(25),
            'unit_cost' => 10,
            'reference_type' => InventoryItem::class,
            'reference_id' => $item->id,
            'movement_date' => now()->subDays(5)->toDateString(),
            'created_by' => $user->id,
        ]);
    }
}
