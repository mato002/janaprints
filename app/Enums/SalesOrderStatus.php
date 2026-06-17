<?php

namespace App\Enums;

enum SalesOrderStatus: string
{
    case Draft = 'draft';
    case Confirmed = 'confirmed';
    case ReadyForProduction = 'ready_for_production';
    case InProduction = 'in_production';
    case Completed = 'completed';
    case ReadyForDispatch = 'ready_for_dispatch';
    case Delivered = 'delivered';
    case Closed = 'closed';
    case Cancelled = 'cancelled';
    case OnHold = 'on_hold';

    /**
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::Confirmed, self::Cancelled, self::OnHold],
            self::Confirmed => [self::ReadyForProduction, self::Cancelled, self::OnHold],
            self::ReadyForProduction => [self::InProduction, self::Cancelled, self::OnHold],
            self::InProduction => [self::Completed, self::Cancelled, self::OnHold],
            self::Completed => [self::ReadyForDispatch, self::Delivered, self::OnHold],
            self::ReadyForDispatch => [self::Delivered, self::OnHold],
            self::Delivered => [self::Closed],
            self::OnHold => [self::Confirmed],
            self::Closed, self::Cancelled => [],
        };
    }

    public function canTransitionTo(self $status): bool
    {
        return in_array($status, $this->allowedTransitions(), true);
    }

    public function isEditable(): bool
    {
        return $this === self::Draft;
    }
}
