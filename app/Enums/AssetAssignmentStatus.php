<?php

namespace App\Enums;

enum AssetAssignmentStatus: string
{
    case Assigned = 'assigned';
    case Returned = 'returned';
    case Transferred = 'transferred';
    case Lost = 'lost';
    case Damaged = 'damaged';
    case Retired = 'retired';

    public function label(): string
    {
        return match ($this) {
            self::Assigned => __('Assigned'),
            self::Returned => __('Returned'),
            self::Transferred => __('Transferred'),
            self::Lost => __('Lost'),
            self::Damaged => __('Damaged'),
            self::Retired => __('Retired'),
        };
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::Assigned => 'info',
            self::Returned => 'success',
            self::Transferred => 'warning',
            self::Lost, self::Damaged => 'danger',
            self::Retired => 'neutral',
        };
    }

    public function isActive(): bool
    {
        return $this === self::Assigned;
    }
}
