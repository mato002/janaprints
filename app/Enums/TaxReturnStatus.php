<?php

namespace App\Enums;

enum TaxReturnStatus: string
{
    case Draft = 'draft';
    case Filed = 'filed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::Filed => __('Filed'),
        };
    }
}
