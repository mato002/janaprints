<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Services\PrintingIntelligence\ForecastConfidenceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForecastConfidenceServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_confidence_bands(): void
    {
        $service = app(ForecastConfidenceService::class);

        $this->assertSame('low', $service->band(30));
        $this->assertSame('medium', $service->band(55));
        $this->assertSame('high', $service->band(85));
    }

    public function test_confidence_score_bounded(): void
    {
        $score = app(ForecastConfidenceService::class)->score([
            'periods_with_data' => 6,
            'historical_periods' => 6,
            'values' => [100, 110, 105, 108, 102, 107],
        ]);

        $this->assertGreaterThanOrEqual(0, $score);
        $this->assertLessThanOrEqual(100, $score);
    }
}
