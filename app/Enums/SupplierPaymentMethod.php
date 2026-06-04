<?php

namespace App\Enums;

enum SupplierPaymentMethod: string
{
    case Cash = 'cash';
    case Bank = 'bank';

    public function label(): string
    {
        return match ($this) {
            self::Cash => __('Cash'),
            self::Bank => __('Bank transfer'),
        };
    }

    public function paymentAccountKey(): string
    {
        return match ($this) {
            self::Cash => 'cash',
            self::Bank => 'bank',
        };
    }
}
