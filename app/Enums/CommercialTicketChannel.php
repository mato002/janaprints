<?php

namespace App\Enums;

enum CommercialTicketChannel: string
{
    case Phone = 'phone';
    case Email = 'email';
    case WhatsApp = 'whatsapp';
    case WalkIn = 'walk_in';
    case Website = 'website';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Phone => __('Phone'),
            self::Email => __('Email'),
            self::WhatsApp => __('WhatsApp'),
            self::WalkIn => __('Walk-in'),
            self::Website => __('Website'),
            self::Other => __('Other'),
        };
    }
}
