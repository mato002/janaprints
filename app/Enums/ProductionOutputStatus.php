<?php

namespace App\Enums;

enum ProductionOutputStatus: string
{
    case Draft = 'draft';
    case Posted = 'posted';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::Posted => __('Posted'),
            self::Cancelled => __('Cancelled'),
        };
    }

    public function isPosted(): bool
    {
        return $this === self::Posted;
    }
}
