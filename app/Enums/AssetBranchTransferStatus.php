<?php

namespace App\Enums;

enum AssetBranchTransferStatus: string
{
    case Draft = 'draft';
    case PendingApproval = 'pending_approval';
    case Approved = 'approved';
    case PendingAcceptance = 'pending_acceptance';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::PendingApproval => __('Pending Approval'),
            self::Approved => __('Approved'),
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
            self::PendingApproval, self::PendingAcceptance => 'warning',
            self::Approved, self::Accepted => 'success',
            self::Rejected => 'danger',
            self::Closed => 'info',
        };
    }
}
