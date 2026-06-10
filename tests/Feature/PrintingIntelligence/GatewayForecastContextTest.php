<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\ProfitabilitySnapshotType;
use App\Models\PrintingIntelligence\PrintProfitabilitySnapshot;
use App\Services\PrintingIntelligence\PrintingIntelligenceGateway;
use Database\Seeders\OrganizationFoundationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GatewayForecastContextTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_gateway_returns_forecast_overview(): void
    {
        $company = \App\Models\Company::query()->where('code', 'JANA')->firstOrFail();

        PrintProfitabilitySnapshot::query()->create([
            'company_id' => $company->id,
            'snapshot_type' => ProfitabilitySnapshotType::Job,
            'revenue' => 5000,
            'total_cost' => 3000,
            'gross_profit' => 2000,
            'gross_margin_percent' => 40,
            'snapshot_date' => now()->toDateString(),
        ]);

        $context = app(PrintingIntelligenceGateway::class)->forecastOverview($company->id);

        $this->assertSame('PI9-V1', $context['formula_version']);
        $this->assertArrayHasKey('forecast_revenue', $context);
        $this->assertArrayHasKey('alerts', $context);
    }

    public function test_scenario_simulation_context(): void
    {
        $company = \App\Models\Company::query()->where('code', 'JANA')->firstOrFail();

        $context = app(PrintingIntelligenceGateway::class)->scenarioSimulation([
            'company_id' => $company->id,
            'scenario' => 'sales_plus_20',
        ]);

        $this->assertArrayHasKey('simulated', $context);
        $this->assertTrue($context['read_only']);
    }
}
