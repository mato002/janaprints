<?php

namespace App\Enums;

enum JobCardRouteStepStatus: string
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Skipped = 'skipped';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('Pending'),
            self::InProgress => __('In Progress'),
            self::Completed => __('Completed'),
            self::Skipped => __('Skipped'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'bg-slate-100 text-slate-700',
            self::InProgress => 'bg-blue-100 text-blue-800',
            self::Completed => 'bg-emerald-100 text-emerald-800',
            self::Skipped => 'bg-amber-100 text-amber-800',
        };
    }
}
