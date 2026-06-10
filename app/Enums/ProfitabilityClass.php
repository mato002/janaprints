<?php

namespace App\Enums;

enum ProfitabilityClass: string
{
    case Excellent = 'excellent';
    case Good = 'good';
    case Average = 'average';
    case Weak = 'weak';
    case LossMaking = 'loss_making';
    case Unknown = 'unknown';

    public function label(): string
    {
        return match ($this) {
            self::Excellent => __('Excellent'),
            self::Good => __('Good'),
            self::Average => __('Average'),
            self::Weak => __('Weak'),
            self::LossMaking => __('Loss making'),
            self::Unknown => __('Unknown'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Excellent => 'bg-emerald-50 text-emerald-800',
            self::Good => 'bg-sky-50 text-sky-800',
            self::Average => 'bg-slate-100 text-slate-700',
            self::Weak => 'bg-amber-50 text-amber-900',
            self::LossMaking => 'bg-red-50 text-red-800',
            self::Unknown => 'bg-slate-100 text-slate-500',
        };
    }
}
