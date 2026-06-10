<?php

namespace App\Enums;

enum OverheadRecommendationStatus: string
{
    case Draft = 'draft';
    case Recommended = 'recommended';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Applied = 'applied';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::Recommended => __('Recommended'),
            self::Rejected => __('Rejected'),
            self::Approved => __('Approved'),
            self::Applied => __('Applied'),
        };
    }
}
