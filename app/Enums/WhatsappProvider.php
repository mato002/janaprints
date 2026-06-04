<?php

namespace App\Enums;

enum WhatsappProvider: string
{
    case Unconfigured = 'unconfigured';
    case MetaCloud = 'meta_cloud';
    case Twilio = 'twilio';
    case AfricasTalking = 'africas_talking';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::Unconfigured => __('Not configured'),
            self::MetaCloud => __('Meta WhatsApp Cloud'),
            self::Twilio => __('Twilio WhatsApp'),
            self::AfricasTalking => __('Africa\'s Talking'),
            self::Custom => __('Custom provider'),
        };
    }
}
