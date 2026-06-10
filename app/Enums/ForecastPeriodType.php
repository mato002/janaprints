<?php

namespace App\Enums;

enum ForecastPeriodType: string
{
    case Month = 'month';
    case Quarter = 'quarter';
    case Year = 'year';

    public function label(): string
    {
        return match ($this) {
            self::Month => __('Month'),
            self::Quarter => __('Quarter'),
            self::Year => __('Year'),
        };
    }
}
