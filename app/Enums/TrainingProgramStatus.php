<?php

namespace App\Enums;

enum TrainingProgramStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Completed = 'completed';
    case Archived = 'archived';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::Active => __('Active'),
            self::Completed => __('Completed'),
            self::Archived => __('Archived'),
            self::Cancelled => __('Cancelled'),
        };
    }

    public function isAssignable(): bool
    {
        return $this === self::Active;
    }
}
