<?php

namespace App\Enums;

enum MachineAvailabilityState: string
{
    case Available = 'available';
    case LimitedCapacity = 'limited_capacity';
    case Unavailable = 'unavailable';

    public function label(): string
    {
        return match ($this) {
            self::Available => __('Available'),
            self::LimitedCapacity => __('Limited Capacity'),
            self::Unavailable => __('Unavailable'),
        };
    }
}
