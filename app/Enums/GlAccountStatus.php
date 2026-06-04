<?php

namespace App\Enums;

enum GlAccountStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Locked = 'locked';

    public function label(): string
    {
        return match ($this) {
            self::Active => __('Active'),
            self::Inactive => __('Inactive'),
            self::Locked => __('Locked'),
        };
    }

    public function isEditable(): bool
    {
        return $this !== self::Locked;
    }
}
