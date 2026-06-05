<?php

namespace App\Enums;

enum ShiftType: string
{
    case Morning = 'morning';
    case Day = 'day';
    case Night = 'night';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::Morning => __('Morning Shift'),
            self::Day => __('Day Shift'),
            self::Night => __('Night Shift'),
            self::Custom => __('Custom Shift'),
        };
    }
}
