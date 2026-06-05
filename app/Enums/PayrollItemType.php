<?php

namespace App\Enums;

enum PayrollItemType: string
{
    case Allowance = 'allowance';
    case Deduction = 'deduction';

    public function label(): string
    {
        return match ($this) {
            self::Allowance => __('Allowance'),
            self::Deduction => __('Deduction'),
        };
    }
}
