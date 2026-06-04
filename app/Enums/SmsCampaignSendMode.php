<?php

namespace App\Enums;

enum SmsCampaignSendMode: string
{
    case Immediate = 'immediate';
    case Scheduled = 'scheduled';

    public function label(): string
    {
        return match ($this) {
            self::Immediate => __('Immediate send'),
            self::Scheduled => __('Scheduled send'),
        };
    }
}
