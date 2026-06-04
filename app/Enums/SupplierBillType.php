<?php

namespace App\Enums;

enum SupplierBillType: string
{
    case Standard = 'standard';
    case FromPurchaseOrder = 'from_po';
    case FromGoodsReceipt = 'from_grn';
    case CreditNote = 'credit_note';

    public function label(): string
    {
        return match ($this) {
            self::Standard => __('Manual bill'),
            self::FromPurchaseOrder => __('From purchase order'),
            self::FromGoodsReceipt => __('From goods receipt'),
            self::CreditNote => __('Credit note'),
        };
    }

    public function documentType(): DocumentType
    {
        return $this->isCredit() ? DocumentType::SupplierCreditNote : DocumentType::SupplierBill;
    }

    public function isCredit(): bool
    {
        return $this === self::CreditNote;
    }
}
