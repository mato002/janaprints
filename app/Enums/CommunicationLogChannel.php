<?php

namespace App\Enums;

enum CommunicationLogChannel: string
{
    case Sms = 'sms';
    case Email = 'email';
    case WhatsApp = 'whatsapp';
    case Notification = 'notification';
    case System = 'system';
    case MobilePush = 'mobile_push';
    case Portal = 'portal';

    public function label(): string
    {
        return match ($this) {
            self::Sms => __('SMS'),
            self::Email => __('Email'),
            self::WhatsApp => __('WhatsApp'),
            self::Notification => __('Notification'),
            self::System => __('System'),
            self::MobilePush => __('Mobile push'),
            self::Portal => __('Customer portal'),
        };
    }
}
