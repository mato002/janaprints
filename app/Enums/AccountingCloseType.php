<?php

namespace App\Enums;

enum AccountingCloseType: string
{
    case PeriodClose = 'period_close';
    case PeriodCloseReversal = 'period_close_reversal';
    case YearEndClose = 'year_end_close';
    case YearEndCloseReversal = 'year_end_close_reversal';

    public function label(): string
    {
        return match ($this) {
            self::PeriodClose => __('Period close'),
            self::PeriodCloseReversal => __('Period close reversal'),
            self::YearEndClose => __('Year-end close'),
            self::YearEndCloseReversal => __('Year-end close reversal'),
        };
    }
}
