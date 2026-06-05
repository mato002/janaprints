<?php

namespace App\Enums;

enum AssetPhysicalCondition: string
{
    case New = 'new';
    case Excellent = 'excellent';
    case Good = 'good';
    case Fair = 'fair';
    case NeedsRepair = 'needs_repair';
    case Damaged = 'damaged';
    case WrittenOff = 'written_off';

    public function label(): string
    {
        return match ($this) {
            self::New => __('New'),
            self::Excellent => __('Excellent'),
            self::Good => __('Good'),
            self::Fair => __('Fair'),
            self::NeedsRepair => __('Needs Repair'),
            self::Damaged => __('Damaged'),
            self::WrittenOff => __('Written Off'),
        };
    }
}
