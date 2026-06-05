<?php

namespace App\Enums;

enum AssetReturnCondition: string
{
    case Excellent = 'excellent';
    case Good = 'good';
    case Fair = 'fair';
    case Damaged = 'damaged';
    case Lost = 'lost';
    case RequiresReview = 'requires_review';

    public function label(): string
    {
        return match ($this) {
            self::Excellent => __('Excellent'),
            self::Good => __('Good'),
            self::Fair => __('Fair'),
            self::Damaged => __('Damaged'),
            self::Lost => __('Lost'),
            self::RequiresReview => __('Requires Review'),
        };
    }
}
