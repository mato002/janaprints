<?php

namespace App\Enums;

enum PostingEventCode: string
{
    case InventoryReceiptPosted = 'inventory.receipt.posted';
    case InventoryIssuePosted = 'inventory.issue.posted';
    case InventoryAdjustmentPosted = 'inventory.adjustment.posted';
    case InventoryConsumptionPosted = 'inventory.consumption.posted';

    case ProcurementGoodsReceiptPosted = 'procurement.goods_receipt.posted';
    case ProcurementInvoiceReceived = 'procurement.invoice.received';

    case ProductionMaterialConsumptionPosted = 'production.material_consumption.posted';
    case ProductionCompletionPosted = 'production.completion.posted';

    case DeliveryCogsPosted = 'dispatch.delivery.cogs.posted';

    case SalesOrderConfirmed = 'sales.order.confirmed';
    case SalesRevenueRecognized = 'sales.revenue.recognized';

    case InvoicePosted = 'invoice.posted';
    case InvoiceCreditNotePosted = 'invoice.credit_note.posted';

    case PaymentReceived = 'payment.received';
    case PaymentMade = 'payment.made';
    case PaymentRefundPosted = 'payment.refund.posted';
    case DepositApplicationPosted = 'payment.deposit.application.posted';
    case DepositRefundPosted = 'payment.deposit.refund.posted';

    case SupplierBillPosted = 'supplier_bill.posted';
    case SupplierBillCreditNotePosted = 'supplier_bill.credit_note.posted';

    case AssetAcquisitionPosted = 'asset.acquisition.posted';
    case AssetDepreciationPosted = 'asset.depreciation.posted';
    case AssetDisposalPosted = 'asset.disposal.posted';
    case AssetWriteOffPosted = 'asset.writeoff.posted';

    case PosSaleCash = 'pos.sale.cash';
    case PosSaleMpesa = 'pos.sale.mpesa';
    case PosSaleCard = 'pos.sale.card';
    case PosReturn = 'pos.return';
    case PosVariance = 'pos.variance';

    case PayrollPosted = 'payroll.posted';

    public function module(): PostingModule
    {
        return match ($this) {
            self::InventoryReceiptPosted,
            self::InventoryIssuePosted,
            self::InventoryAdjustmentPosted,
            self::InventoryConsumptionPosted => PostingModule::Inventory,

            self::ProcurementGoodsReceiptPosted,
            self::ProcurementInvoiceReceived => PostingModule::Procurement,

            self::ProductionMaterialConsumptionPosted,
            self::ProductionCompletionPosted => PostingModule::Production,

            self::DeliveryCogsPosted => PostingModule::Dispatch,

            self::SalesOrderConfirmed,
            self::SalesRevenueRecognized => PostingModule::Sales,

            self::InvoicePosted,
            self::InvoiceCreditNotePosted => PostingModule::Invoice,

            self::PaymentReceived,
            self::PaymentMade,
            self::PaymentRefundPosted,
            self::DepositApplicationPosted,
            self::DepositRefundPosted => PostingModule::Payment,

            self::SupplierBillPosted,
            self::SupplierBillCreditNotePosted => PostingModule::Procurement,

            self::AssetAcquisitionPosted,
            self::AssetDepreciationPosted,
            self::AssetDisposalPosted,
            self::AssetWriteOffPosted => PostingModule::Assets,

            self::PosSaleCash,
            self::PosSaleMpesa,
            self::PosSaleCard,
            self::PosReturn,
            self::PosVariance => PostingModule::Pos,

            self::PayrollPosted => PostingModule::Hr,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::InventoryReceiptPosted => __('Inventory receipt posted'),
            self::InventoryIssuePosted => __('Inventory issue posted'),
            self::InventoryAdjustmentPosted => __('Inventory adjustment posted'),
            self::InventoryConsumptionPosted => __('Inventory consumption posted'),
            self::ProcurementGoodsReceiptPosted => __('Goods receipt posted'),
            self::ProcurementInvoiceReceived => __('Supplier invoice received'),
            self::ProductionMaterialConsumptionPosted => __('Production material consumption'),
            self::ProductionCompletionPosted => __('Production completion'),
            self::DeliveryCogsPosted => __('Delivery COGS'),
            self::SalesOrderConfirmed => __('Sales order confirmed'),
            self::SalesRevenueRecognized => __('Sales revenue recognized'),
            self::InvoicePosted => __('Customer invoice posted'),
            self::InvoiceCreditNotePosted => __('Credit note posted'),
            self::PaymentReceived => __('Customer payment received'),
            self::PaymentMade => __('Supplier payment made'),
            self::PaymentRefundPosted => __('Payment refund posted'),
            self::DepositApplicationPosted => __('Customer deposit applied'),
            self::DepositRefundPosted => __('Customer deposit refund posted'),
            self::SupplierBillPosted => __('Supplier bill posted'),
            self::SupplierBillCreditNotePosted => __('Supplier credit note posted'),
            self::AssetAcquisitionPosted => __('Asset acquisition posted'),
            self::AssetDepreciationPosted => __('Asset depreciation posted'),
            self::AssetDisposalPosted => __('Asset disposal posted'),
            self::AssetWriteOffPosted => __('Asset write-off posted'),
            self::PosSaleCash => __('POS cash sale'),
            self::PosSaleMpesa => __('POS M-Pesa sale'),
            self::PosSaleCard => __('POS card sale'),
            self::PosReturn => __('POS return'),
            self::PosVariance => __('POS cash variance'),
            self::PayrollPosted => __('Payroll posted'),
        };
    }
}
