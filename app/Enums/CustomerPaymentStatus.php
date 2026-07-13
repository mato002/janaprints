<?php

namespace App\Enums;

enum CustomerPaymentStatus: string
{
    case Draft = 'draft';
    case Posted = 'posted';
    case Refunded = 'refunded';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::Posted => __('Posted'),
            self::Refunded => __('Refunded'),
            self::Cancelled => __('Cancelled'),
        };
    }

    public function isEditable(): bool
    {
        return $this === self::Draft;
    }
}
