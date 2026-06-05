<?php

namespace App\Enums;

enum AssetCustodyStatus: string
{
    case Unassigned = 'unassigned';
    case Assigned = 'assigned';
    case Transferred = 'transferred';
    case Returned = 'returned';
    case Lost = 'lost';
    case Damaged = 'damaged';
    case Retired = 'retired';

    public function label(): string
    {
        return match ($this) {
            self::Unassigned => __('Unassigned'),
            self::Assigned => __('Assigned'),
            self::Transferred => __('Transferred'),
            self::Returned => __('Returned'),
            self::Lost => __('Lost'),
            self::Damaged => __('Damaged'),
            self::Retired => __('Retired'),
        };
    }
}
