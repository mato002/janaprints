<?php

namespace App\Enums;

enum SalesOrderFinancialStatus: string
{
    case NotInvoiced = 'not_invoiced';
    case PartiallyInvoiced = 'partially_invoiced';
    case FullyInvoiced = 'fully_invoiced';
    case PartiallyPaid = 'partially_paid';
    case Paid = 'paid';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::NotInvoiced => __('Not Invoiced'),
            self::PartiallyInvoiced => __('Partially Invoiced'),
            self::FullyInvoiced => __('Fully Invoiced'),
            self::PartiallyPaid => __('Partially Paid'),
            self::Paid => __('Paid'),
            self::Closed => __('Closed'),
        };
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::Paid, self::Closed => 'success',
            self::PartiallyPaid, self::PartiallyInvoiced => 'warning',
            self::FullyInvoiced => 'info',
            self::NotInvoiced => 'neutral',
        };
    }
}
