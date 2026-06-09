<?php

namespace App\Enums;

enum CommercialHandoffAttentionLevel: string
{
    case Critical = 'critical';
    case High = 'high';
    case Medium = 'medium';
    case Low = 'low';

    public function label(): string
    {
        return match ($this) {
            self::Critical => __('Critical'),
            self::High => __('High'),
            self::Medium => __('Medium'),
            self::Low => __('Low'),
        };
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::Critical => 'danger',
            self::High => 'warning',
            self::Medium => 'info',
            self::Low => 'neutral',
        };
    }

    public function rank(): int
    {
        return match ($this) {
            self::Critical => 4,
            self::High => 3,
            self::Medium => 2,
            self::Low => 1,
        };
    }

    public static function fromAgeAndValue(int $ageDays, float $value): self
    {
        if ($ageDays >= 21 || ($ageDays >= 14 && $value >= 50_000)) {
            return self::Critical;
        }

        if ($ageDays >= 14 || ($ageDays >= 7 && $value >= 25_000)) {
            return self::High;
        }

        if ($ageDays >= 3 || $value >= 10_000) {
            return self::Medium;
        }

        return self::Low;
    }
}
