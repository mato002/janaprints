<?php

namespace App\Enums;

enum AssetWarrantyStatus: string
{
    case Active = 'active';
    case Expired = 'expired';
    case Void = 'void';
    case Claimed = 'claimed';

    public function label(): string
    {
        return match ($this) {
            self::Active => __('Active'),
            self::Expired => __('Expired'),
            self::Void => __('Void'),
            self::Claimed => __('Claimed'),
        };
    }
}
