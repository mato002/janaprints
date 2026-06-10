<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\CalibrationRuleStatus;
use App\Enums\CalibrationRuleType;
use App\Models\PrintingIntelligence\PrintCalibrationRule;
use App\Services\PrintingIntelligence\ActiveCostingProfileService;
use Database\Seeders\OrganizationFoundationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActiveCostingProfileServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_resolves_config_defaults_and_approved_rules(): void
    {
        $company = \App\Models\Company::query()->where('code', 'JANA')->firstOrFail();
        $service = app(ActiveCostingProfileService::class);

        $defaults = $service->profile($company->id);
        $this->assertEqualsWithDelta(500, (float) $defaults['labour_rate_per_hour'], 0.01);

        PrintCalibrationRule::query()->create([
            'company_id' => $company->id,
            'rule_type' => CalibrationRuleType::LabourRate,
            'rule_key' => 'labour_hourly_rate',
            'proposed_value' => 650,
            'status' => CalibrationRuleStatus::Approved,
            'approved_at' => now(),
            'effective_from' => now()->subDay(),
        ]);

        $updated = $service->profile($company->id);
        $this->assertEqualsWithDelta(650, (float) $updated['labour_rate_per_hour'], 0.01);
    }
}
