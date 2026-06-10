<?php

namespace App\Enums;

enum AdvisorRecommendationType: string
{
    case Quotation = 'quotation';
    case Artwork = 'artwork';
    case Machine = 'machine';
    case Inventory = 'inventory';
    case Profitability = 'profitability';
    case Customer = 'customer';
    case Forecast = 'forecast';
    case Production = 'production';

    public function label(): string
    {
        return match ($this) {
            self::Quotation => __('Quotation'),
            self::Artwork => __('Artwork'),
            self::Machine => __('Machine'),
            self::Inventory => __('Inventory'),
            self::Profitability => __('Profitability'),
            self::Customer => __('Customer'),
            self::Forecast => __('Forecast'),
            self::Production => __('Production'),
        };
    }
}
