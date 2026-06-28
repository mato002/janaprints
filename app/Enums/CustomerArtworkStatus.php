<?php

namespace App\Enums;

enum CustomerArtworkStatus: string
{
    case Active = 'active';
    case Archived = 'archived';
    case Superseded = 'superseded';

    public function label(): string
    {
        return match ($this) {
            self::Active => __('Active'),
            self::Archived => __('Archived'),
            self::Superseded => __('Superseded'),
        };
    }
}
