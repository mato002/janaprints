<?php

namespace App\Enums;

enum ReorderAlertStatus: string
{
    case Open = 'open';
    case Acknowledged = 'acknowledged';
    case Resolved = 'resolved';

    public function label(): string
    {
        return match ($this) {
            self::Open => __('Open'),
            self::Acknowledged => __('Acknowledged'),
            self::Resolved => __('Resolved'),
        };
    }

    public function isActionable(): bool
    {
        return in_array($this, [self::Open, self::Acknowledged], true);
    }
}
