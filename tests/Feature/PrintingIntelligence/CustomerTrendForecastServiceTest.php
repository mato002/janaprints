<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\ProfitabilitySnapshotType;
use App\Models\Crm\Customer;
use App\Models\PrintingIntelligence\PrintProfitabilitySnapshot;
use App\Services\PrintingIntelligence\CustomerTrendForecastService;
use Database\Seeders\OrganizationFoundationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTrendForecastServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_identifies_customer_growth_and_concentration(): void
    {
        $company = \App\Models\Company::query()->where('code', 'JANA')->firstOrFail();
        $customer = Customer::factory()->create(['company_id' => $company->id, 'company_name' => 'Growth Co']);

        PrintProfitabilitySnapshot::query()->create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'snapshot_type' => ProfitabilitySnapshotType::Job,
            'revenue' => 15000,
            'gross_profit' => 6000,
            'snapshot_date' => now()->toDateString(),
        ]);

        $result = app(CustomerTrendForecastService::class)->forecast(['company_id' => $company->id]);

        $this->assertArrayHasKey('customer_concentration_risk_percent', $result);
        $this->assertNotEmpty($result['rankings']);
    }
}
