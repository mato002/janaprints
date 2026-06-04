<?php

namespace App\Support\Inventory;

use App\Enums\InventoryCostingMethod;
use App\Models\Company;

class CompanyCostingSettings
{
    public static function costingMethod(Company $company): InventoryCostingMethod
    {
        $method = $company->settings_json['inventory_costing_method'] ?? InventoryCostingMethod::Fifo->value;

        return InventoryCostingMethod::tryFrom($method) ?? InventoryCostingMethod::Fifo;
    }

    public static function setCostingMethod(Company $company, InventoryCostingMethod $method): void
    {
        $settings = $company->settings_json ?? [];
        $settings['inventory_costing_method'] = $method->value;
        $company->update(['settings_json' => $settings]);
    }
}
