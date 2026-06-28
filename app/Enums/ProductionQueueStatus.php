<?php

namespace App\Enums;

enum ProductionQueueStatus: string
{
    case Waiting = 'waiting';
    case Queued = 'queued';
    case Assigned = 'assigned';
    case InProgress = 'in_progress';
    case Paused = 'paused';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Waiting => __('Waiting'),
            self::Queued => __('Queued'),
            self::Assigned => __('Assigned'),
            self::InProgress => __('In Progress'),
            self::Paused => __('Paused'),
            self::Completed => __('Completed'),
            self::Cancelled => __('Cancelled'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Waiting => 'bg-slate-100 text-slate-700',
            self::Queued => 'bg-indigo-100 text-indigo-800',
            self::Assigned => 'bg-amber-100 text-amber-800',
            self::InProgress => 'bg-blue-100 text-blue-800',
            self::Paused => 'bg-orange-100 text-orange-800',
            self::Completed => 'bg-emerald-100 text-emerald-800',
            self::Cancelled => 'bg-red-100 text-red-800',
        };
    }

    /**
     * @return list<self>
     */
    public static function activeStatuses(): array
    {
        return [
            self::Waiting,
            self::Queued,
            self::Assigned,
            self::InProgress,
            self::Paused,
        ];
    }

    /**
     * Resolve queue workspace filter values (KPI labels or raw enum values).
     */
    public static function tryFromFilter(?string $value): ?self
    {
        if ($value === null || $value === '') {
            return null;
        }

        return match ($value) {
            'pending' => self::Waiting,
            'ready' => self::Assigned,
            'blocked' => null,
            default => self::tryFrom($value),
        };
    }
}
