<?php

namespace App\Enums;

enum FulfilmentMethod: string
{
    case Collection = 'collection';
    case Delivery = 'delivery';

    public function label(): string
    {
        return match ($this) {
            self::Collection => __('Collection'),
            self::Delivery => __('Delivery'),
        };
    }
}
