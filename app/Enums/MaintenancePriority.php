<?php

namespace App\Enums;

enum MaintenancePriority: string
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

    public function badgeVariant(): string
    {
        return match ($this) {
            self::Low => 'neutral',
            self::Normal => 'success',
            self::High => 'warning',
            self::Critical => 'danger',
        };
    }

    public function isCritical(): bool
    {
        return $this === self::Critical;
    }
}
