<?php

namespace App\Enums;

enum MaterialRequirementStatus: string
{
    case Planned = 'planned';
    case Reserved = 'reserved';
    case Partial = 'partial';
    case Fulfilled = 'fulfilled';
    case Shortfall = 'shortfall';

    public function label(): string
    {
        return match ($this) {
            self::Planned => __('Planned'),
            self::Reserved => __('Reserved'),
            self::Partial => __('Partially consumed'),
            self::Fulfilled => __('Fulfilled'),
            self::Shortfall => __('Shortfall'),
        };
    }

    public function isOpen(): bool
    {
        return $this !== self::Fulfilled;
    }
}
