<?php

namespace App\Enums;

enum FulfilmentStatus: string
{
    case Pending = 'pending';
    case ReadyForCollection = 'ready_for_collection';
    case Collected = 'collected';
    case Dispatched = 'dispatched';
    case Delivered = 'delivered';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('Pending'),
            self::ReadyForCollection => __('Ready for collection'),
            self::Collected => __('Collected'),
            self::Dispatched => __('Dispatched'),
            self::Delivered => __('Delivered'),
        };
    }

    public function isComplete(): bool
    {
        return in_array($this, [self::Collected, self::Delivered], true);
    }
}
