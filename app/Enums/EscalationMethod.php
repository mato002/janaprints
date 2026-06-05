<?php

namespace App\Enums;

enum EscalationMethod: string
{
    case Reminder = 'reminder';
    case AutoEscalate = 'auto_escalate';

    public function label(): string
    {
        return match ($this) {
            self::Reminder => __('Reminder'),
            self::AutoEscalate => __('Auto Escalate'),
        };
    }
}
