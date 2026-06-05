<?php

namespace App\Enums;

enum ComplianceAuditCategory: string
{
    case UserCreated = 'user_created';
    case RoleChanged = 'role_changed';
    case PermissionChanged = 'permission_changed';
    case SettingsChanged = 'settings_changed';
    case NumberSeriesChanged = 'number_series_changed';
    case DocumentTypeChanged = 'document_type_changed';
    case InventoryAdjusted = 'inventory_adjusted';
    case AccountingPeriodClosed = 'accounting_period_closed';
    case JournalPosted = 'journal_posted';
    case PaymentReversed = 'payment_reversed';

    public function label(): string
    {
        return match ($this) {
            self::UserCreated => __('User Created'),
            self::RoleChanged => __('Role Changed'),
            self::PermissionChanged => __('Permission Changed'),
            self::SettingsChanged => __('Settings Changed'),
            self::NumberSeriesChanged => __('Number Series Changed'),
            self::DocumentTypeChanged => __('Document Type Changed'),
            self::InventoryAdjusted => __('Inventory Adjusted'),
            self::AccountingPeriodClosed => __('Accounting Period Closed'),
            self::JournalPosted => __('Journal Posted'),
            self::PaymentReversed => __('Payment Reversed'),
        };
    }

    /**
     * @return list<string>
     */
    public function actions(): array
    {
        return match ($this) {
            self::UserCreated => ['created'],
            self::RoleChanged => ['created', 'updated', 'deleted', 'deactivated', 'reactivated', 'role_assignment', 'role_created', 'role_updated', 'role_deleted', 'role_deactivated', 'role_reactivated'],
            self::PermissionChanged => ['permission_assignment', 'permissions_updated'],
            self::SettingsChanged => ['settings_changed'],
            self::NumberSeriesChanged => ['number_series_changed'],
            self::DocumentTypeChanged => ['document_type.created', 'document_type.updated', 'document_type.activated', 'document_type.deactivated'],
            self::InventoryAdjusted => ['inventory_adjusted'],
            self::AccountingPeriodClosed => ['period_closed'],
            self::JournalPosted => ['journal_posted'],
            self::PaymentReversed => ['payment_reversed'],
        };
    }
}
