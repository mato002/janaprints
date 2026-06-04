<?php

namespace App\Enums;

enum CustomerInvoiceType: string
{
    case Standard = 'standard';
    case Partial = 'partial';
    case Deposit = 'deposit';
    case Progress = 'progress';
    case CreditNote = 'credit_note';

    public function label(): string
    {
        return match ($this) {
            self::Standard => __('Standard'),
            self::Partial => __('Partial'),
            self::Deposit => __('Deposit'),
            self::Progress => __('Progress billing'),
            self::CreditNote => __('Credit note'),
        };
    }

    public function isCredit(): bool
    {
        return $this === self::CreditNote;
    }

    public function documentType(): DocumentType
    {
        return $this->isCredit() ? DocumentType::CreditNote : DocumentType::Invoice;
    }
}
