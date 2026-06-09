<?php

namespace App\Enums;

enum RecruitmentPipelineStage: string
{
    case Applied = 'applied';
    case Screening = 'screening';
    case Interview = 'interview';
    case Shortlisted = 'shortlisted';
    case Offer = 'offer';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Hired = 'hired';

    public function label(): string
    {
        return match ($this) {
            self::Applied => __('Applied'),
            self::Screening => __('Screening'),
            self::Interview => __('Interview'),
            self::Shortlisted => __('Shortlisted'),
            self::Offer => __('Offer'),
            self::Accepted => __('Accepted'),
            self::Rejected => __('Rejected'),
            self::Hired => __('Hired'),
        };
    }

    /**
     * @return list<self>
     */
    public static function activeStages(): array
    {
        return [
            self::Applied,
            self::Screening,
            self::Interview,
            self::Shortlisted,
            self::Offer,
            self::Accepted,
        ];
    }
}
