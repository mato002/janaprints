<?php

namespace App\Enums;

enum EmailDeliveryStatus: string
{
    case Draft = 'draft';
    case Queued = 'queued';
    case Sending = 'sending';
    case Sent = 'sent';
    case Delivered = 'delivered';
    case Opened = 'opened';
    case Clicked = 'clicked';
    case Failed = 'failed';
    case Bounced = 'bounced';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::Queued => __('Queued'),
            self::Sending => __('Sending'),
            self::Sent => __('Sent'),
            self::Delivered => __('Delivered'),
            self::Opened => __('Opened'),
            self::Clicked => __('Clicked'),
            self::Failed => __('Failed'),
            self::Bounced => __('Bounced'),
            self::Cancelled => __('Cancelled'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Draft => 'bg-slate-100 text-slate-700',
            self::Queued, self::Sending => 'bg-blue-100 text-blue-800',
            self::Sent, self::Delivered => 'bg-emerald-100 text-emerald-800',
            self::Opened, self::Clicked => 'bg-emerald-100 text-emerald-800',
            self::Failed, self::Bounced => 'bg-red-100 text-red-800',
            self::Cancelled => 'bg-amber-100 text-amber-800',
        };
    }
}
