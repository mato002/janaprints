<?php

namespace App\Enums;

enum CommercialTicketPriority: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Critical = 'critical';

    public function label(): string
    {
        return match ($this) {
            self::Low => __('Low'),
            self::Medium => __('Medium'),
            self::High => __('High'),
            self::Critical => __('Critical'),
        };
    }

    public function defaultDueHours(): int
    {
        return match ($this) {
            self::Low => 72,
            self::Medium => 48,
            self::High => 24,
            self::Critical => 8,
        };
    }
}
