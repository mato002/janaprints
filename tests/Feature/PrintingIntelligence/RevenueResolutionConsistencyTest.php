<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Production\JobCostSheet;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Services\PrintingIntelligence\RevenueResolutionService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RevenueResolutionConsistencyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_job_cost_sheet_revenue_takes_precedence_over_quotation(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();

        $quotation = Quotation::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'total_amount' => 5000,
        ]);

        $order = SalesOrder::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'total_amount' => 7500,
        ]);

        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'quotation_id' => $quotation->id,
            'sales_order_id' => $order->id,
        ]);

        $sheet = JobCostSheet::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'production_job_card_id' => $jobCard->id,
            'revenue' => 9200,
            'total_cost' => 4000,
            'status' => 'calculated',
        ]);

        $resolved = app(RevenueResolutionService::class)->resolve(
            $jobCard->fresh(['salesOrder', 'quotation']),
            $quotation,
            $sheet,
        );

        $this->assertEqualsWithDelta(9200, $resolved, 0.01);
        $this->assertNotEqualsWithDelta(5000, $resolved, 0.01);
        $this->assertNotEqualsWithDelta(7500, $resolved, 0.01);
    }
}
