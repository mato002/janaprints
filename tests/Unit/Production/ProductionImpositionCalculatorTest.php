<?php

namespace Tests\Unit\Production;

use App\Support\Production\ProductionImpositionCalculator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ProductionImpositionCalculatorTest extends TestCase
{
    #[DataProvider('sheetEstimatesProvider')]
    public function test_estimate_sheets(?float $quantity, ?int $ups, ?int $stored, ?int $expected): void
    {
        $this->assertSame($expected, ProductionImpositionCalculator::estimateSheets($quantity, $ups, $stored));
    }

    public static function sheetEstimatesProvider(): array
    {
        return [
            'uses stored value' => [500.0, 4, 130, 130],
            'ceil division' => [500.0, 4, null, 125],
            'missing ups' => [100.0, null, null, null],
            'zero ups' => [100.0, 0, null, null],
        ];
    }
}
