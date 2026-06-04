<?php

namespace App\Enums;

enum PostingAmountSource: string
{
    case Amount = 'amount';
    case Subtotal = 'subtotal';
    case TaxAmount = 'tax_amount';
    case TotalAmount = 'total_amount';
    case AllocatedAmount = 'allocated_amount';
    case UnallocatedAmount = 'unallocated_amount';
    case ContextField = 'context_field';

    public function label(): string
    {
        return match ($this) {
            self::Amount => __('Amount'),
            self::Subtotal => __('Subtotal'),
            self::TaxAmount => __('Tax amount'),
            self::TotalAmount => __('Total amount'),
            self::AllocatedAmount => __('Allocated amount'),
            self::UnallocatedAmount => __('Unallocated amount'),
            self::ContextField => __('Custom context field'),
        };
    }
}
