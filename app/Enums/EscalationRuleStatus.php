<?php

namespace App\Enums;

enum EscalationRuleStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Inactive = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::Active => __('Active'),
            self::Inactive => __('Inactive'),
        };
    }

    public function isOperational(): bool
    {
        return $this === self::Active;
    }
}
