<?php

namespace App\Enums;

enum MaintenanceFrequencyType: string
{
    case Daily = 'daily';
    case Weekly = 'weekly';
    case Monthly = 'monthly';
    case Quarterly = 'quarterly';
    case SemiAnnual = 'semi_annual';
    case Annual = 'annual';
    case MeterBased = 'meter_based';

    public function label(): string
    {
        return match ($this) {
            self::Daily => __('Daily'),
            self::Weekly => __('Weekly'),
            self::Monthly => __('Monthly'),
            self::Quarterly => __('Quarterly'),
            self::SemiAnnual => __('Semi-Annual'),
            self::Annual => __('Annual'),
            self::MeterBased => __('Meter-Based'),
        };
    }
}
