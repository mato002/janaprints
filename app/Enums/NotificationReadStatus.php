<?php

namespace App\Enums;

enum NotificationReadStatus: string
{
    case Unread = 'unread';
    case Read = 'read';
    case Dismissed = 'dismissed';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Unread => __('Unread'),
            self::Read => __('Read'),
            self::Dismissed => __('Dismissed'),
            self::Archived => __('Archived'),
        };
    }
}
