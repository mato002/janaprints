<?php

namespace App\Enums;

enum RetentionPolicyDomain: string
{
    case AuditLogs = 'audit_logs';
    case ActivityLogs = 'activity_logs';
    case Documents = 'documents';
    case Communications = 'communications';
    case Files = 'files';
    case Backups = 'backups';

    public function label(): string
    {
        return match ($this) {
            self::AuditLogs => __('Audit Logs'),
            self::ActivityLogs => __('Activity Logs'),
            self::Documents => __('Documents'),
            self::Communications => __('Communications'),
            self::Files => __('Files'),
            self::Backups => __('Backups'),
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::AuditLogs => __('Compliance audit events and immutable governance records.'),
            self::ActivityLogs => __('User actions and general system activity history.'),
            self::Documents => __('Generated documents, exports, and document artifacts.'),
            self::Communications => __('Email, SMS, and communication delivery logs.'),
            self::Files => __('Uploaded files and attachment storage.'),
            self::Backups => __('Database, file, and storage backup artifacts.'),
        };
    }
}
