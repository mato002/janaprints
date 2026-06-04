<?php

namespace App\Enums;

enum InboxAuditEventType: string
{
    case MessageSent = 'message_sent';
    case MessageEdited = 'message_edited';
    case MessageDeleted = 'message_deleted';
    case AttachmentAdded = 'attachment_added';
    case AttachmentRemoved = 'attachment_removed';
    case NoteAdded = 'note_added';
    case AssignmentChanged = 'assignment_changed';
    case StatusChanged = 'status_changed';
    case EscalationCreated = 'escalation_created';
    case RecordLinked = 'record_linked';
    case WatcherChanged = 'watcher_changed';

    public function label(): string
    {
        return match ($this) {
            self::MessageSent => __('Message sent'),
            self::MessageEdited => __('Message edited'),
            self::MessageDeleted => __('Message deleted'),
            self::AttachmentAdded => __('Attachment added'),
            self::AttachmentRemoved => __('Attachment removed'),
            self::NoteAdded => __('Note added'),
            self::AssignmentChanged => __('Assignment changed'),
            self::StatusChanged => __('Status changed'),
            self::EscalationCreated => __('Escalation created'),
            self::RecordLinked => __('Record linked'),
            self::WatcherChanged => __('Watcher changed'),
        };
    }
}
