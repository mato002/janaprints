<?php

namespace App\Enums;

enum ApprovalRuleType: string
{
    case QuotationApproval = 'quotation_approval';
    case DiscountApproval = 'discount_approval';
    case StockAdjustmentApproval = 'stock_adjustment_approval';
    case ProcurementApproval = 'procurement_approval';
    case PaymentApproval = 'payment_approval';
}
