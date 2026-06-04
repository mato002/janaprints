<?php

namespace App\Enums;

enum PurchaseRequestStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Approved = 'approved';
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
        return $this === self::Submitted;
    }

    public function canConvert(): bool
    {
        return $this === self::Approved;
    }
}
