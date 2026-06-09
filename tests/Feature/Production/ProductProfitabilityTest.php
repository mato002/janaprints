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
use App\Support\Production\ProductProfitabilityService;
use Database\Seeders\ProductionFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductProfitabilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(ProductionFoundationSeeder::class);
    }

    public function test_product_profitability_by_production_type(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'status' => CustomerStatus::Active,
        ]);

        foreach ([ProductionType::Digital, ProductionType::LargeFormat] as $type) {
            $order = SalesOrder::factory()->create([
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'customer_id' => $customer->id,
                'status' => SalesOrderStatus::Confirmed,
                'total_amount' => 5000,
            ]);

            $jobCard = ProductionJobCard::factory()->create([
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'customer_id' => $customer->id,
                'sales_order_id' => $order->id,
                'production_type' => $type,
            ]);

            JobCostSheet::query()->create([
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'production_job_card_id' => $jobCard->id,
                'material_cost' => 1000,
                'total_cost' => 2000,
                'revenue' => 5000,
                'gross_profit' => 3000,
                'gross_margin_percent' => 60,
                'calculated_at' => now(),
            ]);
        }

        $rows = app(ProductProfitabilityService::class)->ranking($company->id, $branch->id);

        $this->assertCount(2, $rows);
        $this->assertArrayHasKey('production_type', $rows[0]);
        $this->assertArrayHasKey('units_sold', $rows[0]);
    }
}
