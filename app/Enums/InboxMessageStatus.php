<?php

namespace App\Enums;

enum InboxMessageStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Delivered = 'delivered';
    case Read = 'read';
    case Failed = 'failed';
    case Received = 'received';
    case InternalNote = 'internal_note';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::Sent => __('Sent'),
            self::Delivered => __('Delivered'),
            self::Read => __('Read'),
            self::Failed => __('Failed'),
            self::Received => __('Received'),
            self::InternalNote => __('Internal note'),
            self::Archived => __('Archived'),
        };
    }
}
