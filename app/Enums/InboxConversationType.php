<?php

namespace App\Enums;

enum InboxConversationType: string
{
    case Customer = 'customer';
    case Lead = 'lead';
    case Supplier = 'supplier';
    case Employee = 'employee';
    case Internal = 'internal';

    public function label(): string
    {
        return match ($this) {
            self::Customer => __('Customer'),
            self::Lead => __('Lead'),
            self::Supplier => __('Supplier'),
            self::Employee => __('Employee'),
            self::Internal => __('Internal'),
        };
    }
}
