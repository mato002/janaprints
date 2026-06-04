<?php

namespace App\Enums;

enum CustomerPaymentMethod: string
{
    case Cash = 'cash';
    case Bank = 'bank';
    case Mpesa = 'mpesa';

    public function label(): string
    {
        return match ($this) {
            self::Cash => __('Cash'),
            self::Bank => __('Bank transfer'),
            self::Mpesa => __('M-Pesa'),
        };
    }

    public function receiptAccountKey(): string
    {
        return match ($this) {
            self::Cash => 'cash',
            self::Bank, self::Mpesa => 'bank',
        };
    }
}
