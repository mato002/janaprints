<?php

namespace App\Enums;

enum EscalationEventType: string
{
    case ReminderSent = 'reminder_sent';
    case Escalated = 'escalated';

    public function label(): string
    {
        return match ($this) {
            self::ReminderSent => __('Reminder Sent'),
            self::Escalated => __('Escalated'),
        };
    }
}
