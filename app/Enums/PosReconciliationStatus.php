<?php

namespace App\Enums;

enum PosReconciliationStatus: string
{
    case Pending = 'pending';
    case Balanced = 'balanced';
    case VarianceFound = 'variance_found';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function isTerminal(): bool
    {
        return in_array($this, [self::Approved, self::Rejected], true);
    }

    public function awaitsApproval(): bool
    {
        return in_array($this, [self::Balanced, self::VarianceFound], true);
    }
}
