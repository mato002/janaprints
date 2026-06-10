<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\CalibrationRuleStatus;
use App\Enums\CalibrationRuleType;
use App\Models\PrintingIntelligence\PrintCalibrationRule;
use App\Services\PrintingIntelligence\PrintingIntelligenceGateway;
use Database\Seeders\OrganizationFoundationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GatewayCalibrationContextTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_gateway_returns_calibration_contexts(): void
    {
        $company = \App\Models\Company::query()->where('code', 'JANA')->firstOrFail();
        $gateway = app(PrintingIntelligenceGateway::class);

        PrintCalibrationRule::query()->create([
            'company_id' => $company->id,
            'rule_type' => CalibrationRuleType::InkYield,
            'rule_key' => 'default_cmyk_coverage_factor',
            'current_value' => 1,
            'proposed_value' => 1.15,
            'status' => CalibrationRuleStatus::Draft,
            'reason' => __('Gateway test'),
        ]);

        $recs = $gateway->calibrationRecommendations($company->id);
        $this->assertNotEmpty($recs['recommendations']);

        $profile = $gateway->activeCostingProfile($company->id);
        $this->assertArrayHasKey('ink_yield_factor', $profile);

        $versions = $gateway->formulaVersions($company->id);
        $this->assertSame('PI7-V1', $versions['PI7']);
    }
}
