<?php

namespace App\Enums;

enum IntegrationApiKeyEnvironment: string
{

    case Production = 'production';
    case Staging = 'staging';
    case Development = 'development';

    public function label(): string
    {
        return match ($this) {
            self::Production => __('Production'),
            self::Staging => __('Staging'),
            self::Development => __('Development'),
        };
    }
}
