<?php

namespace App\Enums;

enum SupplierQuotationStatus: string
{
    case Draft = 'draft';
    case Received = 'received';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Expired = 'expired';

    public function isEditable(): bool
    {
        return $this === self::Draft;
    }
}
