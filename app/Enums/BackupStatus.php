<?php

namespace App\Enums;

enum BackupStatus: string
{
    case Available = 'available';
    case Verified = 'verified';
    case Failed = 'failed';
    case Expired = 'expired';
    case Missing = 'missing';

    public function label(): string
    {
        return match ($this) {
            self::Available => __('Available'),
            self::Verified => __('Verified'),
            self::Failed => __('Failed'),
            self::Expired => __('Expired'),
            self::Missing => __('Missing'),
        };
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::Verified => 'success',
            self::Available => 'info',
            self::Failed => 'danger',
            self::Expired => 'warning',
            self::Missing => 'neutral',
        };
    }
}
