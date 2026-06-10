<?php

namespace App\Enums;

enum AdvisorSeverity: string
{
    case Info = 'info';
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Critical = 'critical';

    public function label(): string
    {
        return match ($this) {
            self::Info => __('Info'),
            self::Low => __('Low'),
            self::Medium => __('Medium'),
            self::High => __('High'),
            self::Critical => __('Critical'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Info => 'bg-slate-100 text-slate-700',
            self::Low => 'bg-sky-50 text-sky-800',
            self::Medium => 'bg-amber-50 text-amber-900',
            self::High => 'bg-orange-50 text-orange-900',
            self::Critical => 'bg-red-50 text-red-800',
        };
    }
}
