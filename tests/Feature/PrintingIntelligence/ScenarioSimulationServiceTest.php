<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\ProfitabilitySnapshotType;
use App\Models\PrintingIntelligence\PrintProfitabilitySnapshot;
use App\Services\PrintingIntelligence\ScenarioSimulationService;
use Database\Seeders\OrganizationFoundationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScenarioSimulationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_simulates_sales_increase_scenario(): void
    {
        $company = \App\Models\Company::query()->where('code', 'JANA')->firstOrFail();

        PrintProfitabilitySnapshot::query()->create([
            'company_id' => $company->id,
            'snapshot_type' => ProfitabilitySnapshotType::Job,
            'revenue' => 10000,
            'total_cost' => 7000,
            'gross_profit' => 3000,
            'snapshot_date' => now()->toDateString(),
        ]);

        $result = app(ScenarioSimulationService::class)->simulate([
            'company_id' => $company->id,
            'scenario' => 'sales_plus_10',
        ]);

        $this->assertTrue($result['read_only']);
        $this->assertGreaterThan((float) $result['baseline']['revenue'], (float) $result['simulated']['revenue']);
    }
}
