<?php

namespace Tests\Feature\Inventory;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryVelocitySnapshot;
use App\Models\User;
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

class VelocitySnapshotCommandTest extends TestCase
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

    public function test_command_generates_snapshots(): void
    {
        $this->seedMovement();

        $this->artisan('inventory:velocity:snapshot', ['--company' => Company::query()->where('code', 'JANA')->value('id')])
            ->assertSuccessful();

        $this->assertGreaterThan(0, InventoryVelocitySnapshot::query()->count());
    }

    public function test_dry_run_does_not_persist(): void
    {
        $this->seedMovement();

        $this->artisan('inventory:velocity:snapshot', [
            '--company' => Company::query()->where('code', 'JANA')->value('id'),
            '--dry-run' => true,
        ])->assertSuccessful();

        $this->assertSame(0, InventoryVelocitySnapshot::query()->count());
    }

    public function test_all_windows_generates_seven_thirty_ninety(): void
    {
        $this->seedMovement();

        $this->artisan('inventory:velocity:snapshot', [
            '--company' => Company::query()->where('code', 'JANA')->value('id'),
            '--all-windows' => true,
        ])->assertSuccessful();

        $windows = InventoryVelocitySnapshot::query()->distinct()->pluck('movement_window_days')->sort()->values()->all();
        $this->assertSame([7, 30, 90], $windows);
    }

    public function test_idempotent_snapshot_update(): void
    {
        $this->seedMovement();
        $companyId = Company::query()->where('code', 'JANA')->value('id');

        $this->artisan('inventory:velocity:snapshot', ['--company' => $companyId])->assertSuccessful();
        $countAfterFirst = InventoryVelocitySnapshot::query()->count();

        $this->artisan('inventory:velocity:snapshot', ['--company' => $companyId])->assertSuccessful();
        $this->assertSame($countAfterFirst, InventoryVelocitySnapshot::query()->count());
    }

    protected function seedMovement(): void
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
            'sku' => 'CMD-'.uniqid(),
            'created_at' => now()->subMonths(2),
        ]);

        InventoryMovementService::record([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'inventory_item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'movement_type' => \App\Enums\InventoryMovementType::Receipt,
            'quantity' => InventoryMovementService::receiptQuantity(40),
            'unit_cost' => 10,
            'reference_type' => InventoryItem::class,
            'reference_id' => $item->id,
            'movement_date' => now()->subDays(10)->toDateString(),
            'created_by' => $user->id,
        ]);
    }
}
