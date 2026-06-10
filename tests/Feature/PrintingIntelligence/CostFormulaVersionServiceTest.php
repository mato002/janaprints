<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\CalibrationRuleStatus;
use App\Enums\CalibrationRuleType;
use App\Models\PrintingIntelligence\PrintCalibrationRule;
use App\Services\PrintingIntelligence\CostFormulaVersionService;
use Database\Seeders\OrganizationFoundationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CostFormulaVersionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_returns_config_versions_and_increments_on_approval(): void
    {
        $service = app(CostFormulaVersionService::class);
        $company = \App\Models\Company::query()->where('code', 'JANA')->firstOrFail();

        $this->assertSame('PI3-V1', $service->currentVersions($company->id)['PI3']);
        $this->assertSame('PI3-V2', $service->nextVersion(CalibrationRuleType::InkYield, $company->id));

        PrintCalibrationRule::query()->create([
            'company_id' => $company->id,
            'rule_type' => CalibrationRuleType::InkYield,
            'rule_key' => 'default_cmyk_coverage_factor',
            'proposed_value' => 1.2,
            'status' => CalibrationRuleStatus::Approved,
            'rule_version' => 'PI3-V2',
            'approved_at' => now(),
        ]);

        $this->assertSame('PI3-V2', $service->currentVersions($company->id)['PI3']);
        $this->assertTrue($service->assertHistoricalImmutability('PI3-V1', 'PI3-V2'));
    }
}
