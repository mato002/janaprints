<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\ProfitabilityClass;
use App\Enums\ProfitabilitySnapshotType;
use App\Models\PrintingIntelligence\PrintProfitabilitySnapshot;
use App\Services\PrintingIntelligence\ProfitabilityAnalyticsService;
use Database\Seeders\OrganizationFoundationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfitabilityAnalyticsServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_aggregates_profitability_by_month(): void
    {
        $company = \App\Models\Company::query()->where('code', 'JANA')->firstOrFail();

        PrintProfitabilitySnapshot::query()->create([
            'company_id' => $company->id,
            'snapshot_type' => ProfitabilitySnapshotType::Job,
            'revenue' => 5000,
            'total_cost' => 3000,
            'gross_profit' => 2000,
            'gross_margin_percent' => 40,
            'profitability_class' => ProfitabilityClass::Excellent,
            'profitability_score' => 40,
            'snapshot_date' => now()->toDateString(),
        ]);

        PrintProfitabilitySnapshot::query()->create([
            'company_id' => $company->id,
            'snapshot_type' => ProfitabilitySnapshotType::Job,
            'revenue' => 1000,
            'total_cost' => 1200,
            'gross_profit' => -200,
            'gross_margin_percent' => -20,
            'profitability_class' => ProfitabilityClass::LossMaking,
            'profitability_score' => 0,
            'snapshot_date' => now()->toDateString(),
        ]);

        $result = app(ProfitabilityAnalyticsService::class)->summarize([
            'company_id' => $company->id,
            'period' => 'month',
        ]);

        $this->assertSame(6000.0, (float) $result['total_revenue']);
        $this->assertSame(1800.0, (float) $result['total_profit']);
        $this->assertSame(1, $result['excellent_jobs']);
        $this->assertSame(1, $result['loss_making_jobs']);
        $this->assertNotEmpty($result['series']);
    }
}
