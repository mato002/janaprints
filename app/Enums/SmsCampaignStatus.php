<?php

namespace App\Enums;

enum SmsCampaignStatus: string
{
    case Draft = 'draft';
    case Queued = 'queued';
    case Sending = 'sending';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::Queued => __('Queued'),
            self::Sending => __('Sending'),
            self::Completed => __('Completed'),
            self::Cancelled => __('Cancelled'),
        };
    }

    public function canEdit(): bool
    {
        return $this === self::Draft;
    }

    public function canQueue(): bool
    {
        return $this === self::Draft;
    }

    public function canCancel(): bool
    {
        return in_array($this, [self::Draft, self::Queued, self::Sending], true);
    }
}
