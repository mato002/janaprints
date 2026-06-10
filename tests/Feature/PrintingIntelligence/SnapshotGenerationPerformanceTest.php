<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Services\PrintingIntelligence\ProfitabilitySnapshotGeneratorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\PrintingIntelligence\Concerns\BuildsProductionCostFixtures;
use Tests\TestCase;

class SnapshotGenerationPerformanceTest extends TestCase
{
    use BuildsProductionCostFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedProductionCostStack();
        config(['printing_intelligence.profitability_intelligence_enabled' => true]);
    }

    public function test_profitability_snapshot_generation_completes_with_eager_loaded_jobs(): void
    {
        [$company, , $jobCard] = $this->jobWithCostSheet();

        DB::flushQueryLog();
        DB::enableQueryLog();

        $snapshots = app(ProfitabilitySnapshotGeneratorService::class)
            ->generateForCompany($company->id, 90, 'job', true);

        $this->assertNotEmpty($snapshots);
        $this->assertDatabaseHas('print_profitability_snapshots', [
            'company_id' => $company->id,
            'production_job_card_id' => $jobCard->id,
            'snapshot_type' => 'job',
        ]);

        $this->assertLessThan(60, count(DB::getQueryLog()));
    }
}
