<?php

namespace App\Enums;

enum CommercialTicketStatus: string
{
    case Open = 'open';
    case Assigned = 'assigned';
    case InProgress = 'in_progress';
    case WaitingCustomer = 'waiting_customer';
    case Resolved = 'resolved';
    case Closed = 'closed';
    case Reopened = 'reopened';

    public function label(): string
    {
        return match ($this) {
            self::Open => __('Open'),
            self::Assigned => __('Assigned'),
            self::InProgress => __('In Progress'),
            self::WaitingCustomer => __('Waiting Customer'),
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
            self::Open => [self::Assigned, self::InProgress, self::Closed],
            self::Assigned => [self::InProgress, self::WaitingCustomer, self::Resolved, self::Closed],
            self::InProgress => [self::WaitingCustomer, self::Resolved, self::Closed],
            self::WaitingCustomer => [self::InProgress, self::Resolved, self::Closed],
            self::Resolved => [self::Closed, self::Reopened],
            self::Closed => [self::Reopened],
            self::Reopened => [self::Assigned, self::InProgress, self::Resolved],
        };
    }

    public function canTransitionTo(self $status): bool
    {
        return in_array($status, $this->allowedTransitions(), true);
    }
}
