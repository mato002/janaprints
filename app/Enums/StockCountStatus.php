<?php

namespace App\Enums;

enum StockCountStatus: string
{
    case Draft = 'draft';
    case InProgress = 'in_progress';
    case Submitted = 'submitted';
    case Approved = 'approved';
    case Posted = 'posted';
    case Closed = 'closed';
    case Cancelled = 'cancelled';

    public function isEditable(): bool
    {
        return in_array($this, [self::Draft, self::InProgress], true);
    }

    public function canSubmit(): bool
    {
        return in_array($this, [self::Draft, self::InProgress], true);
    }

    public function canApprove(): bool
    {
        return $this === self::Submitted;
    }

    public function canPost(): bool
    {
        return $this === self::Approved;
    }

    public function canCancel(): bool
    {
        return in_array($this, [self::Draft, self::InProgress, self::Submitted], true);
    }
}
