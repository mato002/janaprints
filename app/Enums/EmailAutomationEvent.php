<?php

namespace App\Enums;

enum EmailAutomationEvent: string
{
    case QuotationReady = 'quotation_ready';
    case ArtworkApproved = 'artwork_approved';
    case ProductionStarted = 'production_started';
    case ProductionCompleted = 'production_completed';
    case ReadyForCollection = 'ready_for_collection';
    case InvoiceGenerated = 'invoice_generated';
    case PaymentReceived = 'payment_received';
    case StatementAvailable = 'statement_available';
    case SupplierBillApproved = 'supplier_bill_approved';
    case LeaveApproved = 'leave_approved';
    case SystemNotification = 'system_notification';

    public function label(): string
    {
        return match ($this) {
            self::QuotationReady => __('Quotation ready'),
            self::ArtworkApproved => __('Artwork approved'),
            self::ProductionStarted => __('Production started'),
            self::ProductionCompleted => __('Production completed'),
            self::ReadyForCollection => __('Ready for collection'),
            self::InvoiceGenerated => __('Invoice generated'),
            self::PaymentReceived => __('Payment received'),
            self::StatementAvailable => __('Statement available'),
            self::SupplierBillApproved => __('Supplier bill approved'),
            self::LeaveApproved => __('Leave approved'),
            self::SystemNotification => __('System notification'),
        };
    }

    public function templateCategory(): CommunicationTemplateCategory
    {
        return match ($this) {
            self::QuotationReady => CommunicationTemplateCategory::QuotationReady,
            self::ArtworkApproved => CommunicationTemplateCategory::ArtworkApproved,
            self::ProductionStarted => CommunicationTemplateCategory::ProductionStarted,
            self::ProductionCompleted => CommunicationTemplateCategory::ProductionCompleted,
            self::ReadyForCollection => CommunicationTemplateCategory::ReadyForCollection,
            self::InvoiceGenerated => CommunicationTemplateCategory::InvoiceGenerated,
            self::PaymentReceived => CommunicationTemplateCategory::PaymentReceived,
            self::StatementAvailable => CommunicationTemplateCategory::InvoiceGenerated,
            self::SupplierBillApproved => CommunicationTemplateCategory::SupplierBillApproved,
            self::LeaveApproved => CommunicationTemplateCategory::LeaveApproved,
            self::SystemNotification => CommunicationTemplateCategory::AccountActivated,
        };
    }

    /**
     * @return list<self>
     */
    public static function mappable(): array
    {
        return self::cases();
    }
}
