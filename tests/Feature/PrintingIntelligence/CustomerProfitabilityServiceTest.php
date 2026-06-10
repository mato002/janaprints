<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\ProfitabilitySnapshotType;
use App\Models\Crm\Customer;
use App\Models\PrintingIntelligence\PrintProfitabilitySnapshot;
use App\Services\PrintingIntelligence\CustomerProfitabilityService;
use Database\Seeders\OrganizationFoundationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerProfitabilityServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_ranks_customers_by_profit(): void
    {
        $company = \App\Models\Company::query()->where('code', 'JANA')->firstOrFail();
        $customerA = Customer::factory()->create(['company_id' => $company->id, 'company_name' => 'Alpha Co']);
        $customerB = Customer::factory()->create(['company_id' => $company->id, 'company_name' => 'Beta Co']);

        PrintProfitabilitySnapshot::query()->create([
            'company_id' => $company->id,
            'customer_id' => $customerA->id,
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
            'customer_id' => $customerB->id,
            'snapshot_type' => ProfitabilitySnapshotType::Customer,
            'revenue' => 5000,
            'total_cost' => 4500,
            'gross_profit' => 500,
            'gross_margin_percent' => 10,
            'snapshot_date' => now()->toDateString(),
            'metadata' => ['job_count' => 1],
        ]);

        $result = app(CustomerProfitabilityService::class)->analyze(['company_id' => $company->id]);

        $this->assertSame('Alpha Co', $result['most_profitable']['customer_name']);
        $this->assertSame('Beta Co', $result['least_profitable']['customer_name']);
        $this->assertCount(2, $result['rankings']);
    }
}
