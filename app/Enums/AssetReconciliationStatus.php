<?php

namespace App\Enums;

enum AssetReconciliationStatus: string
{
    case Draft = 'draft';
    case Reconciled = 'reconciled';
    case VarianceDetected = 'variance_detected';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::Reconciled => __('Reconciled'),
            self::VarianceDetected => __('Variance Detected'),
        };
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::Draft => 'neutral',
            self::Reconciled => 'success',
            self::VarianceDetected => 'warning',
        };
    }
}
