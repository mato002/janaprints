<?php

namespace Tests\Feature\Production\Concerns;

use App\Enums\InventoryDocumentStatus;
use App\Enums\InventoryStockRole;
use App\Enums\ProductionJobCardStatus;
use App\Enums\StockReceiptSource;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\StockReceipt;
use App\Models\Inventory\Warehouse;
use App\Models\Production\ProductionJobCard;
use App\Models\User;
use App\Support\InventoryStockService;
use App\Support\ProductionMaterialConsumptionService;
use App\Support\StockReceiptService;
use Database\Seeders\GlAccountTypeSeeder;
use Database\Seeders\InventoryFoundationSeeder;
use Database\Seeders\InventoryVirtualWarehouseSeeder;
use Database\Seeders\JanaPrintsAccountingPeriodsSeeder;
use Database\Seeders\JanaPrintsChartOfAccountsSeeder;
use Database\Seeders\JanaPrintsPostingEngineSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Spatie\Permission\Models\Role;

trait InteractsWithProductionCompletion
{
    protected function seedProductionCompletionEnvironment(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(PlatformConfigurationSeeder::class);
        $this->seed(InventoryFoundationSeeder::class);
        $this->seed(InventoryVirtualWarehouseSeeder::class);
        $this->seed(GlAccountTypeSeeder::class);
        $this->seed(JanaPrintsChartOfAccountsSeeder::class);
        $this->seed(JanaPrintsAccountingPeriodsSeeder::class);
        $this->seed(JanaPrintsPostingEngineSeeder::class);
    }

    /**
     * @param  list<string>  $permissions
     */
    protected function completionUser(Company $company, Branch $branch, array $permissions, string $roleName = 'Production'): User
    {
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        Role::findByName($roleName, 'web')->syncPermissions($permissions);
        $user->assignRole($roleName);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        return $user;
    }

    /**
     * @return array{
     *     0: Company,
     *     1: Branch,
     *     2: User,
     *     3: InventoryItem,
     *     4: InventoryItem,
     *     5: Warehouse,
     *     6: ProductionJobCard
     * }
     */
    protected function readyJobForCompletion(
        array $permissions = [
            'production.view',
            'production.outputs.view',
            'production.outputs.post',
            'inventory.view',
            'inventory.receive',
            'inventory.issue',
        ],
        ProductionJobCardStatus $status = ProductionJobCardStatus::InProduction,
    ): array {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $user = $this->completionUser($company, $branch, $permissions);

        $category = \App\Models\Inventory\InventoryCategory::query()->where('company_id', $company->id)->firstOrFail();
        $unit = \App\Models\Inventory\UnitOfMeasure::query()->where('company_id', $company->id)->firstOrFail();

        $rawItem = InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'inventory_category_id' => $category->id,
            'unit_of_measure_id' => $unit->id,
            'sku' => 'RAW-'.uniqid(),
            'stock_role' => InventoryStockRole::RawMaterial,
        ]);

        $finishedItem = InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'inventory_category_id' => $category->id,
            'unit_of_measure_id' => $unit->id,
            'sku' => 'FG-'.uniqid(),
            'stock_role' => InventoryStockRole::FinishedGood,
        ]);

        $warehouse = Warehouse::query()
            ->where('company_id', $company->id)
            ->where('branch_id', $branch->id)
            ->where('is_virtual', false)
            ->firstOrFail();

        $this->postStockReceipt($company, $branch, $user, $rawItem, $warehouse, 100, 20);

        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'created_by' => $user->id,
            'status' => $status,
        ]);

        ProductionMaterialConsumptionService::consume($jobCard, $rawItem, $warehouse->id, 10, $user->id);

        return [$company, $branch, $user, $rawItem, $finishedItem, $warehouse, $jobCard];
    }

    protected function postStockReceipt(
        Company $company,
        Branch $branch,
        User $user,
        InventoryItem $item,
        Warehouse $warehouse,
        float $qty,
        float $unitCost,
    ): void {
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
            'unit_cost' => $unitCost,
        ]);
        StockReceiptService::post($receipt, $user->id);
        InventoryStockService::forgetBalanceCache($item->id, $warehouse->id);
    }
}
