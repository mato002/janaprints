<?php

namespace App\Enums;

enum JournalStatus: string
{
    case Draft = 'draft';
    case Posted = 'posted';
    case Reversed = 'reversed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::Posted => __('Posted'),
            self::Reversed => __('Reversed'),
        };
    }

    public function isEditable(): bool
    {
        return $this === self::Draft;
    }
}
