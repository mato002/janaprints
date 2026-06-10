<?php

namespace App\Enums;

enum ForecastType: string
{
    case Revenue = 'revenue';
    case Profit = 'profit';
    case Capacity = 'capacity';
    case Demand = 'demand';
    case Customer = 'customer';
    case InventoryRisk = 'inventory_risk';
    case Machine = 'machine';

    public function label(): string
    {
        return match ($this) {
            self::Revenue => __('Revenue'),
            self::Profit => __('Profit'),
            self::Capacity => __('Capacity'),
            self::Demand => __('Demand'),
            self::Customer => __('Customer'),
            self::InventoryRisk => __('Inventory risk'),
            self::Machine => __('Machine'),
        };
    }
}
