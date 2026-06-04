<?php

namespace App\Enums;

enum WhatsappMessageDirection: string
{
    case Incoming = 'incoming';
    case Outgoing = 'outgoing';

    public function label(): string
    {
        return match ($this) {
            self::Incoming => __('Incoming'),
            self::Outgoing => __('Outgoing'),
        };
    }
}
