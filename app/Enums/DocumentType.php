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
    case DeliveryNote = 'delivery_note';
    case StockReceipt = 'stock_receipt';
    case StockIssue = 'stock_issue';
    case StockAdjustment = 'stock_adjustment';
    case StockCount = 'stock_count';
    case InventoryReconciliation = 'inventory_reconciliation';
    case Invoice = 'invoice';
    case CreditNote = 'credit_note';
    case Payment = 'payment';
    case SupplierBill = 'supplier_bill';
    case SupplierCreditNote = 'supplier_credit_note';
    case SupplierPayment = 'supplier_payment';
    case Vendor = 'vendor';
    case PurchaseRequest = 'purchase_request';
    case PurchaseOrder = 'purchase_order';
    case GoodsReceipt = 'goods_receipt';
    case SupplierQuotation = 'supplier_quotation';
    case Rfq = 'rfq';
    case FixedAsset = 'fixed_asset';
    case MaintenanceWorkOrder = 'maintenance_work_order';
    case AssetHandover = 'asset_handover';
    case AssetBranchTransfer = 'asset_branch_transfer';
    case DepreciationRun = 'depreciation_run';
    case AssetWriteOff = 'asset_write_off';
    case AssetReconciliation = 'asset_reconciliation';
    case AssetCapitalizationCandidate = 'asset_capitalization_candidate';
    case AssetCapitalizationReconciliation = 'asset_capitalization_reconciliation';
    case Journal = 'journal';

    public function typeCode(): string
    {
        return match ($this) {
            self::Customer => 'CUST',
            self::Lead => 'LEAD',
            self::Quotation => 'QUOTE',
            self::ArtworkRequest => 'ART',
            self::SalesOrder => 'SO',
            self::JobCard => 'JOB',
            self::DeliveryNote => 'DN',
            self::StockReceipt => 'RCPT',
            self::StockIssue => 'ISSUE',
            self::StockAdjustment => 'ADJ',
            self::StockCount => 'SC',
            self::InventoryReconciliation => 'IR',
            self::Invoice => 'INV',
            self::CreditNote => 'CN',
            self::Payment => 'PAY',
            self::SupplierBill => 'SBILL',
            self::SupplierCreditNote => 'SCN',
            self::SupplierPayment => 'SPAY',
            self::Vendor => 'VEND',
            self::PurchaseRequest => 'PR',
            self::PurchaseOrder => 'PO',
            self::GoodsReceipt => 'GRN',
            self::SupplierQuotation => 'SQ',
            self::Rfq => 'RFQ',
            self::FixedAsset => 'AST',
            self::MaintenanceWorkOrder => 'MWO',
            self::AssetHandover => 'AHO',
            self::AssetBranchTransfer => 'ABT',
            self::DepreciationRun => 'DR',
            self::AssetWriteOff => 'AWO',
            self::AssetReconciliation => 'ARC',
            self::AssetCapitalizationCandidate => 'CAP',
            self::AssetCapitalizationReconciliation => 'ACR',
            self::Journal => 'JE',
        };
    }
}
