<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\ProfitabilityClass;
use App\Enums\ProfitabilitySnapshotType;
use App\Models\Company;
use App\Models\PrintingIntelligence\PrintProfitabilitySnapshot;
use App\Services\PrintingIntelligence\Advisor\ProfitabilityAdvisorService;
use Database\Seeders\OrganizationFoundationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfitabilityAdvisorServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_loss_making_job_generates_recommendation(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();

        PrintProfitabilitySnapshot::query()->create([
            'company_id' => $company->id,
            'snapshot_type' => ProfitabilitySnapshotType::Job,
            'revenue' => 1000,
            'total_cost' => 1200,
            'gross_profit' => -200,
            'gross_margin_percent' => -20,
            'profitability_class' => ProfitabilityClass::LossMaking,
            'snapshot_date' => now()->toDateString(),
        ]);

        $recs = app(ProfitabilityAdvisorService::class)->recommend(['company_id' => $company->id]);

        $this->assertTrue(collect($recs)->contains(fn ($r) => str_starts_with($r['rule_code'], 'profitability:loss_job')));
    }
}
