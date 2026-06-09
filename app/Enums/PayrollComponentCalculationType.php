<?php

namespace App\Enums;

enum PayrollComponentCalculationType: string
{
    case Fixed = 'fixed';
    case Percentage = 'percentage';

    public function label(): string
    {
        return match ($this) {
            self::Fixed => __('Fixed Amount'),
            self::Percentage => __('Percentage Based'),
        };
    }
}
