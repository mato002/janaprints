<?php

namespace App\Enums;

enum CommunicationTemplateCategory: string
{
    case QuotationReady = 'quotation_ready';
    case QuotationApproved = 'quotation_approved';
    case QuotationRejected = 'quotation_rejected';
    case ArtworkSubmitted = 'artwork_submitted';
    case ArtworkApproved = 'artwork_approved';
    case ArtworkRejected = 'artwork_rejected';
    case ProductionStarted = 'production_started';
    case ProductionCompleted = 'production_completed';
    case ReadyForCollection = 'ready_for_collection';
    case DispatchStarted = 'dispatch_started';
    case Delivered = 'delivered';
    case InvoiceGenerated = 'invoice_generated';
    case InvoiceOverdue = 'invoice_overdue';
    case PaymentReceived = 'payment_received';
    case DepositReceived = 'deposit_received';
    case SupplierBillApproved = 'supplier_bill_approved';
    case EmployeeCreated = 'employee_created';
    case LeaveApproved = 'leave_approved';
    case LeaveRejected = 'leave_rejected';
    case PasswordReset = 'password_reset';
    case OtpVerification = 'otp_verification';
    case AccountActivated = 'account_activated';
    case RoleChanged = 'role_changed';

    public function label(): string
    {
        return match ($this) {
            self::QuotationReady => __('Quotation Ready'),
            self::QuotationApproved => __('Quotation Approved'),
            self::QuotationRejected => __('Quotation Rejected'),
            self::ArtworkSubmitted => __('Artwork Submitted'),
            self::ArtworkApproved => __('Artwork Approved'),
            self::ArtworkRejected => __('Artwork Rejected'),
            self::ProductionStarted => __('Production Started'),
            self::ProductionCompleted => __('Production Completed'),
            self::ReadyForCollection => __('Ready For Collection'),
            self::DispatchStarted => __('Dispatch Started'),
            self::Delivered => __('Delivered'),
            self::InvoiceGenerated => __('Invoice Generated'),
            self::InvoiceOverdue => __('Invoice Overdue'),
            self::PaymentReceived => __('Payment Received'),
            self::DepositReceived => __('Deposit Received'),
            self::SupplierBillApproved => __('Supplier Bill Approved'),
            self::EmployeeCreated => __('Employee Created'),
            self::LeaveApproved => __('Leave Approved'),
            self::LeaveRejected => __('Leave Rejected'),
            self::PasswordReset => __('Password Reset'),
            self::OtpVerification => __('OTP Verification'),
            self::AccountActivated => __('Account Activated'),
            self::RoleChanged => __('Role Changed'),
        };
    }

    public function group(): string
    {
        return match ($this) {
            self::QuotationReady, self::QuotationApproved, self::QuotationRejected => 'commercial',
            self::ArtworkSubmitted, self::ArtworkApproved, self::ArtworkRejected,
            self::ProductionStarted, self::ProductionCompleted, self::ReadyForCollection,
            self::DispatchStarted, self::Delivered => 'production',
            self::InvoiceGenerated, self::InvoiceOverdue, self::PaymentReceived,
            self::DepositReceived, self::SupplierBillApproved => 'finance',
            self::EmployeeCreated, self::LeaveApproved, self::LeaveRejected => 'hr',
            self::PasswordReset, self::OtpVerification, self::AccountActivated, self::RoleChanged => 'system',
        };
    }
}
