<?php

namespace Tests\Feature\Production;

use App\Enums\CustomerStatus;
use App\Enums\ProductionType;
use App\Enums\SalesOrderStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Production\JobCostSheet;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\SalesOrder;
use App\Support\Production\CustomerProfitabilityService;
use Database\Seeders\ProductionFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerProfitabilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(ProductionFoundationSeeder::class);
    }

    public function test_customer_profitability_ranking(): void
    {
        [$company, $branch] = $this->seedSheet(10000, 6000);

        $rows = app(CustomerProfitabilityService::class)->ranking($company->id, $branch->id);

        $this->assertNotEmpty($rows);
        $this->assertEquals(4000.0, $rows[0]['profit']);
        $this->assertEquals(40.0, $rows[0]['margin_percent']);
        $this->assertEquals(1, $rows[0]['jobs_count']);
    }

    /**
     * @return array{0: Company, 1: Branch}
     */
    protected function seedSheet(float $revenue, float $cost): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'status' => CustomerStatus::Active,
        ]);

        $order = SalesOrder::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'status' => SalesOrderStatus::Confirmed,
            'total_amount' => $revenue,
        ]);

        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'sales_order_id' => $order->id,
            'production_type' => ProductionType::Digital,
        ]);

        JobCostSheet::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'production_job_card_id' => $jobCard->id,
            'material_cost' => $cost,
            'total_cost' => $cost,
            'revenue' => $revenue,
            'gross_profit' => $revenue - $cost,
            'gross_margin_percent' => $revenue > 0 ? round((($revenue - $cost) / $revenue) * 100, 2) : 0,
            'calculated_at' => now(),
        ]);

        return [$company, $branch];
    }
}
