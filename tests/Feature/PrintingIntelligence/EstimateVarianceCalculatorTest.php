<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\EstimateVarianceClass;
use App\Services\PrintingIntelligence\EstimateVarianceCalculator;
use Tests\TestCase;

class EstimateVarianceCalculatorTest extends TestCase
{
    public function test_calculates_absolute_and_percentage_variance(): void
    {
        $result = app(EstimateVarianceCalculator::class)->calculate(
            ['material' => 100, 'ink' => 50, 'machine' => 200, 'labour' => 100, 'overhead' => 50, 'total' => 500],
            ['material' => 120, 'ink' => 40, 'machine' => 220, 'labour' => 90, 'overhead' => 60, 'total' => 530],
        );

        $this->assertEqualsWithDelta(30, $result['total']['variance'], 0.01);
        $this->assertEqualsWithDelta(6, $result['total']['variance_percent'], 0.01);
    }

    public function test_handles_estimated_zero(): void
    {
        $result = app(EstimateVarianceCalculator::class)->calculate(
            ['material' => 0, 'ink' => 0, 'machine' => 0, 'labour' => 0, 'overhead' => 0, 'total' => 0],
            ['material' => 100, 'ink' => 0, 'machine' => 0, 'labour' => 0, 'overhead' => 0, 'total' => 100],
        );

        $this->assertNull($result['total']['variance_percent']);
        $this->assertNotEmpty($result['warnings']);
    }

    public function test_classifies_variance_and_accuracy_score(): void
    {
        $accurate = app(EstimateVarianceCalculator::class)->calculate(
            ['material' => 100, 'ink' => 0, 'machine' => 0, 'labour' => 0, 'overhead' => 0, 'total' => 100],
            ['material' => 104, 'ink' => 0, 'machine' => 0, 'labour' => 0, 'overhead' => 0, 'total' => 104],
        );
        $this->assertSame(EstimateVarianceClass::Accurate, $accurate['variance_class']);
        $this->assertEqualsWithDelta(96, $accurate['accuracy_score'], 0.01);

        $unreliable = app(EstimateVarianceCalculator::class)->calculate(
            ['material' => 100, 'ink' => 0, 'machine' => 0, 'labour' => 0, 'overhead' => 0, 'total' => 100],
            ['material' => 150, 'ink' => 0, 'machine' => 0, 'labour' => 0, 'overhead' => 0, 'total' => 150],
        );
        $this->assertSame(EstimateVarianceClass::Unreliable, $unreliable['variance_class']);
        $this->assertEqualsWithDelta(50, $unreliable['accuracy_score'], 0.01);
    }
}
