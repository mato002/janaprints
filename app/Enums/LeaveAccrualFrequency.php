<?php

namespace App\Enums;

enum LeaveAccrualFrequency: string
{
    case Monthly = 'monthly';
    case Quarterly = 'quarterly';
    case Annual = 'annual';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::Monthly => __('Monthly'),
            self::Quarterly => __('Quarterly'),
            self::Annual => __('Annual'),
            self::Custom => __('Custom'),
        };
    }
}
