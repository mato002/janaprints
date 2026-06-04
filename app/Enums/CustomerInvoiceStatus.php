<?php

namespace App\Enums;

enum CustomerInvoiceStatus: string
{
    case Draft = 'draft';
    case Approved = 'approved';
    case Posted = 'posted';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::Approved => __('Approved'),
            self::Posted => __('Posted'),
            self::Cancelled => __('Cancelled'),
        };
    }

    public function isEditable(): bool
    {
        return $this === self::Draft;
    }

    public function canTransitionTo(self $status): bool
    {
        return in_array($status, $this->allowedTransitions(), true);
    }

    /**
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::Approved, self::Cancelled],
            self::Approved => [self::Posted, self::Cancelled],
            self::Posted => [],
            self::Cancelled => [],
        };
    }
}
