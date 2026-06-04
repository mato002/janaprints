<?php

namespace App\Enums\Dispatch;

enum DeliveryNoteStatus: string
{
    case Draft = 'draft';
    case Dispatched = 'dispatched';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function isEditable(): bool
    {
        return $this === self::Draft;
    }

    public function isImmutable(): bool
    {
        return $this === self::Delivered;
    }

    public function canDispatch(): bool
    {
        return $this === self::Draft;
    }

    public function canDeliver(): bool
    {
        return $this === self::Dispatched;
    }

    public function canCancel(): bool
    {
        return in_array($this, [self::Draft, self::Dispatched], true);
    }

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::Dispatched => __('Dispatched'),
            self::Delivered => __('Delivered'),
            self::Cancelled => __('Cancelled'),
        };
    }
}
