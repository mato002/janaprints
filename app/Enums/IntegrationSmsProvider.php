<?php

namespace App\Enums;

enum IntegrationSmsProvider: string
{

    case Onfon = 'onfon';
    case AfricasTalking = 'africas_talking';
    case Twilio = 'twilio';
    case Http = 'http';

    public function label(): string
    {
        return match ($this) {
            self::Onfon => __('Onfon'),
            self::AfricasTalking => __("Africa's Talking"),
            self::Twilio => __('Twilio'),
            self::Http => __('Generic HTTP Provider'),
        };
    }
}
