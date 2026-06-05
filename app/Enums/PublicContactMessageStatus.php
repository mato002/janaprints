<?php

namespace App\Enums;

enum PublicContactMessageStatus: string
{
    case Unread = 'unread';
    case Read = 'read';
    case Responded = 'responded';
    case Closed = 'closed';
    case Spam = 'spam';

    public function label(): string
    {
        return match ($this) {
            self::Unread => __('Unread'),
            self::Read => __('Read'),
            self::Responded => __('Responded'),
            self::Closed => __('Closed'),
            self::Spam => __('Spam'),
        };
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::Unread => 'danger',
            self::Read => 'info',
            self::Responded => 'success',
            self::Closed => 'neutral',
            self::Spam => 'danger',
        };
    }
}
