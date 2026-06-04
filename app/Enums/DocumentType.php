<?php

namespace App\Enums;

enum DocumentType: string
{
    case Customer = 'customer';
    case Lead = 'lead';
    case Quotation = 'quotation';
    case ArtworkRequest = 'artwork_request';
    case SalesOrder = 'sales_order';
    case JobCard = 'job_card';
    case StockReceipt = 'stock_receipt';
    case StockIssue = 'stock_issue';
    case StockAdjustment = 'stock_adjustment';
    case Invoice = 'invoice';
    case Payment = 'payment';
    case Vendor = 'vendor';
    case PurchaseRequest = 'purchase_request';
    case PurchaseOrder = 'purchase_order';
    case GoodsReceipt = 'goods_receipt';
    case SupplierQuotation = 'supplier_quotation';
    case Rfq = 'rfq';
    case FixedAsset = 'fixed_asset';

    public function typeCode(): string
    {
        return match ($this) {
            self::Customer => 'CUST',
            self::Lead => 'LEAD',
            self::Quotation => 'QUOTE',
            self::ArtworkRequest => 'ART',
            self::SalesOrder => 'SO',
            self::JobCard => 'JOB',
            self::StockReceipt => 'RCPT',
            self::StockIssue => 'ISSUE',
            self::StockAdjustment => 'ADJ',
            self::Invoice => 'INV',
            self::Payment => 'PAY',
            self::Vendor => 'VEND',
            self::PurchaseRequest => 'PR',
            self::PurchaseOrder => 'PO',
            self::GoodsReceipt => 'GRN',
            self::SupplierQuotation => 'SQ',
            self::Rfq => 'RFQ',
            self::FixedAsset => 'AST',
        };
    }
}
