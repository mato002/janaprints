<?php

namespace Tests\Feature\PrintingIntelligence\Concerns;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\Warehouse;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\SalesOrder;
use App\Support\Production\JobCostingService;
use App\Support\ProductionMaterialConsumptionService;
use Database\Seeders\GlAccountTypeSeeder;
use Database\Seeders\InventoryFoundationSeeder;
use Database\Seeders\JanaPrintsAccountingPeriodsSeeder;
use Database\Seeders\JanaPrintsChartOfAccountsSeeder;
use Database\Seeders\JanaPrintsPostingEngineSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;

trait BuildsProductionCostFixtures
{
    protected function seedProductionCostStack(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(PlatformConfigurationSeeder::class);
        $this->seed(InventoryFoundationSeeder::class);
        $this->seed(GlAccountTypeSeeder::class);
        $this->seed(JanaPrintsChartOfAccountsSeeder::class);
        $this->seed(JanaPrintsAccountingPeriodsSeeder::class);
        $this->seed(JanaPrintsPostingEngineSeeder::class);
    }

    /**
     * @return array{0: Company, 1: Branch, 2: ProductionJobCard, 3: \App\Models\Production\JobCostSheet}
     */
    protected function jobWithCostSheet(float $consumptionQty = 4, float $unitCost = 10, float $orderTotal = 8000): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $warehouse = Warehouse::query()->where('company_id', $company->id)->firstOrFail();
        $item = InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);
        $user = \App\Models\User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
        ]);

        $this->seedStock($company, $branch, $item, $warehouse, $user->id);

        $order = SalesOrder::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'total_amount' => $orderTotal,
        ]);

        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'sales_order_id' => $order->id,
        ]);

        ProductionMaterialConsumptionService::consume($jobCard, $item, $warehouse->id, $consumptionQty, $user->id);
        $sheet = JobCostingService::buildOrRefresh($jobCard->fresh());

        return [$company, $branch, $jobCard, $sheet];
    }

    protected function seedStock(Company $company, Branch $branch, InventoryItem $item, Warehouse $warehouse, int $userId): void
    {
        $receipt = \App\Models\Inventory\StockReceipt::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'warehouse_id' => $warehouse->id,
            'receipt_number' => 'SR-PI6-'.uniqid(),
            'source' => \App\Enums\StockReceiptSource::Purchase,
            'receipt_date' => now()->toDateString(),
            'status' => \App\Enums\InventoryDocumentStatus::Draft,
            'received_by' => $userId,
        ]);
        $receipt->items()->create([
            'inventory_item_id' => $item->id,
            'quantity' => 50,
            'unit_cost' => 10,
        ]);
        \App\Support\StockReceiptService::post($receipt, $userId);
    }
}
