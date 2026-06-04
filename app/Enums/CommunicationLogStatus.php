<?php

namespace App\Enums;

enum CommunicationLogStatus: string
{
    case Draft = 'draft';
    case Queued = 'queued';
    case Sending = 'sending';
    case Sent = 'sent';
    case Delivered = 'delivered';
    case Read = 'read';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::Queued => __('Queued'),
            self::Sending => __('Sending'),
            self::Sent => __('Sent'),
            self::Delivered => __('Delivered'),
            self::Read => __('Read'),
            self::Failed => __('Failed'),
            self::Cancelled => __('Cancelled'),
            self::Archived => __('Archived'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Delivered, self::Read, self::Sent => 'bg-emerald-50 text-emerald-800',
            self::Failed => 'bg-red-50 text-red-800',
            self::Queued, self::Sending => 'bg-blue-50 text-blue-800',
            self::Cancelled, self::Archived => 'bg-slate-100 text-slate-600',
            default => 'bg-amber-50 text-amber-900',
        };
    }
}
