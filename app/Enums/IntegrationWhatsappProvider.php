<?php

namespace App\Enums;

enum IntegrationWhatsappProvider: string
{
    case MetaCloud = 'meta_cloud';
    case Twilio = 'twilio';
    case AfricasTalking = 'africas_talking';
    case Http = 'http';

    public function label(): string
    {
        return match ($this) {
            self::MetaCloud => __('WhatsApp Business (Meta Cloud)'),
            self::Twilio => __('Twilio WhatsApp'),
            self::AfricasTalking => __("Africa's Talking WhatsApp"),
            self::Http => __('Custom HTTP Provider'),
        };
    }
}
