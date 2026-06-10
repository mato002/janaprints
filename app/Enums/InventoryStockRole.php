<?php

namespace App\Enums;

enum InventoryStockRole: string
{
    case RawMaterial = 'raw_material';
    case Wip = 'wip';
    case FinishedGood = 'finished_good';
    case Consumable = 'consumable';
    case AssetSpare = 'asset_spare';
    case Packaging = 'packaging';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::RawMaterial => __('Raw material'),
            self::Wip => __('Work in progress'),
            self::FinishedGood => __('Finished good'),
            self::Consumable => __('Consumable'),
            self::AssetSpare => __('Asset spare'),
            self::Packaging => __('Packaging'),
            self::Other => __('Other'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::RawMaterial => 'bg-amber-50 text-amber-900',
            self::Wip => 'bg-sky-50 text-sky-900',
            self::FinishedGood => 'bg-emerald-50 text-emerald-900',
            self::Consumable => 'bg-violet-50 text-violet-900',
            self::AssetSpare => 'bg-slate-100 text-slate-700',
            self::Packaging => 'bg-orange-50 text-orange-900',
            self::Other => 'bg-slate-50 text-slate-600',
        };
    }
}
