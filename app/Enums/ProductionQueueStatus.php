<?php

namespace App\Enums;

enum ProductionQueueStatus: string
{
    case Pending = 'pending';
    case Assigned = 'assigned';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    /**
     * Resolve queue workspace filter values (KPI labels or raw enum values).
     */
    public static function tryFromFilter(?string $value): ?self
    {
        if ($value === null || $value === '') {
            return null;
        }

        return match ($value) {
            'waiting' => self::Pending,
            'ready' => self::Assigned,
            'blocked' => null,
            default => self::tryFrom($value),
        };
    }
}
