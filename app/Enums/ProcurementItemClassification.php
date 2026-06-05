<?php

namespace App\Enums;

enum ProcurementItemClassification: string
{
    case InventoryItem = 'inventory_item';
    case Consumable = 'consumable';
    case Service = 'service';
    case FixedAsset = 'fixed_asset';
    case LeaseAsset = 'lease_asset';
    case MixedAssetBundle = 'mixed_asset_bundle';

    public function label(): string
    {
        return match ($this) {
            self::InventoryItem => __('Inventory Item'),
            self::Consumable => __('Consumable'),
            self::Service => __('Service'),
            self::FixedAsset => __('Fixed Asset'),
            self::LeaseAsset => __('Lease Asset'),
            self::MixedAssetBundle => __('Mixed Asset Bundle'),
        };
    }

    public function isCapitalizable(): bool
    {
        return in_array($this, [self::FixedAsset, self::LeaseAsset, self::MixedAssetBundle], true);
    }

    public function requiresInventory(): bool
    {
        return in_array($this, [self::InventoryItem, self::Consumable], true);
    }
}
