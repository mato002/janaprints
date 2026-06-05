<?php

namespace App\Enums;

enum DelegationReason: string
{
    case AnnualLeave = 'annual_leave';
    case Travel = 'travel';
    case SickLeave = 'sick_leave';
    case TemporaryAbsence = 'temporary_absence';

    public function label(): string
    {
        return match ($this) {
            self::AnnualLeave => 'Annual Leave',
            self::Travel => 'Travel',
            self::SickLeave => 'Sick Leave',
            self::TemporaryAbsence => 'Temporary Absence',
        };
    }
}
