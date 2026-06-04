<?php

namespace App\Enums;

enum WhatsappAutomationEvent: string
{
    case QuoteApproved = 'quote_approved';
    case ArtworkApproved = 'artwork_approved';
    case ProductionStarted = 'production_started';
    case ProductionCompleted = 'production_completed';
    case InvoiceGenerated = 'invoice_generated';
    case PaymentReceived = 'payment_received';

    public function label(): string
    {
        return match ($this) {
            self::QuoteApproved => __('Quote approved'),
            self::ArtworkApproved => __('Artwork approved'),
            self::ProductionStarted => __('Production started'),
            self::ProductionCompleted => __('Production completed'),
            self::InvoiceGenerated => __('Invoice generated'),
            self::PaymentReceived => __('Payment received'),
        };
    }

    /**
     * Maps ERP domain events to COM-1 template categories (automation-ready, no sending).
     */
    public function templateCategory(): CommunicationTemplateCategory
    {
        return match ($this) {
            self::QuoteApproved => CommunicationTemplateCategory::QuotationApproved,
            self::ArtworkApproved => CommunicationTemplateCategory::ArtworkApproved,
            self::ProductionStarted => CommunicationTemplateCategory::ProductionStarted,
            self::ProductionCompleted => CommunicationTemplateCategory::ProductionCompleted,
            self::InvoiceGenerated => CommunicationTemplateCategory::InvoiceGenerated,
            self::PaymentReceived => CommunicationTemplateCategory::PaymentReceived,
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
