<?php

namespace App\Enums;

enum InboxSlaStatus: string
{
    case Green = 'green';
    case Amber = 'amber';
    case Red = 'red';

    public function label(): string
    {
        return match ($this) {
            self::Green => __('On track'),
            self::Amber => __('At risk'),
            self::Red => __('Overdue'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Green => 'bg-emerald-100 text-emerald-800',
            self::Amber => 'bg-amber-100 text-amber-800',
            self::Red => 'bg-red-100 text-red-800',
        };
    }

    public function borderClass(): string
    {
        return match ($this) {
            self::Green => 'border-l-emerald-500',
            self::Amber => 'border-l-amber-500',
            self::Red => 'border-l-red-500',
        };
    }
}
