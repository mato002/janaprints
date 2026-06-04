<?php

namespace App\Enums;

enum CommunicationChannel: string
{
    case Sms = 'sms';
    case Email = 'email';
    case WhatsApp = 'whatsapp';
    case Notification = 'notification';

    public function label(): string
    {
        return match ($this) {
            self::Sms => __('SMS'),
            self::Email => __('Email'),
            self::WhatsApp => __('WhatsApp'),
            self::Notification => __('Notification'),
        };
    }
}
