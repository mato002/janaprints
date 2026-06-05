<?php

namespace App\Enums;

enum AssetHealthBand: string
{
    case Excellent = 'excellent';
    case Good = 'good';
    case Fair = 'fair';
    case Poor = 'poor';
    case Critical = 'critical';

    public function label(): string
    {
        return match ($this) {
            self::Excellent => __('Excellent'),
            self::Good => __('Good'),
            self::Fair => __('Fair'),
            self::Poor => __('Poor'),
            self::Critical => __('Critical'),
        };
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::Excellent => 'success',
            self::Good => 'info',
            self::Fair => 'warning',
            self::Poor => 'danger',
            self::Critical => 'danger',
        };
    }

    public static function fromScore(int $score): self
    {
        return match (true) {
            $score >= 85 => self::Excellent,
            $score >= 70 => self::Good,
            $score >= 50 => self::Fair,
            $score >= 30 => self::Poor,
            default => self::Critical,
        };
    }
}
