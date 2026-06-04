<?php

namespace App\Enums;

enum SmsDeliveryStatus: string
{
    case Queued = 'queued';
    case Sent = 'sent';
    case Delivered = 'delivered';
    case Failed = 'failed';
    case Rejected = 'rejected';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Queued => __('Queued'),
            self::Sent => __('Sent'),
            self::Delivered => __('Delivered'),
            self::Failed => __('Failed'),
            self::Rejected => __('Rejected'),
            self::Expired => __('Expired'),
        };
    }
}
