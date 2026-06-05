<?php

namespace App\Enums;

enum DocumentModule: string
{
    case Commercial = 'commercial';
    case Production = 'production';
    case SupplyChain = 'supply_chain';
    case Inventory = 'inventory';
    case Accounting = 'accounting';
    case Hr = 'hr';
    case Communications = 'communications';

    public function label(): string
    {
        return match ($this) {
            self::Commercial => 'Commercial',
            self::Production => 'Production',
            self::SupplyChain => 'Supply Chain',
            self::Inventory => 'Inventory',
            self::Accounting => 'Accounting',
            self::Hr => 'HR',
            self::Communications => 'Communications',
        };
    }
}
