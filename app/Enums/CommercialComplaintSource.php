<?php

namespace App\Enums;

enum CommercialComplaintSource: string
{
    case WalkIn = 'walk_in';
    case Phone = 'phone';
    case WhatsApp = 'whatsapp';
    case Email = 'email';
    case Website = 'website';
    case Staff = 'staff';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::WalkIn => __('Walk-in'),
            self::Phone => __('Phone'),
            self::WhatsApp => __('WhatsApp'),
            self::Email => __('Email'),
            self::Website => __('Website'),
            self::Staff => __('Staff'),
            self::Other => __('Other'),
        };
    }
}
