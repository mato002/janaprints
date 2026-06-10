<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\ProfitabilityClass;
use App\Enums\ProfitabilitySnapshotType;
use App\Models\PrintingIntelligence\PrintProfitabilitySnapshot;
use App\Services\PrintingIntelligence\PrintingIntelligenceGateway;
use Database\Seeders\OrganizationFoundationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GatewayProfitabilityContextTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_gateway_returns_profitability_overview(): void
    {
        $company = \App\Models\Company::query()->where('code', 'JANA')->firstOrFail();

        PrintProfitabilitySnapshot::query()->create([
            'company_id' => $company->id,
            'snapshot_type' => ProfitabilitySnapshotType::Job,
            'revenue' => 10000,
            'total_cost' => 6000,
            'gross_profit' => 4000,
            'gross_margin_percent' => 40,
            'profitability_class' => ProfitabilityClass::Excellent,
            'snapshot_date' => now()->toDateString(),
        ]);

        $context = app(PrintingIntelligenceGateway::class)->profitabilityOverview($company->id);

        $this->assertSame('PI8-V1', $context['formula_version']);
        $this->assertSame(10000.0, (float) $context['summary']['total_revenue']);
        $this->assertCount(1, $context['top_profitable_jobs']);
    }

    public function test_analytics_summary_returns_series(): void
    {
        $company = \App\Models\Company::query()->where('code', 'JANA')->firstOrFail();

        PrintProfitabilitySnapshot::query()->create([
            'company_id' => $company->id,
            'snapshot_type' => ProfitabilitySnapshotType::Job,
            'revenue' => 2000,
            'total_cost' => 1000,
            'gross_profit' => 1000,
            'gross_margin_percent' => 50,
            'profitability_score' => 50,
            'snapshot_date' => now()->toDateString(),
        ]);

        $context = app(PrintingIntelligenceGateway::class)->analyticsSummary(['company_id' => $company->id]);

        $this->assertSame(2000.0, (float) $context['total_revenue']);
        $this->assertNotEmpty($context['series']);
    }

    public function test_margin_leakage_context_delegates_to_service(): void
    {
        $company = \App\Models\Company::query()->where('code', 'JANA')->firstOrFail();

        $context = app(PrintingIntelligenceGateway::class)->marginLeakage(['company_id' => $company->id]);

        $this->assertArrayHasKey('top_profit_erosion_drivers', $context);
        $this->assertArrayHasKey('comparison_count', $context);
    }
}
