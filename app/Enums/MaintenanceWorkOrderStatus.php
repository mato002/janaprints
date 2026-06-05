<?php

namespace App\Enums;

enum MaintenanceWorkOrderStatus: string
{
    case Draft = 'draft';
    case Open = 'open';
    case Assigned = 'assigned';
    case InProgress = 'in_progress';
    case WaitingParts = 'waiting_parts';
    case WaitingVendor = 'waiting_vendor';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::Open => __('Open'),
            self::Assigned => __('Assigned'),
            self::InProgress => __('In Progress'),
            self::WaitingParts => __('Waiting Parts'),
            self::WaitingVendor => __('Waiting Vendor'),
            self::Completed => __('Completed'),
            self::Cancelled => __('Cancelled'),
            self::Closed => __('Closed'),
        };
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::Draft => 'neutral',
            self::Open, self::Assigned => 'warning',
            self::InProgress => 'success',
            self::WaitingParts, self::WaitingVendor => 'warning',
            self::Completed => 'success',
            self::Cancelled => 'neutral',
            self::Closed => 'neutral',
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [
            self::Open,
            self::Assigned,
            self::InProgress,
            self::WaitingParts,
            self::WaitingVendor,
        ], true);
    }

    public function blocksProduction(): bool
    {
        return in_array($this, [
            self::Open,
            self::Assigned,
            self::InProgress,
            self::WaitingParts,
            self::WaitingVendor,
        ], true);
    }
}
