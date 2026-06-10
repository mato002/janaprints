<?php

namespace App\Enums;

enum CalibrationRuleStatus: string
{
    case Draft = 'draft';
    case PendingReview = 'pending_review';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Retired = 'retired';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::PendingReview => __('Pending review'),
            self::Approved => __('Approved'),
            self::Rejected => __('Rejected'),
            self::Retired => __('Retired'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Draft => 'bg-slate-100 text-slate-700',
            self::PendingReview => 'bg-amber-50 text-amber-900',
            self::Approved => 'bg-emerald-50 text-emerald-800',
            self::Rejected => 'bg-red-50 text-red-800',
            self::Retired => 'bg-slate-100 text-slate-500',
        };
    }
}
