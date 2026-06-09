<?php

namespace App\Enums;

enum VacancyStatus: string
{
    case Draft = 'draft';
    case Open = 'open';
    case Closed = 'closed';
    case Filled = 'filled';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::Open => __('Open'),
            self::Closed => __('Closed'),
            self::Filled => __('Filled'),
            self::Cancelled => __('Cancelled'),
        };
    }

    public function acceptsApplications(): bool
    {
        return $this === self::Open;
    }
}
