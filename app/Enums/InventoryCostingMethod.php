<?php

namespace App\Enums;

enum InventoryCostingMethod: string
{
    case Fifo = 'fifo';
    case WeightedAverage = 'weighted_average';

    public function label(): string
    {
        return match ($this) {
            self::Fifo => __('FIFO'),
            self::WeightedAverage => __('Weighted average'),
        };
    }
}
