<?php

namespace App\Enums;

enum PaymentFrequency: string
{
    case Monthly = 'monthly';
    case Biweekly = 'biweekly';
    case Weekly = 'weekly';

    public function label(): string
    {
        return match ($this) {
            self::Monthly => __('Monthly'),
            self::Biweekly => __('Bi-weekly'),
            self::Weekly => __('Weekly'),
        };
    }
}
