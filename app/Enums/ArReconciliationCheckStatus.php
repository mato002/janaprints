<?php

namespace App\Enums;

enum ArReconciliationCheckStatus: string
{
    case Matched = 'matched';
    case Variance = 'variance';

    public function label(): string
    {
        return match ($this) {
            self::Matched => __('Matched'),
            self::Variance => __('Variance'),
        };
    }
}
