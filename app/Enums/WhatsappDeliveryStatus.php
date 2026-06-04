<?php

namespace App\Enums;

enum WhatsappDeliveryStatus: string
{
    case Queued = 'queued';
    case Sent = 'sent';
    case Delivered = 'delivered';
    case Read = 'read';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Queued => __('Queued'),
            self::Sent => __('Sent'),
            self::Delivered => __('Delivered'),
            self::Read => __('Read'),
            self::Failed => __('Failed'),
            self::Cancelled => __('Cancelled'),
            self::Expired => __('Expired'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Queued => 'bg-slate-100 text-slate-700',
            self::Sent => 'bg-blue-100 text-blue-800',
            self::Delivered => 'bg-emerald-100 text-emerald-800',
            self::Read => 'bg-emerald-100 text-emerald-800',
            self::Failed => 'bg-red-100 text-red-800',
            self::Cancelled => 'bg-amber-100 text-amber-800',
            self::Expired => 'bg-amber-100 text-amber-800',
        };
    }
}
