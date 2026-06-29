<?php

namespace App\Enums;

enum ProductionJobCardStatus: string
{
    case Draft = 'draft';
    case Queued = 'queued';
    case InProduction = 'in_production';
    case QualityCheck = 'quality_check';
    case Completed = 'completed';
    case ReadyForDispatch = 'ready_for_dispatch';
    case OnHold = 'on_hold';
    case Cancelled = 'cancelled';
    case Rework = 'rework';
    case AwaitingCustomerApproval = 'awaiting_customer_approval';
    case Outsourced = 'outsourced';
    case Returned = 'returned';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::Queued => __('Queued'),
            self::InProduction => __('In production'),
            self::QualityCheck => __('Quality check'),
            self::Completed => __('Completed'),
            self::ReadyForDispatch => __('Ready for dispatch'),
            self::OnHold => __('On hold'),
            self::Cancelled => __('Cancelled'),
            self::Rework => __('Rework'),
            self::AwaitingCustomerApproval => __('Awaiting customer approval'),
            self::Outsourced => __('Outsourced'),
            self::Returned => __('Returned'),
        };
    }

    /**
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::Queued, self::Cancelled, self::OnHold],
            self::Queued => [self::Draft, self::InProduction, self::Outsourced, self::Cancelled, self::OnHold],
            self::InProduction => [self::QualityCheck, self::Outsourced, self::Cancelled, self::OnHold],
            self::Outsourced => [self::Returned, self::Cancelled, self::OnHold],
            self::Returned => [self::InProduction, self::QualityCheck, self::OnHold],
            self::QualityCheck => [self::ReadyForDispatch, self::Rework, self::AwaitingCustomerApproval, self::OnHold],
            self::AwaitingCustomerApproval => [self::ReadyForDispatch, self::Rework, self::OnHold],
            self::Rework => [self::InProduction, self::QualityCheck, self::Cancelled, self::OnHold],
            self::Completed => [self::ReadyForDispatch, self::OnHold],
            self::ReadyForDispatch => [],
            self::OnHold => [self::Queued, self::InProduction],
            self::Cancelled => [],
        };
    }

    public function canTransitionTo(self $status): bool
    {
        return in_array($status, $this->allowedTransitions(), true);
    }

    public function isEditable(): bool
    {
        return in_array($this, [self::Draft, self::Queued, self::OnHold], true);
    }
}
