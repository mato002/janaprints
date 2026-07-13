<?php

namespace App\Enums;

enum ApprovalRuleType: string
{
    case QuotationApproval = 'quotation_approval';
    case DiscountApproval = 'discount_approval';
    case StockAdjustmentApproval = 'stock_adjustment_approval';
    case PurchaseRequestApproval = 'purchase_request_approval';
    case ProcurementApproval = 'procurement_approval';
    case RfqApproval = 'rfq_approval';
    case GoodsReceiptApproval = 'goods_receipt_approval';
    case VendorInvoiceApproval = 'vendor_invoice_approval';
    case PaymentApproval = 'payment_approval';
    case AssetTransferApproval = 'asset_transfer_approval';
    case AssetWriteOffApproval = 'asset_write_off_approval';
    case AssetDisposalApproval = 'asset_disposal_approval';
    case AssetCapitalizationApproval = 'asset_capitalization_approval';
    case CalibrationRuleApproval = 'calibration_rule_approval';
    case PayrollApproval = 'payroll_approval';
}
