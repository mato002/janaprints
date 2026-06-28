<?php

namespace App\Enums;

enum SalesOrderBillingType: string
{
    case Deposit50 = 'deposit_50';
    case Advance100 = 'advance_100';
    case Net30 = 'net_30';

    public function label(): string
    {
        return match ($this) {
            self::Deposit50 => __('50% Deposit'),
            self::Advance100 => __('100% Advance'),
            self::Net30 => __('Net 30 Days'),
        };
    }

    public function defaultPaymentTermsDays(): int
    {
        return match ($this) {
            self::Deposit50 => 30,
            self::Advance100 => 0,
            self::Net30 => 30,
        };
    }

    public function depositPercent(): ?float
    {
        return match ($this) {
            self::Deposit50 => 50.0,
            self::Advance100 => 100.0,
            self::Net30 => null,
        };
    }
}
