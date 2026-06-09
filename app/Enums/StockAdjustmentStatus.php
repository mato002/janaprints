<?php

namespace App\Enums;

enum StockAdjustmentStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Approved = 'approved';
    case Posted = 'posted';

    public function canSubmit(): bool
    {
        return $this === self::Draft;
    }

    public function canApprove(): bool
    {
        return $this === self::Submitted;
    }

    public function canPost(bool $requiresApproval): bool
    {
        if ($this === self::Posted) {
            return false;
        }

        if ($requiresApproval) {
            return $this === self::Approved;
        }

        return in_array($this, [self::Draft, self::Approved], true);
    }

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::Submitted => __('Pending Approval'),
            self::Approved => __('Approved'),
            self::Posted => __('Posted'),
        };
    }
}
