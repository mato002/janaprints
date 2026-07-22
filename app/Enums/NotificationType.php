<?php

namespace App\Enums;

enum NotificationType: string
{
    case QuotationSubmitted = 'quotation_submitted';
    case QuotationApproved = 'quotation_approved';
    case QuotationRejected = 'quotation_rejected';
    case ArtworkSubmitted = 'artwork_submitted';
    case ArtworkApproved = 'artwork_approved';
    case ArtworkRejected = 'artwork_rejected';
    case ProductionStarted = 'production_started';
    case ProductionDelayed = 'production_delayed';
    case ProductionCompleted = 'production_completed';
    case ReadyForDispatch = 'ready_for_dispatch';
    case Delivered = 'delivered';
    case InvoiceGenerated = 'invoice_generated';
    case InvoiceOverdue = 'invoice_overdue';
    case PaymentReceived = 'payment_received';
    case SupplierBillApproved = 'supplier_bill_approved';
    case PeriodClosingReminder = 'period_closing_reminder';
    case EmployeeCreated = 'employee_created';
    case LeaveRequestSubmitted = 'leave_request_submitted';
    case LeaveApproved = 'leave_approved';
    case LeaveRejected = 'leave_rejected';
    case RoleChanged = 'role_changed';
    case PasswordReset = 'password_reset';
    case PermissionUpdated = 'permission_updated';
    case BranchAssigned = 'branch_assigned';
    case SmsCampaignCompleted = 'sms_campaign_completed';
    case PublicQuoteRequestReceived = 'public_quote_request_received';
    case InboxCustomerMessage = 'inbox_customer_message';
    case JobCardCreated = 'job_card_created';

    public function label(): string
    {
        return match ($this) {
            self::QuotationSubmitted => __('Quotation Submitted'),
            self::QuotationApproved => __('Quotation Approved'),
            self::QuotationRejected => __('Quotation Rejected'),
            self::ArtworkSubmitted => __('Artwork Submitted'),
            self::ArtworkApproved => __('Artwork Approved'),
            self::ArtworkRejected => __('Artwork Rejected'),
            self::ProductionStarted => __('Production Started'),
            self::ProductionDelayed => __('Production Delayed'),
            self::ProductionCompleted => __('Production Completed'),
            self::ReadyForDispatch => __('Ready For Dispatch'),
            self::Delivered => __('Delivered'),
            self::InvoiceGenerated => __('Invoice Generated'),
            self::InvoiceOverdue => __('Invoice Overdue'),
            self::PaymentReceived => __('Payment Received'),
            self::SupplierBillApproved => __('Supplier Bill Approved'),
            self::PeriodClosingReminder => __('Period Closing Reminder'),
            self::EmployeeCreated => __('Employee Created'),
            self::LeaveRequestSubmitted => __('Leave Request Submitted'),
            self::LeaveApproved => __('Leave Approved'),
            self::LeaveRejected => __('Leave Rejected'),
            self::RoleChanged => __('Role Changed'),
            self::PasswordReset => __('Password Reset'),
            self::PermissionUpdated => __('Permission Updated'),
            self::BranchAssigned => __('Branch Assigned'),
            self::SmsCampaignCompleted => __('SMS Campaign Completed'),
            self::PublicQuoteRequestReceived => __('New Quote Request'),
            self::InboxCustomerMessage => __('New Client Message'),
            self::JobCardCreated => __('New Job for Production'),
        };
    }

    public function category(): NotificationCategory
    {
        return match ($this) {
            self::QuotationSubmitted, self::QuotationApproved, self::QuotationRejected,
            self::PublicQuoteRequestReceived => NotificationCategory::Commercial,
            self::ArtworkSubmitted, self::ArtworkApproved, self::ArtworkRejected,
            self::ProductionStarted, self::ProductionDelayed, self::ProductionCompleted,
            self::ReadyForDispatch, self::Delivered => NotificationCategory::Production,
            self::InvoiceGenerated, self::InvoiceOverdue, self::PaymentReceived,
            self::SupplierBillApproved, self::PeriodClosingReminder => NotificationCategory::Accounting,
            self::EmployeeCreated, self::LeaveRequestSubmitted, self::LeaveApproved, self::LeaveRejected => NotificationCategory::Hr,
            self::JobCardCreated => NotificationCategory::Production,
            self::RoleChanged, self::PasswordReset, self::PermissionUpdated, self::BranchAssigned,
            self::SmsCampaignCompleted, self::InboxCustomerMessage => NotificationCategory::System,
        };
    }

    public function defaultPriority(): NotificationPriority
    {
        return match ($this) {
            self::InvoiceOverdue, self::ProductionDelayed, self::PeriodClosingReminder => NotificationPriority::Critical,
            self::QuotationApproved, self::QuotationRejected, self::PaymentReceived, self::ProductionCompleted,
            self::PublicQuoteRequestReceived, self::InboxCustomerMessage,
            self::JobCardCreated => NotificationPriority::High,
            self::PasswordReset, self::PermissionUpdated => NotificationPriority::High,
            default => NotificationPriority::Normal,
        };
    }
}
