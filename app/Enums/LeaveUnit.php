<?php

namespace App\Enums;

enum LeaveUnit: string
{
    case Days = 'days';
    case Hours = 'hours';

    public function label(): string
    {
        return match ($this) {
            self::Days => __('Days'),
            self::Hours => __('Hours'),
        };
    }
}
