<?php

namespace App\Enums;

enum PurchaseRequestStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case PendingApproval = 'pending_approval';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case ConvertedToPo = 'converted_to_po';
    case Closed = 'closed';

    public function isEditable(): bool
    {
        return $this === self::Draft;
    }

    public function canSubmit(): bool
    {
        return $this === self::Draft;
    }

    public function canApprove(): bool
    {
        return in_array($this, [self::Submitted, self::PendingApproval], true);
    }

    public function canReject(): bool
    {
        return $this->canApprove();
    }

    public function canConvert(): bool
    {
        return $this === self::Approved;
    }
}
