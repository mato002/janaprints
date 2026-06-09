<?php

namespace App\Enums;

enum JobCostReviewStatus: string
{
    case None = 'none';
    case RequiresReview = 'requires_review';
    case Approved = 'approved';

    public function label(): string
    {
        return match ($this) {
            self::None => __('None'),
            self::RequiresReview => __('Requires cost review'),
            self::Approved => __('Approved'),
        };
    }
}
