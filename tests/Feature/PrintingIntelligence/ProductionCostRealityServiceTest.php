<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\Warehouse;
use App\Models\Production\JobCostSheet;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\SalesOrder;
use App\Services\PrintingIntelligence\ProductionCostRealityService;
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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionCostRealityServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(PlatformConfigurationSeeder::class);
        $this->seed(InventoryFoundationSeeder::class);
        $this->seed(GlAccountTypeSeeder::class);
        $this->seed(JanaPrintsChartOfAccountsSeeder::class);
        $this->seed(JanaPrintsAccountingPeriodsSeeder::class);
        $this->seed(JanaPrintsPostingEngineSeeder::class);
    }

    public function test_reads_actual_job_costs_without_writes(): void
    {
        [$jobCard, $sheetBefore] = $this->jobWithCostSheet();

        $service = app(ProductionCostRealityService::class);

        $this->assertGreaterThan(0, $service->actualMaterialCost($jobCard->id));
        $this->assertGreaterThan(0, $service->actualProductionCost($jobCard->id));
        $this->assertNotEmpty($service->actualConsumption($jobCard->id));

        $profitability = $service->jobProfitability($jobCard->id);
        $this->assertNotNull($profitability);
        $this->assertArrayHasKey('gross_margin_percent', $profitability);

        $this->assertSame(
            JobCostSheet::query()->where('production_job_card_id', $jobCard->id)->count(),
            $sheetBefore,
        );
    }

    /**
     * @return array{0: ProductionJobCard, 1: int}
     */
    protected function jobWithCostSheet(): array
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
            'total_amount' => 8000,
        ]);

        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'sales_order_id' => $order->id,
        ]);

        ProductionMaterialConsumptionService::consume($jobCard, $item, $warehouse->id, 4, $user->id);
        JobCostingService::buildOrRefresh($jobCard->fresh());

        $count = JobCostSheet::query()->where('production_job_card_id', $jobCard->id)->count();

        return [$jobCard, $count];
    }

    protected function seedStock(Company $company, Branch $branch, InventoryItem $item, Warehouse $warehouse, int $userId): void
    {
        $receipt = \App\Models\Inventory\StockReceipt::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'warehouse_id' => $warehouse->id,
            'receipt_number' => 'SR-PI-'.uniqid(),
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
