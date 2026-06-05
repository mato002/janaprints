<?php

namespace App\Enums;

enum ExitStatus: string
{
    case Initiated = 'initiated';
    case ClearanceInProgress = 'clearance_in_progress';
    case ClearanceComplete = 'clearance_complete';
    case Settled = 'settled';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Initiated => __('Initiated'),
            self::ClearanceInProgress => __('Clearance In Progress'),
            self::ClearanceComplete => __('Clearance Complete'),
            self::Settled => __('Settled'),
            self::Closed => __('Closed'),
        };
    }
}
