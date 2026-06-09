<?php

namespace App\Enums;

enum AssetAcquisitionAccountingStatus: string
{
    case NotPosted = 'not_posted';
    case Posted = 'posted';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::NotPosted => __('Not Posted'),
            self::Posted => __('Posted'),
            self::Failed => __('Failed'),
        };
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::NotPosted => 'warning',
            self::Posted => 'success',
            self::Failed => 'danger',
        };
    }
}
