<?php

namespace App\Enums;

enum IntegrationProviderStatus: string
{

    case Connected = 'connected';
    case Disconnected = 'disconnected';
    case Error = 'error';

    public function label(): string
    {
        return match ($this) {
            self::Connected => __('Connected'),
            self::Disconnected => __('Disconnected'),
            self::Error => __('Error'),
        };
    }
}
