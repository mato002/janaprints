<?php

namespace App\Enums;

enum CustomerDepositRefundStatus: string
{
    case Posted = 'posted';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Posted => __('Posted'),
            self::Cancelled => __('Cancelled'),
        };
    }
}
