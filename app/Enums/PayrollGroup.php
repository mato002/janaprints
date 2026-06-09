<?php

namespace App\Enums;

enum PayrollGroup: string
{
    case Main = 'main';
    case Management = 'management';
    case Casual = 'casual';
    case Contract = 'contract';

    public function label(): string
    {
        return match ($this) {
            self::Main => __('Main Payroll'),
            self::Management => __('Management'),
            self::Casual => __('Casual'),
            self::Contract => __('Contract'),
        };
    }
}
