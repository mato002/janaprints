<?php

namespace App\Enums;

enum PerformanceReviewCycle: string
{
    case Quarterly = 'quarterly';
    case SemiAnnual = 'semi_annual';
    case Annual = 'annual';

    public function label(): string
    {
        return match ($this) {
            self::Quarterly => __('Quarterly'),
            self::SemiAnnual => __('Semi Annual'),
            self::Annual => __('Annual'),
        };
    }
}
