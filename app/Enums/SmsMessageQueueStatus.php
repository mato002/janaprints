<?php

namespace App\Enums;

enum SmsMessageQueueStatus: string
{
    case Queued = 'queued';
    case Processing = 'processing';
    case Sent = 'sent';
    case Failed = 'failed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Queued => __('Queued'),
            self::Processing => __('Processing'),
            self::Sent => __('Sent'),
            self::Failed => __('Failed'),
            self::Cancelled => __('Cancelled'),
        };
    }
}
