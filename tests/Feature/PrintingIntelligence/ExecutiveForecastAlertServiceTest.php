<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\ProfitabilitySnapshotType;
use App\Models\PrintingIntelligence\PrintProfitabilitySnapshot;
use App\Services\PrintingIntelligence\ExecutiveForecastAlertService;
use Database\Seeders\OrganizationFoundationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExecutiveForecastAlertServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_generates_read_only_alerts(): void
    {
        $company = \App\Models\Company::query()->where('code', 'JANA')->firstOrFail();

        PrintProfitabilitySnapshot::query()->create([
            'company_id' => $company->id,
            'snapshot_type' => ProfitabilitySnapshotType::Job,
            'revenue' => 1000,
            'total_cost' => 1500,
            'gross_profit' => -500,
            'gross_margin_percent' => -50,
            'snapshot_date' => now()->toDateString(),
        ]);

        $result = app(ExecutiveForecastAlertService::class)->generate(['company_id' => $company->id]);

        $this->assertTrue($result['read_only']);
        $this->assertIsArray($result['alerts']);
    }
}
