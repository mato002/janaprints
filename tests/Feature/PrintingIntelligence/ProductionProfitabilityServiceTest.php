<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\ProfitabilityClass;
use App\Services\PrintingIntelligence\ProductionProfitabilityService;
use App\Services\PrintingIntelligence\ProfitabilitySnapshotGeneratorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\PrintingIntelligence\Concerns\BuildsProductionCostFixtures;
use Tests\TestCase;

class ProductionProfitabilityServiceTest extends TestCase
{
    use BuildsProductionCostFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedProductionCostStack();
    }

    public function test_calculates_job_profitability_from_actual_costs(): void
    {
        [$company, , $jobCard] = $this->jobWithCostSheet(consumptionQty: 4, unitCost: 10, orderTotal: 8000);

        $metrics = app(ProductionProfitabilityService::class)->calculateForJob($jobCard->fresh());

        $this->assertGreaterThan(0, (float) $metrics['revenue']);
        $this->assertGreaterThan(0, (float) $metrics['total_cost']);
        $this->assertEqualsWithDelta(
            (float) $metrics['revenue'] - (float) $metrics['total_cost'],
            (float) $metrics['gross_profit'],
            0.01
        );
        $this->assertNotNull($metrics['gross_margin_percent']);
    }

    public function test_classifies_margin_bands(): void
    {
        $service = app(ProductionProfitabilityService::class);

        $this->assertSame(ProfitabilityClass::Excellent, $service->classify(45));
        $this->assertSame(ProfitabilityClass::Good, $service->classify(30));
        $this->assertSame(ProfitabilityClass::Average, $service->classify(20));
        $this->assertSame(ProfitabilityClass::Weak, $service->classify(5));
        $this->assertSame(ProfitabilityClass::LossMaking, $service->classify(0));
        $this->assertSame(ProfitabilityClass::Unknown, $service->classify(null));
    }

    public function test_snapshot_generator_persists_job_snapshot(): void
    {
        [$company, , $jobCard] = $this->jobWithCostSheet();

        $snapshot = app(ProfitabilitySnapshotGeneratorService::class)->generateJobSnapshot($jobCard->fresh(), true);

        $this->assertNotNull($snapshot);
        $this->assertDatabaseHas('print_profitability_snapshots', [
            'company_id' => $company->id,
            'production_job_card_id' => $jobCard->id,
            'snapshot_type' => 'job',
        ]);
    }
}
