<?php

namespace Tests\Feature\Inventory;

use App\Enums\InventoryMovementType;
use App\Jobs\Commercial\ProcessCommercialReportExportJob;
use App\Models\Branch;
use App\Models\CommercialReportExport;
use App\Models\Company;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryMovement;
use App\Models\Inventory\InventoryValuation;
use App\Models\Inventory\Warehouse;
use App\Models\User;
use App\Support\Inventory\InventoryCostingService;
use Database\Seeders\InventoryFoundationSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InventoryReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(PlatformConfigurationSeeder::class);
        $this->seed(InventoryFoundationSeeder::class);
    }

    public function test_inventory_reports_requires_permission(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['inventory.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.inventory.reports.index'))
            ->assertForbidden();
    }

    public function test_inventory_reports_index_loads(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['reports.inventory.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.inventory.reports.index'))
            ->assertOk()
            ->assertSee(__('Inventory Reports'), false)
            ->assertSee(__('Data Readiness'), false)
            ->assertSee(__('Stock On Hand'), false)
            ->assertSee(__('Low Stock'), false);
    }

    public function test_stock_on_hand_report_shows_balances(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->inventoryContext(['reports.inventory.view']);
        $this->receipt($company, $branch, $item, $warehouse, 25, 100, $user);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.inventory.reports.index', ['tab' => 'stock_on_hand']))
            ->assertOk()
            ->assertSee($item->item_name, false)
            ->assertSee($item->sku, false)
            ->assertSee('25.00', false);
    }

    public function test_low_stock_report_shows_shortfall(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->inventoryContext(['reports.inventory.view']);
        $item->update(['reorder_level' => 50]);
        $this->receipt($company, $branch, $item, $warehouse, 10, 50, $user);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.inventory.reports.index', ['tab' => 'low_stock']))
            ->assertOk()
            ->assertSee($item->item_name, false)
            ->assertSee('40.00', false);
    }

    public function test_out_of_stock_report_lists_zero_balances(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->inventoryContext(['reports.inventory.view']);

        InventoryValuation::query()->updateOrCreate(
            [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'inventory_item_id' => $item->id,
                'warehouse_id' => $warehouse->id,
            ],
            [
                'quantity_on_hand' => 0,
                'average_unit_cost' => 0,
            ],
        );

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.inventory.reports.index', ['tab' => 'out_of_stock']))
            ->assertOk()
            ->assertSee($item->item_name, false)
            ->assertSee($warehouse->name, false);
    }

    public function test_slow_moving_report_lists_idle_stock(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->inventoryContext(['reports.inventory.view']);
        $this->receipt($company, $branch, $item, $warehouse, 15, 80, $user, now()->subDays(120)->toDateString());

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.inventory.reports.index', [
                'tab' => 'slow_moving',
                'to_date' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee($item->item_name, false);
    }

    public function test_dead_stock_report_lists_stale_inventory(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->inventoryContext(['reports.inventory.view']);
        $this->receipt($company, $branch, $item, $warehouse, 8, 120, $user, now()->subDays(200)->toDateString());

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.inventory.reports.index', [
                'tab' => 'dead_stock',
                'to_date' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee($item->item_name, false)
            ->assertSee('8.00', false);
    }

    public function test_export_requires_permission(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['reports.inventory.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.inventory.reports.export', ['tab' => 'stock_on_hand']), ['format' => 'csv'])
            ->assertForbidden();
    }

    public function test_export_queues_job(): void
    {
        Queue::fake();

        [$company, $branch, $user] = $this->tenantUser([
            'reports.inventory.view',
            'reports.inventory.export',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.inventory.reports.export', ['tab' => 'stock_on_hand']), ['format' => 'csv'])
            ->assertRedirect();

        Queue::assertPushed(ProcessCommercialReportExportJob::class);

        $this->assertDatabaseHas('commercial_report_exports', [
            'company_id' => $company->id,
            'user_id' => $user->id,
            'module' => 'inventory',
            'tab' => 'stock_on_hand',
            'format' => 'csv',
        ]);
    }

    public function test_legacy_inventory_report_route_redirects(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['reports.inventory.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.reports.inventory'))
            ->assertRedirect(route('admin.inventory.reports.index'));
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
        Role::findByName('Viewer', 'web')->syncPermissions($permissions);
        $user->assignRole('Viewer');
        $item = InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'reorder_level' => 5,
        ]);
        $warehouse = Warehouse::query()->where('company_id', $company->id)->firstOrFail();

        return [$company, $branch, $user, $item, $warehouse];
    }

    protected function receipt(
        Company $company,
        Branch $branch,
        InventoryItem $item,
        Warehouse $warehouse,
        float $qty,
        float $cost,
        User $user,
        ?string $movementDate = null,
    ): void {
        $movement = InventoryMovement::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'inventory_item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'movement_type' => InventoryMovementType::Receipt,
            'quantity' => $qty,
            'unit_cost' => $cost,
            'movement_date' => $movementDate ?? now()->toDateString(),
            'created_by' => $user->id,
            'reference_type' => \App\Models\Inventory\StockReceipt::class,
            'reference_id' => 1,
        ]);

        InventoryCostingService::processReceipt($movement);
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
        Role::findByName('Viewer', 'web')->syncPermissions($permissions);
        $user->assignRole('Viewer');

        return [$company, $branch, $user];
    }
}
