<?php

namespace App\Enums;

enum SmsRecipientSource: string
{
    case Customers = 'customers';
    case Leads = 'leads';
    case Employees = 'employees';
    case Suppliers = 'suppliers';
    case Manual = 'manual';
    case Imported = 'imported';
    case Dynamic = 'dynamic';

    public function label(): string
    {
        return match ($this) {
            self::Customers => __('Customers'),
            self::Leads => __('Leads'),
            self::Employees => __('Employees'),
            self::Suppliers => __('Suppliers'),
            self::Manual => __('Manual numbers'),
            self::Imported => __('Imported lists'),
            self::Dynamic => __('Dynamic filters'),
        };
    }
}
