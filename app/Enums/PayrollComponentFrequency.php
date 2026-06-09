<?php

namespace App\Enums;

enum PayrollComponentFrequency: string
{
    case Recurring = 'recurring';
    case OneTime = 'one_time';

    public function label(): string
    {
        return match ($this) {
            self::Recurring => __('Recurring'),
            self::OneTime => __('One-Time'),
        };
    }
}
