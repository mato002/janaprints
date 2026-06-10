<?php

namespace App\Enums;

enum InventoryVelocityClass: string
{
    case FastMoving = 'fast_moving';
    case Normal = 'normal';
    case SlowMoving = 'slow_moving';
    case DeadStock = 'dead_stock';
    case NewItem = 'new_item';
    case NoData = 'no_data';

    public function label(): string
    {
        return match ($this) {
            self::FastMoving => __('Fast moving'),
            self::Normal => __('Normal'),
            self::SlowMoving => __('Slow moving'),
            self::DeadStock => __('Dead stock'),
            self::NewItem => __('New item'),
            self::NoData => __('No data'),
        };
    }
}
