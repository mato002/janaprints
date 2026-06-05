<?php

namespace App\Enums;

enum AttendanceCorrectionType: string
{
    case MissingClockOut = 'missing_clock_out';
    case ManualCorrection = 'manual_correction';
    case Adjustment = 'adjustment';

    public function label(): string
    {
        return match ($this) {
            self::MissingClockOut => __('Missing Clock Out'),
            self::ManualCorrection => __('Manual Correction'),
            self::Adjustment => __('Attendance Adjustment'),
        };
    }
}
