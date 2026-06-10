<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\ProfitabilitySnapshotType;
use App\Models\PrintingIntelligence\PrintProfitabilitySnapshot;
use App\Services\PrintingIntelligence\ProfitForecastService;
use Database\Seeders\OrganizationFoundationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfitForecastServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_forecasts_profit_and_margin(): void
    {
        $company = \App\Models\Company::query()->where('code', 'JANA')->firstOrFail();

        PrintProfitabilitySnapshot::query()->create([
            'company_id' => $company->id,
            'snapshot_type' => ProfitabilitySnapshotType::Job,
            'revenue' => 10000,
            'total_cost' => 6000,
            'gross_profit' => 4000,
            'gross_margin_percent' => 40,
            'snapshot_date' => now()->toDateString(),
        ]);

        $result = app(ProfitForecastService::class)->forecast(['company_id' => $company->id]);

        $this->assertNotNull($result['forecast_profit']['forecast_value']);
        $this->assertNotNull($result['forecast_margin_percent']['forecast_value']);
    }
}
