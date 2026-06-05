<?php

namespace App\Enums;

enum PerformanceRating: string
{
    case Excellent = 'excellent';
    case Good = 'good';
    case Average = 'average';
    case Poor = 'poor';
    case Critical = 'critical';

    public function label(): string
    {
        return match ($this) {
            self::Excellent => __('Excellent'),
            self::Good => __('Good'),
            self::Average => __('Average'),
            self::Poor => __('Poor'),
            self::Critical => __('Critical'),
        };
    }

    public function score(): int
    {
        return match ($this) {
            self::Excellent => 100,
            self::Good => 80,
            self::Average => 60,
            self::Poor => 40,
            self::Critical => 20,
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Excellent => 'success',
            self::Good => 'info',
            self::Average => 'warning',
            self::Poor => 'danger',
            self::Critical => 'danger',
        };
    }
}
