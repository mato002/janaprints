<?php

namespace App\Enums;

enum EstimateVarianceClass: string
{
    case Accurate = 'accurate';
    case MinorVariance = 'minor_variance';
    case ModerateVariance = 'moderate_variance';
    case MajorVariance = 'major_variance';
    case Unreliable = 'unreliable';
    case Unknown = 'unknown';

    public function label(): string
    {
        return match ($this) {
            self::Accurate => __('Accurate'),
            self::MinorVariance => __('Minor variance'),
            self::ModerateVariance => __('Moderate variance'),
            self::MajorVariance => __('Major variance'),
            self::Unreliable => __('Unreliable'),
            self::Unknown => __('Unknown'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Accurate => 'bg-emerald-50 text-emerald-800',
            self::MinorVariance => 'bg-sky-50 text-sky-800',
            self::ModerateVariance => 'bg-amber-50 text-amber-900',
            self::MajorVariance => 'bg-orange-50 text-orange-800',
            self::Unreliable => 'bg-red-50 text-red-800',
            self::Unknown => 'bg-slate-100 text-slate-600',
        };
    }
}
