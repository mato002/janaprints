<?php

namespace App\Enums;

enum PosSessionStatus: string
{
    case Open = 'open';
    case Closing = 'closing';
    case PendingApproval = 'pending_approval';
    case Closed = 'closed';
    case Suspended = 'suspended';
    case Cancelled = 'cancelled';

    public function acceptsSales(): bool
    {
        return $this === self::Open;
    }

    public function isActive(): bool
    {
        return in_array($this, [self::Open, self::Suspended], true);
    }

    public function isSettled(): bool
    {
        return in_array($this, [self::Closed, self::Cancelled], true);
    }
}
