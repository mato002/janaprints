<?php

namespace App\Enums;

enum TaxDocumentType: string
{
    case CustomerInvoice = 'customer_invoice';
    case CustomerCreditNote = 'customer_credit_note';
    case SupplierBill = 'supplier_bill';
    case SupplierCreditNote = 'supplier_credit_note';
    case SupplierPayment = 'supplier_payment';

    public function label(): string
    {
        return match ($this) {
            self::CustomerInvoice => __('Customer invoice'),
            self::CustomerCreditNote => __('Customer credit note'),
            self::SupplierBill => __('Supplier bill'),
            self::SupplierCreditNote => __('Supplier credit note'),
            self::SupplierPayment => __('Supplier payment'),
        };
    }

    public function ledgerDirection(): TaxDirection
    {
        return match ($this) {
            self::CustomerInvoice, self::CustomerCreditNote => TaxDirection::Output,
            self::SupplierBill, self::SupplierCreditNote, self::SupplierPayment => TaxDirection::Input,
        };
    }
}
