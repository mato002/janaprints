<?php

namespace App\Enums;

enum GlAccountTypeCode: string
{
    case Asset = 'asset';
    case Liability = 'liability';
    case Equity = 'equity';
    case Revenue = 'revenue';
    case CostOfSales = 'cost_of_sales';
    case Expense = 'expense';

    public function label(): string
    {
        return match ($this) {
            self::Asset => __('Asset'),
            self::Liability => __('Liability'),
            self::Equity => __('Equity'),
            self::Revenue => __('Revenue'),
            self::CostOfSales => __('Cost Of Sales'),
            self::Expense => __('Expense'),
        };
    }

    public function defaultNormalBalance(): NormalBalance
    {
        return match ($this) {
            self::Asset, self::CostOfSales, self::Expense => NormalBalance::Debit,
            self::Liability, self::Equity, self::Revenue => NormalBalance::Credit,
        };
    }

    public function codeRangePrefix(): string
    {
        return match ($this) {
            self::Asset => '1',
            self::Liability => '2',
            self::Equity => '3',
            self::Revenue => '4',
            self::CostOfSales => '5',
            self::Expense => '6',
        };
    }
}
