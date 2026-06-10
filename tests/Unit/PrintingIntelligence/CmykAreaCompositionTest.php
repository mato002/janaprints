<?php

namespace Tests\Unit\PrintingIntelligence;

use App\Support\PrintingIntelligence\CmykAreaComposition;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CmykAreaCompositionTest extends TestCase
{
    #[Test]
    public function test_from_channel_coverage_totals_one_hundred(): void
    {
        $composition = CmykAreaComposition::fromChannelCoverage(25, 25, 25, 25, 0, 0);

        $this->assertSame(100.0, $composition['total']);
        $this->assertEqualsWithDelta(25, $composition['cyan'], 0.01);
        $this->assertEqualsWithDelta(25, $composition['magenta'], 0.01);
        $this->assertEqualsWithDelta(25, $composition['yellow'], 0.01);
        $this->assertEqualsWithDelta(25, $composition['black'], 0.01);
    }

    #[Test]
    public function test_from_channel_coverage_includes_white_and_transparent(): void
    {
        $composition = CmykAreaComposition::fromChannelCoverage(40, 20, 10, 10, 15, 5);

        $this->assertSame(100.0, $composition['total']);
        $this->assertEqualsWithDelta(15, $composition['white'], 0.01);
        $this->assertEqualsWithDelta(5, $composition['transparent'], 0.01);
    }
}
