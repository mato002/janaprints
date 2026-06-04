<?php

namespace App\Enums;

enum AccountingPeriodStatus: string
{
    case Open = 'open';
    case Closed = 'closed';
    case Locked = 'locked';

    public function label(): string
    {
        return match ($this) {
            self::Open => __('Open'),
            self::Closed => __('Closed'),
            self::Locked => __('Locked'),
        };
    }

    public function canPost(): bool
    {
        return $this === self::Open;
    }

    public function isEditable(): bool
    {
        return $this !== self::Locked;
    }
}
