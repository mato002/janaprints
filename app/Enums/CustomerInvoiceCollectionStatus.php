<?php

namespace App\Enums;

enum CustomerInvoiceCollectionStatus: string
{
    case Pending = 'pending';
    case Unpaid = 'unpaid';
    case Partial = 'partial';
    case Paid = 'paid';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('Pending'),
            self::Unpaid => __('Unpaid'),
            self::Partial => __('Partial'),
            self::Paid => __('Paid'),
            self::Cancelled => __('Cancelled'),
        };
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::Pending => 'info',
            self::Unpaid => 'warning',
            self::Partial => 'accent',
            self::Paid => 'success',
            self::Cancelled => 'neutral',
        };
    }
}
