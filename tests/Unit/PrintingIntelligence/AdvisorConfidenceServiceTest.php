<?php

namespace Tests\Unit\PrintingIntelligence;

use App\Services\PrintingIntelligence\Advisor\AdvisorConfidenceService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdvisorConfidenceServiceTest extends TestCase
{
    #[Test]
    public function score_returns_value_between_zero_and_one_hundred(): void
    {
        $service = app(AdvisorConfidenceService::class);

        $score = $service->score([
            'data_points' => 3,
            'required_points' => 3,
            'historical_periods' => 4,
            'signal_strength' => 80,
        ]);

        $this->assertGreaterThanOrEqual(0, $score);
        $this->assertLessThanOrEqual(100, $score);
    }

    #[Test]
    public function band_maps_score_to_low_medium_high(): void
    {
        $service = app(AdvisorConfidenceService::class);

        $this->assertSame('low', $service->band(30));
        $this->assertSame('medium', $service->band(60));
        $this->assertSame('high', $service->band(85));
    }
}
