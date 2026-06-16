<?php

namespace App\Enums;

enum PayrollRunStatus: string
{
    case Draft = 'draft';
    case Generated = 'generated';
    case UnderReview = 'under_review';
    case PendingApproval = 'pending_approval';
    case Approved = 'approved';
    case Posted = 'posted';
    case Paid = 'paid';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::Generated => __('Generated'),
            self::UnderReview => __('Under Review'),
            self::PendingApproval => __('Pending Approval'),
            self::Approved => __('Approved'),
            self::Posted => __('Posted'),
            self::Paid => __('Paid'),
            self::Cancelled => __('Cancelled'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Draft => 'neutral',
            self::Generated => 'info',
            self::UnderReview => 'info',
            self::PendingApproval => 'warning',
            self::Approved => 'warning',
            self::Posted => 'success',
            self::Paid => 'success',
            self::Cancelled => 'neutral',
        };
    }

    public function canGenerate(): bool
    {
        return in_array($this, [self::Draft, self::Generated, self::UnderReview], true);
    }

    public function canSubmitReview(): bool
    {
        return $this === self::Generated;
    }

    public function canSubmitApproval(): bool
    {
        return $this === self::UnderReview;
    }

    public function canApprove(): bool
    {
        return $this === self::PendingApproval;
    }

    public function canPost(): bool
    {
        return $this === self::Approved;
    }

    public function canMarkPaid(): bool
    {
        return $this === self::Posted;
    }

    public function canCancel(): bool
    {
        return ! in_array($this, [self::Posted, self::Paid, self::Cancelled], true);
    }

    public function isLockedForGeneration(): bool
    {
        return in_array($this, [self::PendingApproval, self::Approved, self::Posted, self::Paid, self::Cancelled], true);
    }
}
