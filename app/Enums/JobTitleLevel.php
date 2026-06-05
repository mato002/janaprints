<?php

namespace App\Enums;

enum JobTitleLevel: string
{
    case Executive = 'executive';
    case SeniorManagement = 'senior_management';
    case Management = 'management';
    case Supervisor = 'supervisor';
    case Officer = 'officer';
    case Staff = 'staff';

    public function label(): string
    {
        return match ($this) {
            self::Executive => __('Executive'),
            self::SeniorManagement => __('Senior Management'),
            self::Management => __('Management'),
            self::Supervisor => __('Supervisor'),
            self::Officer => __('Officer'),
            self::Staff => __('Staff'),
        };
    }
}
