<?php

namespace App\Enums;

enum AssetCapitalizationReconciliationStatus: string
{
    case Balanced = 'balanced';
    case Variance = 'variance';
    case Critical = 'critical';

    public function label(): string
    {
        return match ($this) {
            self::Balanced => __('Balanced'),
            self::Variance => __('Variance'),
            self::Critical => __('Critical'),
        };
    }
}
