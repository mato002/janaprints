<?php

namespace Tests\Feature\Production;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\Warehouse;
use App\Enums\InventoryDocumentStatus;
use App\Enums\StockReceiptSource;
use App\Models\Inventory\StockReceipt;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\SalesOrder;
use App\Support\Production\JobCostingService;
use App\Support\StockReceiptService;
use App\Support\Production\JobProfitabilityService;
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

class JobCostingTest extends TestCase
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

    public function test_job_cost_sheet_from_material_consumption(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $item = InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);
        $warehouse = Warehouse::query()->where('company_id', $company->id)->firstOrFail();

        $user = \App\Models\User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
        ]);

        $this->seedStock($company, $branch, $item, $warehouse, $user->id);

        $order = SalesOrder::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'total_amount' => 5000,
        ]);

        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'sales_order_id' => $order->id,
        ]);

        ProductionMaterialConsumptionService::consume(
            $jobCard,
            $item,
            $warehouse->id,
            5,
            $user->id,
        );

        $sheet = JobCostingService::buildOrRefresh($jobCard);

        $this->assertGreaterThan(0, (float) $sheet->material_cost);
        $this->assertEquals(5000.0, (float) $sheet->revenue);
        $this->assertNotNull($sheet->calculated_at);

        $dashboard = JobProfitabilityService::dashboard($company->id, $branch->id);
        $this->assertNotEmpty($dashboard['customer_profitability']);
    }

    private function seedStock(Company $company, Branch $branch, InventoryItem $item, Warehouse $warehouse, int $userId): void
    {
        $receipt = StockReceipt::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'warehouse_id' => $warehouse->id,
            'receipt_number' => 'SR-JC-001',
            'source' => StockReceiptSource::Purchase,
            'receipt_date' => now()->toDateString(),
            'status' => InventoryDocumentStatus::Draft,
            'received_by' => $userId,
        ]);
        $receipt->items()->create([
            'inventory_item_id' => $item->id,
            'quantity' => 100,
            'unit_cost' => 20,
        ]);

        StockReceiptService::post($receipt, $userId);
    }
}
