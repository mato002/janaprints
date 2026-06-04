<?php

namespace App\Enums;

enum EmailCampaignType: string
{
    case Single = 'single';
    case Bulk = 'bulk';
    case Scheduled = 'scheduled';
    case Template = 'template';

    public function label(): string
    {
        return match ($this) {
            self::Single => __('Single email'),
            self::Bulk => __('Bulk email'),
            self::Scheduled => __('Scheduled email'),
            self::Template => __('Template email'),
        };
    }
}
