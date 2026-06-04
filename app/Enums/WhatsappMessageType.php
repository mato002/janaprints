<?php

namespace App\Enums;

enum WhatsappMessageType: string
{
    case Incoming = 'incoming';
    case Outgoing = 'outgoing';
    case Template = 'template';
    case Manual = 'manual';
    case System = 'system';

    public function label(): string
    {
        return match ($this) {
            self::Incoming => __('Incoming'),
            self::Outgoing => __('Outgoing'),
            self::Manual => __('Manual message'),
            self::Template => __('Template message'),
            self::System => __('System message'),
        };
    }
}
