<?php

namespace App\Enums;

enum CustomerType: string
{
    case Individual = 'individual';
    case Corporate = 'corporate';

    public function label(): string
    {
        return match ($this) {
            self::Individual => __('Individual'),
            self::Corporate => __('Corporate'),
        };
    }
}
