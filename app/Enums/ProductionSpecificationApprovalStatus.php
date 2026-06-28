<?php

namespace App\Enums;

enum ProductionSpecificationApprovalStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::Pending => __('Pending approval'),
            self::Approved => __('Approved'),
            self::Rejected => __('Rejected'),
        };
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::Approved => 'success',
            self::Pending => 'warning',
            self::Rejected => 'danger',
            self::Draft => 'neutral',
        };
    }
}
