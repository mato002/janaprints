<?php

namespace App\Enums;

enum InterviewRecommendation: string
{
    case Hire = 'hire';
    case Reject = 'reject';
    case Hold = 'hold';

    public function label(): string
    {
        return match ($this) {
            self::Hire => __('Hire'),
            self::Reject => __('Reject'),
            self::Hold => __('Hold'),
        };
    }
}
