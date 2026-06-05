<?php

namespace App\Enums;

enum CapitalizationCandidateStatus: string
{
    case Pending = 'pending';
    case Ready = 'ready';
    case Capitalized = 'capitalized';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('Pending'),
            self::Ready => __('Ready'),
            self::Capitalized => __('Capitalized'),
            self::Rejected => __('Rejected'),
            self::Cancelled => __('Cancelled'),
        };
    }
}
