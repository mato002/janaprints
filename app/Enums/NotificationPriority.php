<?php

namespace App\Enums;

enum NotificationPriority: string
{
    case Low = 'low';
    case Normal = 'normal';
    case High = 'high';
    case Critical = 'critical';

    public function label(): string
    {
        return match ($this) {
            self::Low => __('Low'),
            self::Normal => __('Normal'),
            self::High => __('High'),
            self::Critical => __('Critical'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Low => 'bg-slate-100 text-slate-700',
            self::Normal => 'bg-blue-50 text-blue-800',
            self::High => 'bg-amber-50 text-amber-900',
            self::Critical => 'bg-red-50 text-red-800',
        };
    }
}
