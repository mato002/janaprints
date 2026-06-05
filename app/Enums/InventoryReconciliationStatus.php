<?php

namespace App\Enums;

enum InventoryReconciliationStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Posted = 'posted';
    case Closed = 'closed';

    public function canApprove(): bool
    {
        return $this === self::Pending;
    }

    public function canPost(): bool
    {
        return $this === self::Approved;
    }
}
