<?php

namespace App\Enums;

enum AssetAcquisitionSource: string
{
    case Manual = 'manual';
    case Procurement = 'procurement';

    public function label(): string
    {
        return match ($this) {
            self::Manual => __('Manual Entry'),
            self::Procurement => __('Procurement'),
        };
    }
}
