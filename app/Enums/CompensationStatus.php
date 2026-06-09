<?php

namespace App\Enums;

enum CompensationStatus: string
{
    case PendingApproval = 'pending_approval';
    case Active = 'active';
    case Superseded = 'superseded';

    public function label(): string
    {
        return match ($this) {
            self::PendingApproval => __('Pending Approval'),
            self::Active => __('Active'),
            self::Superseded => __('Superseded'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PendingApproval => 'warning',
            self::Active => 'success',
            self::Superseded => 'neutral',
        };
    }
}
