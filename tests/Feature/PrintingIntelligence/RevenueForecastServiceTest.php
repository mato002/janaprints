<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\ProfitabilitySnapshotType;
use App\Models\PrintingIntelligence\PrintProfitabilitySnapshot;
use App\Services\PrintingIntelligence\RevenueForecastService;
use Database\Seeders\OrganizationFoundationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RevenueForecastServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_forecasts_revenue_from_historical_snapshots(): void
    {
        $company = \App\Models\Company::query()->where('code', 'JANA')->firstOrFail();

        for ($i = 1; $i <= 4; $i++) {
            PrintProfitabilitySnapshot::query()->create([
                'company_id' => $company->id,
                'snapshot_type' => ProfitabilitySnapshotType::Job,
                'revenue' => 1000 * $i,
                'total_cost' => 500 * $i,
                'gross_profit' => 500 * $i,
                'snapshot_date' => now()->subMonths(5 - $i)->toDateString(),
            ]);
        }

        $result = app(RevenueForecastService::class)->forecast(['company_id' => $company->id]);

        $this->assertSame('PI9-V1', $result['formula_version']);
        $this->assertNotNull($result['next_month']['forecast_value']);
        $this->assertNotNull($result['next_quarter']['forecast_value']);
    }
}
