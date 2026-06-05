<?php

namespace App\Enums;

enum AssetWriteOffReason: string
{
    case Damaged = 'damaged';
    case Lost = 'lost';
    case Obsolete = 'obsolete';
    case Destroyed = 'destroyed';

    public function label(): string
    {
        return match ($this) {
            self::Damaged => __('Damaged'),
            self::Lost => __('Lost'),
            self::Obsolete => __('Obsolete'),
            self::Destroyed => __('Destroyed'),
        };
    }
}
