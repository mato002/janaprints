<?php

namespace App\Enums;

enum QualityCheckResult: string
{
    case Passed = 'passed';
    case Failed = 'failed';
    case ConditionalPass = 'conditional_pass';
    /** @deprecated Use Failed with rework fields */
    case ReworkRequired = 'rework_required';

    public function label(): string
    {
        return match ($this) {
            self::Passed => __('Pass'),
            self::Failed => __('Fail'),
            self::ConditionalPass => __('Conditional pass'),
            self::ReworkRequired => __('Rework required'),
        };
    }

    public function isBlocking(): bool
    {
        return in_array($this, [self::Failed, self::ReworkRequired, self::ConditionalPass], true);
    }
}
