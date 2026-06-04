<?php

namespace App\Enums;

enum EmailCampaignStatus: string
{
    case Draft = 'draft';
    case Scheduled = 'scheduled';
    case Queued = 'queued';
    case Sending = 'sending';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::Scheduled => __('Scheduled'),
            self::Queued => __('Queued'),
            self::Sending => __('Sending'),
            self::Completed => __('Completed'),
            self::Cancelled => __('Cancelled'),
            self::Failed => __('Failed'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Draft => 'bg-slate-100 text-slate-700',
            self::Scheduled => 'bg-blue-100 text-blue-800',
            self::Queued, self::Sending => 'bg-amber-100 text-amber-800',
            self::Completed => 'bg-emerald-100 text-emerald-800',
            self::Cancelled => 'bg-slate-100 text-slate-600',
            self::Failed => 'bg-red-100 text-red-800',
        };
    }
}
