<?php

namespace App\Enums;

enum AdvisorRecommendationStatus: string
{
    case Open = 'open';
    case Acknowledged = 'acknowledged';
    case Dismissed = 'dismissed';

    public function label(): string
    {
        return match ($this) {
            self::Open => __('Open'),
            self::Acknowledged => __('Acknowledged'),
            self::Dismissed => __('Dismissed'),
        };
    }
}
