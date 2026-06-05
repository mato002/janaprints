<?php

namespace App\Enums;

enum AttendanceMethod: string
{
    case Manual = 'manual';
    case Clock = 'clock';
    case Shift = 'shift';

    public function label(): string
    {
        return match ($this) {
            self::Manual => __('Manual'),
            self::Clock => __('Clock In / Out'),
            self::Shift => __('Shift'),
        };
    }
}
