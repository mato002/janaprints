<?php

namespace App\Enums;

enum ArReconciliationExceptionType: string
{
    case MissingJournal = 'missing_journal';
    case OverAllocation = 'over_allocation';
    case NegativeBalance = 'negative_balance';
    case OrphanPayment = 'orphan_payment';
    case UnallocatedDeposit = 'unallocated_deposit';

    public function label(): string
    {
        return match ($this) {
            self::MissingJournal => __('Missing journal'),
            self::OverAllocation => __('Over allocation'),
            self::NegativeBalance => __('Negative balance'),
            self::OrphanPayment => __('Orphan payment'),
            self::UnallocatedDeposit => __('Unallocated deposit'),
        };
    }

    public function isBlocking(): bool
    {
        return $this !== self::UnallocatedDeposit;
    }
}
