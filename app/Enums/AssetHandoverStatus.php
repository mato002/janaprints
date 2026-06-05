<?php

namespace App\Enums;

enum AssetHandoverStatus: string
{
    case Draft = 'draft';
    case PendingAcceptance = 'pending_acceptance';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::PendingAcceptance => __('Pending Acceptance'),
            self::Accepted => __('Accepted'),
            self::Rejected => __('Rejected'),
            self::Closed => __('Closed'),
        };
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::Draft => 'neutral',
            self::PendingAcceptance => 'warning',
            self::Accepted => 'success',
            self::Rejected => 'danger',
            self::Closed => 'info',
        };
    }
}
