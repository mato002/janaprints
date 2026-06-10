<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Models\PrintingIntelligence\PrintProfitabilitySnapshot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\PrintingIntelligence\Concerns\BuildsProductionCostFixtures;
use Tests\TestCase;

class ProfitabilityCommandTest extends TestCase
{
    use BuildsProductionCostFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedProductionCostStack();
        config(['printing_intelligence.profitability_intelligence_enabled' => true]);
    }

    public function test_command_generates_snapshots(): void
    {
        [$company] = array_slice($this->jobWithCostSheet(), 0, 1);

        $this->artisan('printing:profitability:generate', ['--company' => $company->id])
            ->assertSuccessful();

        $this->assertGreaterThan(0, PrintProfitabilitySnapshot::query()->where('company_id', $company->id)->count());
    }

    public function test_dry_run_does_not_persist(): void
    {
        [$company, , $jobCard] = $this->jobWithCostSheet();

        $this->artisan('printing:profitability:generate', [
            '--company' => $company->id,
            '--dry-run' => true,
        ])->assertSuccessful();

        $this->assertSame(0, PrintProfitabilitySnapshot::query()->where('production_job_card_id', $jobCard->id)->count());
    }
}
