<?php

namespace App\Enums;

enum ReplenishmentRecommendation: string
{
    case Transfer = 'transfer';
    case Purchase = 'purchase';

    public function label(): string
    {
        return match ($this) {
            self::Transfer => __('Transfer'),
            self::Purchase => __('Purchase'),
        };
    }
}
