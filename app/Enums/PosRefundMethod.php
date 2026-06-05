<?php

namespace App\Enums;

enum PosRefundMethod: string
{
    case Cash = 'cash';
    case Mpesa = 'mpesa';
    case Card = 'card';
    case StoreCredit = 'store_credit';
    case NoRefund = 'no_refund';

    public function label(): string
    {
        return match ($this) {
            self::Cash => __('Cash'),
            self::Mpesa => __('M-Pesa'),
            self::Card => __('Card'),
            self::StoreCredit => __('Store Credit'),
            self::NoRefund => __('No Refund'),
        };
    }
}
