<?php

namespace App\Enums;

enum DepreciationPostingStatus: string
{
    case Draft = 'draft';
    case Posted = 'posted';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::Posted => __('Posted'),
        };
    }
}
