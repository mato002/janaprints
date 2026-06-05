<?php

namespace App\Enums;

enum PayrollRunStatus: string
{
    case Draft = 'draft';
    case Calculated = 'calculated';
    case Approved = 'approved';
    case Posted = 'posted';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::Calculated => __('Calculated'),
            self::Approved => __('Approved'),
            self::Posted => __('Posted'),
            self::Cancelled => __('Cancelled'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Draft => 'neutral',
            self::Calculated => 'info',
            self::Approved => 'warning',
            self::Posted => 'success',
            self::Cancelled => 'neutral',
        };
    }
}
