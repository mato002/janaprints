<?php

namespace Tests\Feature\Inventory;

use App\Enums\InventoryDocumentStatus;
use App\Enums\PostingEventCode;
use App\Enums\StockIssueDestination;
use App\Models\Accounting\Journal;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\StockIssue;
use App\Models\Inventory\Warehouse;
use App\Models\Production\ProductionJobCard;
use App\Models\User;
use App\Support\Accounting\InventoryAccountingPostingService;
use App\Support\ProductionMaterialConsumptionService;
use App\Support\StockIssueService;
use App\Support\StockReceiptService;
use App\Enums\StockReceiptSource;
use App\Models\Inventory\StockReceipt;
use Database\Seeders\GlAccountTypeSeeder;
use Database\Seeders\InventoryFoundationSeeder;
use Database\Seeders\JanaPrintsAccountingPeriodsSeeder;
use Database\Seeders\JanaPrintsChartOfAccountsSeeder;
use Database\Seeders\JanaPrintsPostingEngineSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WipPostingExclusivityTest extends TestCase
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

    public function test_production_stock_issue_does_not_create_wip_journal(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->inventoryContext([
            'inventory.view', 'inventory.receive', 'inventory.issue', 'inventory.issue.production.override',
        ]);
        $this->postReceipt($company, $branch, $user, $item, $warehouse, 10);
        $this->assignWarehouseManager($warehouse, $user);

        $issue = $this->productionStockIssue($company, $branch, $user, $warehouse, $item, 'SI-WIP-EX-01', 2);
        StockIssueService::post($issue, $user->id);

        $this->assertNull($issue->fresh()->posted_journal_id);
        $this->assertSame(0, Journal::query()
            ->where('source_type', 'stock_issue')
            ->where('source_id', $issue->id)
            ->count());
    }

    public function test_material_consumption_creates_wip_journal(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->inventoryContext([
            'inventory.view', 'inventory.receive', 'inventory.issue', 'production.view',
        ]);
        $this->postReceipt($company, $branch, $user, $item, $warehouse, 20);

        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'created_by' => $user->id,
        ]);

        $consumption = ProductionMaterialConsumptionService::consume(
            $jobCard,
            $item,
            $warehouse->id,
            4,
            $user->id,
        );

        $this->assertDatabaseHas('journals', [
            'source_type' => 'production_material_consumption',
            'source_id' => $consumption->id,
            'posting_event' => PostingEventCode::ProductionMaterialConsumptionPosted->value,
        ]);
    }

    public function test_no_duplicate_wip_accounting_from_stock_issue_and_consumption(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->inventoryContext([
            'inventory.view', 'inventory.receive', 'inventory.issue', 'inventory.issue.production.override', 'production.view',
        ]);
        $this->postReceipt($company, $branch, $user, $item, $warehouse, 20);
        $this->assignWarehouseManager($warehouse, $user);

        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'created_by' => $user->id,
        ]);

        $issue = $this->productionStockIssue($company, $branch, $user, $warehouse, $item, 'SI-WIP-EX-02', 2);
        StockIssueService::post($issue, $user->id);

        $consumption = ProductionMaterialConsumptionService::consume($jobCard, $item, $warehouse->id, 3, $user->id);

        $this->assertSame(1, Journal::query()
            ->where('posting_event', PostingEventCode::ProductionMaterialConsumptionPosted->value)
            ->where('source_type', 'production_material_consumption')
            ->where('source_id', $consumption->id)
            ->count());

        $this->assertSame(0, Journal::query()
            ->where('posting_event', PostingEventCode::InventoryIssuePosted->value)
            ->where('source_type', 'stock_issue')
            ->where('source_id', $issue->id)
            ->count());
    }

    public function test_post_stock_issue_returns_null_for_production_destination(): void
    {
        [$company, $branch, $user, $item, $warehouse] = $this->inventoryContext([
            'inventory.view', 'inventory.receive', 'inventory.issue', 'inventory.issue.production.override',
        ]);
        $this->postReceipt($company, $branch, $user, $item, $warehouse, 5);
        $this->assignWarehouseManager($warehouse, $user);

        $issue = $this->productionStockIssue($company, $branch, $user, $warehouse, $item, 'SI-WIP-EX-03', 1);

        $journal = app(InventoryAccountingPostingService::class)->postStockIssue($issue, $user->id);

        $this->assertNull($journal);
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
        Role::findByName('Storekeeper', 'web')->syncPermissions($permissions);
        $user->assignRole('Storekeeper');

        $warehouse = Warehouse::query()->where('company_id', $company->id)->where('branch_id', $branch->id)->firstOrFail();
        $category = \App\Models\Inventory\InventoryCategory::query()->where('company_id', $company->id)->firstOrFail();
        $unit = \App\Models\Inventory\UnitOfMeasure::query()->where('company_id', $company->id)->firstOrFail();

        $item = InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'inventory_category_id' => $category->id,
            'unit_of_measure_id' => $unit->id,
            'sku' => 'WIPX-'.uniqid(),
        ]);

        return [$company, $branch, $user, $item, $warehouse];
    }

    protected function assignWarehouseManager(Warehouse $warehouse, User $user): void
    {
        DB::table('user_warehouse')->updateOrInsert(
            ['user_id' => $user->id, 'warehouse_id' => $warehouse->id],
            ['is_manager' => true, 'created_at' => now(), 'updated_at' => now()],
        );
    }

    protected function postReceipt(Company $company, Branch $branch, User $user, InventoryItem $item, Warehouse $warehouse, float $qty): void
    {
        $receipt = StockReceipt::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'warehouse_id' => $warehouse->id,
            'receipt_number' => 'SR-WIPX-'.uniqid(),
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
    }

    protected function productionStockIssue(
        Company $company,
        Branch $branch,
        User $user,
        Warehouse $warehouse,
        InventoryItem $item,
        string $number,
        float $qty,
    ): StockIssue {
        $issue = StockIssue::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'warehouse_id' => $warehouse->id,
            'issue_number' => $number,
            'destination' => StockIssueDestination::Production,
            'issue_date' => now()->toDateString(),
            'status' => InventoryDocumentStatus::Draft,
            'issued_by' => $user->id,
            'production_override_reason' => 'Floor allocation',
            'production_override_by' => $user->id,
            'production_override_at' => now(),
        ]);
        $issue->items()->create([
            'inventory_item_id' => $item->id,
            'quantity' => $qty,
            'unit_cost' => 10,
        ]);

        return $issue;
    }
}
