<?php

namespace App\Enums;

enum CoverageClass: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Full = 'full';
    case Unknown = 'unknown';

    public function label(): string
    {
        return match ($this) {
            self::Low => __('Low'),
            self::Medium => __('Medium'),
            self::High => __('High'),
            self::Full => __('Full'),
            self::Unknown => __('Unknown'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Low => 'bg-emerald-50 text-emerald-800',
            self::Medium => 'bg-sky-50 text-sky-800',
            self::High => 'bg-amber-50 text-amber-900',
            self::Full => 'bg-red-50 text-red-800',
            self::Unknown => 'bg-slate-100 text-slate-700',
        };
    }
}
