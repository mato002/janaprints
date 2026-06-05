<?php

namespace App\Enums;

enum AssetDisposalStatus: string
{
    case Draft = 'draft';
    case PendingApproval = 'pending_approval';
    case Approved = 'approved';
    case Posted = 'posted';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::PendingApproval => __('Pending Approval'),
            self::Approved => __('Approved'),
            self::Posted => __('Posted'),
        };
    }
}
