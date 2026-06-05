<?php

namespace App\Enums;

enum ClearanceStatus: string
{
    case Pending = 'pending';
    case Cleared = 'cleared';
    case Waived = 'waived';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('Pending'),
            self::Cleared => __('Cleared'),
            self::Waived => __('Waived'),
        };
    }
}
