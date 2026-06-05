<?php

namespace App\Enums;

enum CommercialTicketCommentVisibility: string
{
    case Internal = 'internal';
    case Customer = 'customer';

    public function label(): string
    {
        return match ($this) {
            self::Internal => __('Internal'),
            self::Customer => __('Customer'),
        };
    }
}
