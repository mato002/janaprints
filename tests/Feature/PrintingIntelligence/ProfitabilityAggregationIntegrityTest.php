<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\ProfitabilitySnapshotType;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\PrintingIntelligence\PrintProfitabilitySnapshot;
use App\Services\PrintingIntelligence\CustomerProfitabilityService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfitabilityAggregationIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_duplicate_customer_snapshots_are_deduped_by_latest_date(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'company_name' => 'Dedupe Customer',
        ]);

        PrintProfitabilitySnapshot::query()->create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'snapshot_type' => ProfitabilitySnapshotType::Customer,
            'revenue' => 5000,
            'total_cost' => 4000,
            'gross_profit' => 1000,
            'gross_margin_percent' => 20,
            'snapshot_date' => now()->subDays(10)->toDateString(),
            'metadata' => ['job_count' => 1],
        ]);

        PrintProfitabilitySnapshot::query()->create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'snapshot_type' => ProfitabilitySnapshotType::Customer,
            'revenue' => 12000,
            'total_cost' => 7000,
            'gross_profit' => 5000,
            'gross_margin_percent' => 41.67,
            'snapshot_date' => now()->toDateString(),
            'metadata' => ['job_count' => 3],
        ]);

        $result = app(CustomerProfitabilityService::class)->analyze(['company_id' => $company->id]);

        $this->assertCount(1, $result['rankings']);
        $this->assertEqualsWithDelta(12000, $result['total_revenue'], 0.01);
        $this->assertEqualsWithDelta(5000, $result['total_profit'], 0.01);
        $this->assertSame('Dedupe Customer', $result['most_profitable']['customer_name']);
    }
}
