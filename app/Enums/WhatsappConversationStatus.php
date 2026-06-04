<?php

namespace App\Enums;

enum WhatsappConversationStatus: string
{
    case Open = 'open';
    case Closed = 'closed';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Open => __('Open'),
            self::Closed => __('Closed'),
            self::Archived => __('Archived'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Open => 'bg-emerald-100 text-emerald-800',
            self::Closed => 'bg-slate-100 text-slate-700',
            self::Archived => 'bg-amber-100 text-amber-800',
        };
    }
}
