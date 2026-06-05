<?php

namespace App\Enums;

enum AssetWriteOffStatus: string
{
    case Draft = 'draft';
    case PendingApproval = 'pending_approval';
    case Approved = 'approved';
    case Posted = 'posted';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::PendingApproval => __('Pending Approval'),
            self::Approved => __('Approved'),
            self::Posted => __('Posted'),
            self::Rejected => __('Rejected'),
            self::Cancelled => __('Cancelled'),
        };
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::Draft => 'neutral',
            self::PendingApproval => 'warning',
            self::Approved, self::Posted => 'success',
            self::Rejected, self::Cancelled => 'danger',
        };
    }
}
