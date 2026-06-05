<?php

namespace App\Enums;

enum DowntimeImpactLevel: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Critical = 'critical';

    public function label(): string
    {
        return match ($this) {
            self::Low => __('Low'),
            self::Medium => __('Medium'),
            self::High => __('High'),
            self::Critical => __('Critical'),
        };
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::Low => 'neutral',
            self::Medium => 'warning',
            self::High => 'warning',
            self::Critical => 'danger',
        };
    }
}
