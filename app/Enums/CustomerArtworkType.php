<?php

namespace App\Enums;

enum CustomerArtworkType: string
{
    case Layout = 'layout';
    case Logo = 'logo';
    case Template = 'template';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Layout => __('Layout'),
            self::Logo => __('Logo'),
            self::Template => __('Template'),
            self::Other => __('Other'),
        };
    }
}
