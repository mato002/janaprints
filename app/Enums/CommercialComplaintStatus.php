<?php

namespace App\Enums;

enum CommercialComplaintStatus: string
{
    case Open = 'open';
    case Assigned = 'assigned';
    case Investigating = 'investigating';
    case Resolved = 'resolved';
    case Closed = 'closed';
    case Reopened = 'reopened';

    public function label(): string
    {
        return match ($this) {
            self::Open => __('Open'),
            self::Assigned => __('Assigned'),
            self::Investigating => __('Investigating'),
            self::Resolved => __('Resolved'),
            self::Closed => __('Closed'),
            self::Reopened => __('Reopened'),
        };
    }

    /**
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Open => [self::Assigned, self::Investigating, self::Resolved, self::Closed],
            self::Assigned => [self::Investigating, self::Resolved, self::Closed],
            self::Investigating => [self::Resolved, self::Closed],
            self::Resolved => [self::Closed, self::Reopened],
            self::Closed => [self::Reopened],
            self::Reopened => [self::Assigned, self::Investigating, self::Resolved],
        };
    }

    public function canTransitionTo(self $status): bool
    {
        return in_array($status, $this->allowedTransitions(), true);
    }
}
