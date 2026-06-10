<?php

namespace App\Enums;

enum PostingModule: string
{
    case Inventory = 'inventory';
    case Procurement = 'procurement';
    case Production = 'production';
    case Dispatch = 'dispatch';
    case Sales = 'sales';
    case Invoice = 'invoice';
    case Payment = 'payment';
    case Assets = 'assets';
    case Pos = 'pos';
    case Hr = 'hr';
    case General = 'general';

    public function label(): string
    {
        return match ($this) {
            self::Inventory => __('Inventory'),
            self::Procurement => __('Procurement'),
            self::Production => __('Production'),
            self::Dispatch => __('Dispatch'),
            self::Sales => __('Sales'),
            self::Invoice => __('Invoices'),
            self::Payment => __('Payments'),
            self::Assets => __('Fixed Assets'),
            self::Pos => __('Point of Sale'),
            self::Hr => __('HR & Payroll'),
            self::General => __('General'),
        };
    }
}
