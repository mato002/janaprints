<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\ProfitabilitySnapshotType;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\PrintingIntelligence\PrintProfitabilitySnapshot;
use App\Services\PrintingIntelligence\CustomerProfitabilityService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BranchScopedAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_branch_filter_limits_customer_profitability_rankings(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $hq = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $satellite = Branch::factory()->create([
            'company_id' => $company->id,
            'code' => 'SAT',
            'name' => 'Satellite Branch',
        ]);

        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'company_name' => 'Branch Scoped Co',
        ]);

        PrintProfitabilitySnapshot::query()->create([
            'company_id' => $company->id,
            'branch_id' => $hq->id,
            'customer_id' => $customer->id,
            'snapshot_type' => ProfitabilitySnapshotType::Customer,
            'revenue' => 10000,
            'total_cost' => 6000,
            'gross_profit' => 4000,
            'gross_margin_percent' => 40,
            'snapshot_date' => now()->toDateString(),
            'metadata' => ['job_count' => 2],
        ]);

        PrintProfitabilitySnapshot::query()->create([
            'company_id' => $company->id,
            'branch_id' => $satellite->id,
            'customer_id' => $customer->id,
            'snapshot_type' => ProfitabilitySnapshotType::Customer,
            'revenue' => 2000,
            'total_cost' => 1800,
            'gross_profit' => 200,
            'gross_margin_percent' => 10,
            'snapshot_date' => now()->toDateString(),
            'metadata' => ['job_count' => 1],
        ]);

        $service = app(CustomerProfitabilityService::class);

        $hqResult = $service->analyze(['company_id' => $company->id, 'branch_id' => $hq->id]);
        $satelliteResult = $service->analyze(['company_id' => $company->id, 'branch_id' => $satellite->id]);

        $this->assertEqualsWithDelta(10000, $hqResult['total_revenue'], 0.01);
        $this->assertEqualsWithDelta(4000, $hqResult['total_profit'], 0.01);
        $this->assertEqualsWithDelta(2000, $satelliteResult['total_revenue'], 0.01);
        $this->assertEqualsWithDelta(200, $satelliteResult['total_profit'], 0.01);
    }
}
