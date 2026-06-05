<?php

namespace App\Enums;

enum IntegrationWebhookStatus: string
{

    case Active = 'active';
    case Disabled = 'disabled';

    public function label(): string
    {
        return match ($this) {
            self::Active => __('Active'),
            self::Disabled => __('Disabled'),
        };
    }
}
