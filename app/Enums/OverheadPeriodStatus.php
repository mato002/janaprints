<?php

namespace App\Enums;

enum OverheadPeriodStatus: string
{
    case Draft = 'draft';
    case Captured = 'captured';
    case Analysed = 'analysed';
    case Approved = 'approved';
    case Locked = 'locked';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::Captured => __('Captured'),
            self::Analysed => __('Analysed'),
            self::Approved => __('Approved'),
            self::Locked => __('Locked'),
        };
    }

    public function isEditable(): bool
    {
        return ! in_array($this, [self::Approved, self::Locked], true);
    }
}
