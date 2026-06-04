<?php

namespace App\Enums;

enum FiscalYearStatus: string
{
    case Open = 'open';
    case YearEndPreparation = 'year_end_preparation';
    case Closed = 'closed';
    case Locked = 'locked';

    public function label(): string
    {
        return match ($this) {
            self::Open => __('Open'),
            self::YearEndPreparation => __('Year-end preparation'),
            self::Closed => __('Closed'),
            self::Locked => __('Locked'),
        };
    }

    public function canPost(): bool
    {
        return $this === self::Open;
    }
}
