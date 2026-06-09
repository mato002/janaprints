<?php

namespace App\Enums;

enum DomainCommunicationEvent: string
{
    case CustomerCreated = 'customer_created';
    case LeadConverted = 'lead_converted';
    case QuotationSent = 'quotation_sent';
    case ArtworkApproved = 'artwork_approved';
    case SalesOrderConfirmed = 'sales_order_confirmed';
    case InvoiceGenerated = 'invoice_generated';
    case PaymentReceived = 'payment_received';
    case InvoiceOverdue = 'invoice_overdue';
    case DeliveryCompleted = 'delivery_completed';
    case FollowUpDue = 'follow_up_due';

    public function label(): string
    {
        return match ($this) {
            self::CustomerCreated => __('Customer created'),
            self::LeadConverted => __('Lead converted'),
            self::QuotationSent => __('Quotation sent'),
            self::ArtworkApproved => __('Artwork approved'),
            self::SalesOrderConfirmed => __('Sales order confirmed'),
            self::InvoiceGenerated => __('Invoice generated'),
            self::PaymentReceived => __('Payment received'),
            self::InvoiceOverdue => __('Invoice overdue'),
            self::DeliveryCompleted => __('Delivery completed'),
            self::FollowUpDue => __('Follow-up due'),
        };
    }

    public function logType(): CommunicationLogType
    {
        return match ($this) {
            self::InvoiceOverdue, self::FollowUpDue => CommunicationLogType::Reminder,
            default => CommunicationLogType::Transactional,
        };
    }

    public function templateCategory(): ?CommunicationTemplateCategory
    {
        return match ($this) {
            self::CustomerCreated => CommunicationTemplateCategory::AccountActivated,
            self::LeadConverted => CommunicationTemplateCategory::AccountActivated,
            self::QuotationSent => CommunicationTemplateCategory::QuotationReady,
            self::ArtworkApproved => CommunicationTemplateCategory::ArtworkApproved,
            self::SalesOrderConfirmed => CommunicationTemplateCategory::ProductionStarted,
            self::InvoiceGenerated => CommunicationTemplateCategory::InvoiceGenerated,
            self::PaymentReceived => CommunicationTemplateCategory::PaymentReceived,
            self::InvoiceOverdue => CommunicationTemplateCategory::InvoiceOverdue,
            self::DeliveryCompleted => CommunicationTemplateCategory::Delivered,
            self::FollowUpDue => null,
        };
    }

    public function notificationType(): ?NotificationType
    {
        return match ($this) {
            self::QuotationSent => NotificationType::QuotationApproved,
            self::ArtworkApproved => NotificationType::ArtworkApproved,
            self::InvoiceGenerated => NotificationType::InvoiceGenerated,
            self::PaymentReceived => NotificationType::PaymentReceived,
            self::InvoiceOverdue => NotificationType::InvoiceOverdue,
            self::DeliveryCompleted => NotificationType::Delivered,
            default => null,
        };
    }

    public function emailAutomationEvent(): ?EmailAutomationEvent
    {
        return match ($this) {
            self::QuotationSent => EmailAutomationEvent::QuotationReady,
            self::ArtworkApproved => EmailAutomationEvent::ArtworkApproved,
            self::InvoiceGenerated => EmailAutomationEvent::InvoiceGenerated,
            self::PaymentReceived => EmailAutomationEvent::PaymentReceived,
            default => null,
        };
    }

    public function whatsappAutomationEvent(): ?WhatsappAutomationEvent
    {
        return match ($this) {
            self::QuotationSent => WhatsappAutomationEvent::QuoteApproved,
            self::ArtworkApproved => WhatsappAutomationEvent::ArtworkApproved,
            self::InvoiceGenerated => WhatsappAutomationEvent::InvoiceGenerated,
            self::PaymentReceived => WhatsappAutomationEvent::PaymentReceived,
            default => null,
        };
    }

    public function webhookEvent(): ?IntegrationWebhookEvent
    {
        return match ($this) {
            self::CustomerCreated => IntegrationWebhookEvent::CustomerCreated,
            self::QuotationSent => IntegrationWebhookEvent::QuotationSent,
            self::ArtworkApproved => IntegrationWebhookEvent::ArtworkApproved,
            self::SalesOrderConfirmed => IntegrationWebhookEvent::SalesOrderCreated,
            self::InvoiceGenerated => IntegrationWebhookEvent::InvoiceGenerated,
            self::PaymentReceived => IntegrationWebhookEvent::PaymentReceived,
            self::DeliveryCompleted => IntegrationWebhookEvent::DeliveryCompleted,
            default => null,
        };
    }

    /**
     * @return list<self>
     */
    public static function scheduled(): array
    {
        return [
            self::InvoiceOverdue,
            self::FollowUpDue,
        ];
    }
}
