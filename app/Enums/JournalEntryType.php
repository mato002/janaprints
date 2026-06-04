<?php

namespace App\Enums;

enum JournalEntryType: string
{
    case Manual = 'manual';
    case System = 'system';
    case Reversal = 'reversal';
    case PeriodClose = 'period_close';
    case YearEndClose = 'year_end_close';

    public function label(): string
    {
        return match ($this) {
            self::Manual => __('Manual'),
            self::System => __('System'),
            self::Reversal => __('Reversal'),
            self::PeriodClose => __('Period close'),
            self::YearEndClose => __('Year-end close'),
        };
    }
}
