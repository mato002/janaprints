<?php

namespace App\Enums;

enum IntegrationWebhookEvent: string
{

    case CustomerCreated = 'customer.created';
    case QuotationCreated = 'quotation.created';
    case QuotationApproved = 'quotation.approved';
    case SalesOrderCreated = 'sales_order.created';
    case ArtworkApproved = 'artwork.approved';
    case ProductionStarted = 'production.started';
    case ProductionCompleted = 'production.completed';
    case StockReceived = 'stock.received';
    case StockIssued = 'stock.issued';
    case StockAdjusted = 'stock.adjusted';
    case InvoiceCreated = 'invoice.created';
    case InvoiceApproved = 'invoice.approved';
    case InvoiceGenerated = 'invoice.generated';
    case PaymentReceived = 'payment.received';
    case StatementGenerated = 'statement.generated';
    case DeliveryCompleted = 'delivery.completed';

    public function label(): string
    {
        return match ($this) {
            self::CustomerCreated => __('Customer created'),
            self::QuotationCreated => __('Quotation created'),
            self::QuotationApproved => __('Quotation approved'),
            self::SalesOrderCreated => __('Sales order created'),
            self::ArtworkApproved => __('Artwork approved'),
            self::ProductionStarted => __('Production started'),
            self::ProductionCompleted => __('Production completed'),
            self::StockReceived => __('Stock received'),
            self::StockIssued => __('Stock issued'),
            self::StockAdjusted => __('Stock adjusted'),
            self::InvoiceCreated => __('Invoice created'),
            self::InvoiceApproved => __('Invoice approved'),
            self::InvoiceGenerated => __('Invoice generated'),
            self::PaymentReceived => __('Payment received'),
            self::StatementGenerated => __('Statement generated'),
            self::DeliveryCompleted => __('Delivery completed'),
        };
    }

    public function group(): string
    {
        return match ($this) {
            self::CustomerCreated, self::QuotationCreated, self::QuotationApproved, self::SalesOrderCreated => 'commercial',
            self::ArtworkApproved, self::ProductionStarted, self::ProductionCompleted => 'production',
            self::StockReceived, self::StockIssued, self::StockAdjusted => 'inventory',
            self::InvoiceCreated, self::InvoiceApproved, self::InvoiceGenerated, self::PaymentReceived, self::StatementGenerated => 'finance',
            self::DeliveryCompleted => 'production',
        };
    }
}
