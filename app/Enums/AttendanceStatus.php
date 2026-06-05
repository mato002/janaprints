<?php

namespace App\Enums;

enum AttendanceStatus: string
{
    case Present = 'present';
    case Absent = 'absent';
    case Late = 'late';
    case HalfDay = 'half_day';
    case Leave = 'leave';
    case Holiday = 'holiday';
    case OffDay = 'off_day';

    public function label(): string
    {
        return match ($this) {
            self::Present => __('Present'),
            self::Absent => __('Absent'),
            self::Late => __('Late'),
            self::HalfDay => __('Half Day'),
            self::Leave => __('Leave'),
            self::Holiday => __('Holiday'),
            self::OffDay => __('Off Day'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Present => 'success',
            self::Absent => 'danger',
            self::Late => 'warning',
            self::HalfDay => 'warning',
            self::Leave => 'info',
            self::Holiday => 'neutral',
            self::OffDay => 'neutral',
        };
    }
}
