<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Models\Company;
use App\Services\PrintingIntelligence\PrintingIntelligenceGateway;
use Database\Seeders\OrganizationFoundationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GatewayAdvisorContextTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_gateway_returns_advisor_overview(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $gateway = app(PrintingIntelligenceGateway::class);

        $context = $gateway->advisorOverview($company->id);

        $this->assertArrayHasKey('recommendations', $context);
        $this->assertArrayHasKey('summary', $context);
        $this->assertTrue($context['read_only']);
    }

    public function test_gateway_executive_advisor_summary(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $gateway = app(PrintingIntelligenceGateway::class);

        $summary = $gateway->executiveAdvisorSummary(['company_id' => $company->id]);

        $this->assertArrayHasKey('top_opportunities', $summary);
        $this->assertArrayHasKey('top_risks', $summary);
        $this->assertTrue($summary['read_only']);
    }

    public function test_gateway_quotation_recommendations(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();

        $recs = app(PrintingIntelligenceGateway::class)->quotationRecommendations(['company_id' => $company->id]);

        $this->assertIsArray($recs);
    }
}
