<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Services\PrintingIntelligence\ActualProductionCostResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\PrintingIntelligence\Concerns\BuildsProductionCostFixtures;
use Tests\TestCase;

class ActualProductionCostResolverTest extends TestCase
{
    use BuildsProductionCostFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedProductionCostStack();
    }

    public function test_resolves_actual_cost_from_job_cost_sheet(): void
    {
        [, , $jobCard] = $this->jobWithCostSheet();

        $result = app(ActualProductionCostResolver::class)->resolve($jobCard);

        $this->assertGreaterThan(0, $result['actual_material_cost']);
        $this->assertGreaterThan(0, $result['actual_total_cost']);
        $this->assertNotNull($result['job_cost_sheet_id']);
        $this->assertNotNull($result['actual_selling_price']);
    }

    public function test_warns_when_actual_data_missing(): void
    {
        [, , $jobCard] = $this->jobWithCostSheet();
        \App\Models\Production\JobCostSheet::query()->where('production_job_card_id', $jobCard->id)->delete();

        $result = app(ActualProductionCostResolver::class)->resolve($jobCard->fresh());

        $this->assertSame(0.0, $result['actual_total_cost']);
        $this->assertNotEmpty($result['warnings']);
    }

    public function test_falls_back_safely_when_ink_split_unavailable(): void
    {
        [, , $jobCard] = $this->jobWithCostSheet();

        $result = app(ActualProductionCostResolver::class)->resolve($jobCard);

        $this->assertSame(0.0, $result['actual_ink_cost']);
        $this->assertTrue(
            collect($result['warnings'])->contains(fn ($w) => str_contains(strtolower($w), 'ink')),
        );
    }
}
