<?php

namespace App\Enums;

enum PublicQuoteRequestStatus: string
{
    case Pending = 'pending';
    case Reviewing = 'reviewing';
    case Quoted = 'quoted';
    case Closed = 'closed';
    case Spam = 'spam';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('Pending'),
            self::Reviewing => __('Reviewing'),
            self::Quoted => __('Quoted'),
            self::Closed => __('Closed'),
            self::Spam => __('Spam'),
        };
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Reviewing => 'info',
            self::Quoted => 'success',
            self::Closed => 'neutral',
            self::Spam => 'danger',
        };
    }

    public function workspaceLabel(): string
    {
        return match ($this) {
            self::Pending => __('Pending'),
            self::Reviewing => __('Reviewed'),
            self::Quoted => __('Quoted'),
            self::Closed => __('Approved'),
            self::Spam => __('Rejected'),
        };
    }
}
