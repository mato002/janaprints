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

    /**
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::Queued, self::Cancelled, self::OnHold],
            self::Queued => [self::InProduction, self::Cancelled, self::OnHold],
            self::InProduction => [self::QualityCheck, self::Cancelled, self::OnHold],
            self::QualityCheck => [self::Completed, self::Rework, self::OnHold],
            self::Rework => [self::InProduction, self::Cancelled, self::OnHold],
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
