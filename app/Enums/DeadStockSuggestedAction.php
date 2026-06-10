<?php

namespace App\Enums;

enum DeadStockSuggestedAction: string
{
    case Promote = 'promote';
    case Discount = 'discount';
    case Bundle = 'bundle';
    case Inspect = 'inspect';
    case Transfer = 'transfer';
    case WriteDownReview = 'write_down_review';

    public function label(): string
    {
        return match ($this) {
            self::Promote => __('Promote'),
            self::Discount => __('Discount'),
            self::Bundle => __('Bundle'),
            self::Inspect => __('Inspect'),
            self::Transfer => __('Transfer'),
            self::WriteDownReview => __('Write-down review'),
        };
    }
}
