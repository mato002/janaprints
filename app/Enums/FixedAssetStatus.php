<?php

namespace App\Enums;

enum FixedAssetStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Idle = 'idle';
    case UnderMaintenance = 'under_maintenance';
    case Disposed = 'disposed';
    case Retired = 'retired';
    case WrittenOff = 'written_off';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::Active => __('Active'),
            self::Idle => __('Idle'),
            self::UnderMaintenance => __('Under Maintenance'),
            self::Disposed => __('Disposed'),
            self::Retired => __('Retired'),
            self::WrittenOff => __('Written Off'),
        };
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::Draft => 'neutral',
            self::Active => 'success',
            self::Idle => 'warning',
            self::UnderMaintenance => 'warning',
            self::Disposed, self::Retired, self::WrittenOff => 'neutral',
        };
    }
}
