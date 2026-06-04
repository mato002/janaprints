<?php

namespace App\Enums;

enum SupplierBillLineType: string
{
    case Inventory = 'inventory';
    case Expense = 'expense';

    public function label(): string
    {
        return match ($this) {
            self::Inventory => __('Inventory'),
            self::Expense => __('Expense'),
        };
    }
}
