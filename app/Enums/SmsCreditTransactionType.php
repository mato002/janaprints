<?php

namespace App\Enums;

enum SmsCreditTransactionType: string
{
    case Opening = 'opening';
    case Purchase = 'purchase';
    case Usage = 'usage';
    case Adjustment = 'adjustment';
    case Refund = 'refund';

    public function label(): string
    {
        return match ($this) {
            self::Opening => __('Opening balance'),
            self::Purchase => __('Credits purchased'),
            self::Usage => __('Credits used'),
            self::Adjustment => __('Adjustment'),
            self::Refund => __('Refund'),
        };
    }
}
