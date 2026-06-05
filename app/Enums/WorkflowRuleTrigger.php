<?php

namespace App\Enums;

enum WorkflowRuleTrigger: string
{
    case Created = 'created';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Created => __('Created'),
            self::Approved => __('Approved'),
            self::Rejected => __('Rejected'),
            self::Completed => __('Completed'),
            self::Cancelled => __('Cancelled'),
            self::Closed => __('Closed'),
        };
    }
}
