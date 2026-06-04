<?php

namespace App\Enums;

enum NotificationCategory: string
{
    case Commercial = 'commercial';
    case Production = 'production';
    case Accounting = 'accounting';
    case Hr = 'hr';
    case System = 'system';

    public function label(): string
    {
        return match ($this) {
            self::Commercial => __('Commercial'),
            self::Production => __('Production'),
            self::Accounting => __('Accounting'),
            self::Hr => __('HR'),
            self::System => __('System'),
        };
    }

    public function preferenceKey(): string
    {
        return match ($this) {
            self::Commercial => 'commercial_alerts',
            self::Production => 'production_alerts',
            self::Accounting => 'accounting_alerts',
            self::Hr => 'hr_alerts',
            self::System => 'system_alerts',
        };
    }
}
