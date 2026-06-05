<?php

namespace App\Enums;

enum PosReturnType: string
{
    case FullReturn = 'full_return';
    case PartialReturn = 'partial_return';
    case DamagedItem = 'damaged_item';
    case WrongItem = 'wrong_item';
    case CustomerCancellation = 'customer_cancellation';
    case PricingError = 'pricing_error';

    public function label(): string
    {
        return match ($this) {
            self::FullReturn => __('Full Return'),
            self::PartialReturn => __('Partial Return'),
            self::DamagedItem => __('Damaged Item'),
            self::WrongItem => __('Wrong Item'),
            self::CustomerCancellation => __('Customer Cancellation'),
            self::PricingError => __('Pricing Error'),
        };
    }
}
