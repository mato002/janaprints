<?php

namespace Tests\Feature\Inventory;

use App\Enums\ApprovalRuleType;
use App\Enums\InventoryDocumentStatus;
use App\Enums\StockAdjustmentDirection;
use App\Enums\StockAdjustmentStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\StockAdjustment;
use App\Models\Inventory\Warehouse;
use App\Models\Platform\ApprovalRule;
use App\Models\User;
use App\Support\AccessControl\PermissionCatalog;
use App\Support\Platform\SettingsControlCenterPresenter;
use App\Support\Platform\SettingsRegistry;
use App\Support\Platform\SystemSettingsManager;
use App\Support\StockAdjustmentService;
use Database\Seeders\GlAccountTypeSeeder;
use Database\Seeders\InventoryFoundationSeeder;
use Database\Seeders\JanaPrintsAccountingPeriodsSeeder;
use Database\Seeders\JanaPrintsChartOfAccountsSeeder;
use Database\Seeders\JanaPrintsPostingEngineSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InventoryGovernanceTest extends TestCase
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
    }

    public function test_permission_catalog_exposes_inventory_control_permissions(): void
    {
        $catalog = app(PermissionCatalog::class);
        $inventory = collect($catalog->matrixSections())->firstWhere('module_key', 'inventory');
        $rowKeys = collect($inventory['rows'])->pluck('row_key')->all();

        $this->assertContains('inventory.stock_counts', $rowKeys);
        $this->assertContains('inventory.cycle_counts', $rowKeys);
        $this->assertContains('inventory.variances', $rowKeys);
        $this->assertContains('inventory.reconciliations', $rowKeys);
        $this->assertContains('inventory.variance_reasons', $rowKeys);

        foreach ([
            'inventory.count.view',
            'inventory.count.create',
            'inventory.count.edit',
            'inventory.count.submit',
            'inventory.count.approve',
            'inventory.count.post',
            'inventory.cycle.view',
            'inventory.cycle.manage',
            'inventory.variance.view',
            'inventory.reconcile.view',
            'inventory.reconcile.approve',
            'inventory.reconcile.post',
            'inventory.classification.manage',
            'inventory.variance-reasons.view',
            'inventory.variance-reasons.manage',
        ] as $permission) {
            $this->assertTrue(
                Permission::query()->where('name', $permission)->exists(),
                "Missing permission: {$permission}",
            );
        }
    }

    public function test_stock_count_index_requires_permission(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['inventory.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.inventory.stock-counts.index'))
            ->assertForbidden();
    }

    public function test_variance_export_requires_view_permission(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['inventory.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.inventory.variances.export', ['format' => 'csv']))
            ->assertForbidden();
    }

    public function test_high_value_adjustment_blocks_posting_before_approval(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->inventoryContext(['inventory.view', 'inventory.adjust', 'inventory.receive']);

        $adjustment = $this->makeAdjustment($company, $branch, $user, $warehouse, $item, quantity: 200, unitCost: 10);

        $this->expectException(ValidationException::class);
        StockAdjustmentService::post($adjustment, $user->id);
    }

    public function test_adjustment_approval_workflow_and_audit_history(): void
    {
        $this->markTestSkipped('Stock adjustment submit/approve workflow is not implemented on StockAdjustmentService.');

        [$company, $branch, $submitter, $approver, $item, $warehouse] = $this->approvalActors();

        $adjustment = $this->makeAdjustment($company, $branch, $submitter, $warehouse, $item, quantity: 200, unitCost: 10);

        StockAdjustmentService::submit($adjustment->fresh(), $submitter->id);
        $adjustment->refresh();

        $this->assertSame(StockAdjustmentStatus::Submitted, $adjustment->status);
        $this->assertSame($submitter->id, $adjustment->submitted_by);
        $this->assertNotNull($adjustment->submitted_at);

        StockAdjustmentService::approve($adjustment, $approver->id, 'Verified shrinkage');
        $adjustment->refresh();

        $this->assertSame(StockAdjustmentStatus::Approved, $adjustment->status);
        $this->assertSame($approver->id, $adjustment->approved_by);
        $this->assertNotNull($adjustment->approved_at);
        $this->assertSame('Verified shrinkage', $adjustment->approval_reason);

        StockAdjustmentService::post($adjustment, $approver->id);
        $adjustment->refresh();

        $this->assertSame(StockAdjustmentStatus::Posted, $adjustment->status);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($approver)
            ->get(route('admin.inventory.adjustments.show', $adjustment))
            ->assertOk()
            ->assertSee($submitter->name, false)
            ->assertSee($approver->name, false)
            ->assertSee('Verified shrinkage', false);
    }

    public function test_settings_inventory_cards_resolve_to_live_routes(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();

        $presenter = new SettingsControlCenterPresenter(
            app(SettingsRegistry::class),
            app(SystemSettingsManager::class),
        );

        $cards = collect($presenter->hub($company->id, null)['cards'])->keyBy('id');

        foreach ([
            'warehouses' => 'admin.inventory.warehouses.index',
            'inventory-categories' => 'admin.inventory.catalogue.categories.index',
            'units-of-measure' => 'admin.master-data.index',
        ] as $cardId => $routeName) {
            $card = $cards->get($cardId);

            $this->assertNotNull($card, "Missing card: {$cardId}");
            $this->assertFalse($card['comingSoon'], "Card {$cardId} should not be coming soon");
            $this->assertStringStartsWith(route($routeName), $card['href']);
        }
    }

    public function test_average_cost_workspace_links_to_valuation(): void
    {
        $admin = User::factory()->create([
            'company_id' => 1,
            'default_branch_id' => 1,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $admin->assignRole('Company Admin');

        session(['active_company_id' => 1, 'active_branch_id' => 1]);

        $this->actingAs($admin)
            ->get(route('admin.workspaces.supply-chain.section', ['section' => 'costing']))
            ->assertOk()
            ->assertSee(route('admin.inventory.valuation.index', absolute: false), false);
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
        Role::findByName('Storekeeper', 'web')->syncPermissions($permissions);
        $user->assignRole('Storekeeper');

        return [$company, $branch, $user];
    }

    /**
     * @param  list<string>  $permissions
     * @return array{0: Company, 1: Branch, 2: User, 3: InventoryItem, 4: Warehouse}
     */
    protected function inventoryContext(array $permissions): array
    {
        [$company, $branch, $user] = $this->tenantUser($permissions);
        $this->seed(InventoryFoundationSeeder::class);

        $warehouse = Warehouse::query()->where('company_id', $company->id)->where('branch_id', $branch->id)->first();
        $category = \App\Models\Inventory\InventoryCategory::query()->where('company_id', $company->id)->first();
        $unit = \App\Models\Inventory\UnitOfMeasure::query()->where('company_id', $company->id)->first();
        $item = InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'inventory_category_id' => $category->id,
            'unit_of_measure_id' => $unit->id,
            'sku' => 'GOV-'.uniqid(),
            'standard_cost' => 10,
        ]);

        return [$company, $branch, $user, $item, $warehouse];
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User, 3: User, 4: InventoryItem, 5: Warehouse}
     */
    protected function approvalActors(): array
    {
        [$company, $branch, $submitter, $item, $warehouse] = $this->inventoryContext([
            'inventory.view', 'inventory.adjust', 'inventory.receive',
        ]);

        $approver = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        Role::findByName('Storekeeper', 'web')->syncPermissions(['inventory.view', 'inventory.adjust']);
        $approver->assignRole('Storekeeper');

        ApprovalRule::query()->updateOrCreate(
            [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'rule_type' => ApprovalRuleType::StockAdjustmentApproval->value,
            ],
            [
                'is_enabled' => true,
                'min_approvers' => 1,
                'tiers' => [
                    ['threshold_amount' => 500, 'threshold_percent' => null, 'approver_role' => 'Storekeeper', 'approver_permission' => 'inventory.adjust'],
                ],
            ],
        );

        return [$company, $branch, $submitter, $approver, $item, $warehouse];
    }

    protected function makeAdjustment(
        Company $company,
        Branch $branch,
        User $user,
        Warehouse $warehouse,
        InventoryItem $item,
        float $quantity,
        float $unitCost,
    ): StockAdjustment {
        $adjustment = StockAdjustment::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'warehouse_id' => $warehouse->id,
            'adjustment_number' => 'SA-GOV-'.uniqid(),
            'adjustment_date' => now()->toDateString(),
            'status' => InventoryDocumentStatus::Draft,
            'reason' => 'Governance test adjustment',
            'adjusted_by' => $user->id,
        ]);

        $adjustment->items()->create([
            'inventory_item_id' => $item->id,
            'direction' => StockAdjustmentDirection::Increase,
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
        ]);

        return $adjustment->fresh(['items', 'warehouse']);
    }
}
