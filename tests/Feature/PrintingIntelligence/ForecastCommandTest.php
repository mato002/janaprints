<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\ProfitabilitySnapshotType;
use App\Models\PrintingIntelligence\PrintForecastSnapshot;
use App\Models\PrintingIntelligence\PrintProfitabilitySnapshot;
use Database\Seeders\OrganizationFoundationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForecastCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(OrganizationFoundationSeeder::class);
        config(['printing_intelligence.executive_forecasting_enabled' => true]);
    }

    public function test_command_generates_forecast_snapshots(): void
    {
        $company = \App\Models\Company::query()->where('code', 'JANA')->firstOrFail();

        PrintProfitabilitySnapshot::query()->create([
            'company_id' => $company->id,
            'snapshot_type' => ProfitabilitySnapshotType::Job,
            'revenue' => 8000,
            'total_cost' => 5000,
            'gross_profit' => 3000,
            'snapshot_date' => now()->toDateString(),
        ]);

        $this->artisan('printing:forecast:generate', ['--company' => $company->id])
            ->assertSuccessful();

        $this->assertGreaterThan(0, PrintForecastSnapshot::query()->where('company_id', $company->id)->count());
    }

    public function test_dry_run_does_not_persist(): void
    {
        $company = \App\Models\Company::query()->where('code', 'JANA')->firstOrFail();

        $this->artisan('printing:forecast:generate', [
            '--company' => $company->id,
            '--dry-run' => true,
        ])->assertSuccessful();

        $this->assertSame(0, PrintForecastSnapshot::query()->count());
    }
}
