<?php

namespace App\Enums;

enum DepreciationMethod: string
{
    case StraightLine = 'straight_line';
    case ReducingBalance = 'reducing_balance';
    case UnitsOfProduction = 'units_of_production';

    public function label(): string
    {
        return match ($this) {
            self::StraightLine => __('Straight Line'),
            self::ReducingBalance => __('Reducing Balance'),
            self::UnitsOfProduction => __('Units of Production'),
        };
    }

    public function isImplemented(): bool
    {
        return $this === self::StraightLine;
    }
}
