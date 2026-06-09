<?php

namespace Tests\Feature\Production;

use App\Enums\JobCostCategory;
use App\Enums\JobCostReviewStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\Warehouse;
use App\Enums\InventoryDocumentStatus;
use App\Enums\StockReceiptSource;
use App\Models\Inventory\StockReceipt;
use App\Models\Production\JobCostSnapshot;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Support\Production\JobCostingService;
use App\Support\StockReceiptService;
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

class JobMarginTest extends TestCase
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

    public function test_variance_triggers_cost_review(): void
    {
        [$jobCard, $user, $item, $warehouse] = $this->jobWithQuotationEstimates(estimatedTotal: 1000, revenue: 5000);

        ProductionMaterialConsumptionService::consume(
            $jobCard,
            $item,
            $warehouse->id,
            10,
            $user->id,
        );

        $sheet = JobCostingService::buildOrRefresh($jobCard);

        $this->assertGreaterThan(0, (float) $sheet->material_cost);
        $this->assertEquals(JobCostReviewStatus::RequiresReview, $sheet->cost_review_status);
    }

    public function test_labor_component_allocation(): void
    {
        [$jobCard] = $this->jobWithQuotationEstimates(estimatedTotal: 5000, revenue: 8000);

        JobCostingService::addComponent($jobCard, [
            'cost_category' => JobCostCategory::Labor->value,
            'description' => 'Design labour',
            'hours' => 4,
            'hourly_rate' => 250,
        ]);

        $sheet = JobCostingService::buildOrRefresh($jobCard);

        $this->assertEquals(1000.0, (float) $sheet->labor_cost);
        $this->assertGreaterThan(0, (float) $sheet->overhead_cost);
    }

    public function test_snapshot_created_on_job_close(): void
    {
        [$jobCard, $user, $item, $warehouse] = $this->jobWithQuotationEstimates(estimatedTotal: 2000, revenue: 6000);

        ProductionMaterialConsumptionService::consume(
            $jobCard,
            $item,
            $warehouse->id,
            2,
            $user->id,
        );

        JobCostingService::buildOrRefresh($jobCard);
        $snapshot = JobCostingService::freezeOnJobClose($jobCard);

        $this->assertInstanceOf(JobCostSnapshot::class, $snapshot);
        $this->assertTrue($jobCard->fresh()->costSheet?->is_frozen);
        $this->assertDatabaseCount('job_cost_snapshots', 1);
    }

    /**
     * @return array{0: ProductionJobCard, 1: \App\Models\User, 2: InventoryItem, 3: Warehouse}
     */
    protected function jobWithQuotationEstimates(float $estimatedTotal, float $revenue): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();

        $user = \App\Models\User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
        ]);

        $quotation = Quotation::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'estimated_material_cost' => $estimatedTotal * 0.6,
            'estimated_labor_cost' => $estimatedTotal * 0.2,
            'estimated_machine_cost' => $estimatedTotal * 0.1,
            'estimated_overhead_cost' => $estimatedTotal * 0.1,
            'estimated_total_cost' => $estimatedTotal,
        ]);

        $order = SalesOrder::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'quotation_id' => $quotation->id,
            'total_amount' => $revenue,
        ]);

        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'sales_order_id' => $order->id,
            'quotation_id' => $quotation->id,
        ]);

        $item = InventoryItem::factory()->create([
            'company_id' => $jobCard->company_id,
            'branch_id' => $jobCard->branch_id,
        ]);

        $warehouse = Warehouse::query()
            ->where('company_id', $jobCard->company_id)
            ->where('branch_id', $jobCard->branch_id)
            ->firstOrFail();

        $this->seedStock($company, $branch, $item, $warehouse, $user->id);

        return [$jobCard, $user, $item, $warehouse];
    }

    protected function seedStock(Company $company, Branch $branch, InventoryItem $item, Warehouse $warehouse, int $userId): void
    {
        $receipt = StockReceipt::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'warehouse_id' => $warehouse->id,
            'receipt_number' => 'SR-JM-'.uniqid(),
            'source' => StockReceiptSource::Purchase,
            'receipt_date' => now()->toDateString(),
            'status' => InventoryDocumentStatus::Draft,
            'received_by' => $userId,
        ]);
        $receipt->items()->create(['inventory_item_id' => $item->id, 'quantity' => 100, 'unit_cost' => 50]);
        StockReceiptService::post($receipt, $userId);
    }
}
