<?php

namespace App\Enums;

enum InboxConversationStatus: string
{
    case Open = 'open';
    case WaitingCustomer = 'waiting_customer';
    case WaitingInternal = 'waiting_internal';
    case PendingApproval = 'pending_approval';
    case Pending = 'pending';
    case Escalated = 'escalated';
    case Resolved = 'resolved';
    case Closed = 'closed';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Open => __('Open'),
            self::WaitingCustomer => __('Waiting customer'),
            self::WaitingInternal => __('Waiting internal team'),
            self::PendingApproval => __('Pending approval'),
            self::Pending => __('Pending'),
            self::Escalated => __('Escalated'),
            self::Resolved => __('Resolved'),
            self::Closed => __('Closed'),
            self::Archived => __('Archived'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Open => 'bg-emerald-100 text-emerald-800',
            self::WaitingCustomer => 'bg-sky-100 text-sky-800',
            self::WaitingInternal => 'bg-indigo-100 text-indigo-800',
            self::PendingApproval => 'bg-violet-100 text-violet-800',
            self::Pending => 'bg-amber-100 text-amber-800',
            self::Escalated => 'bg-red-100 text-red-800',
            self::Resolved => 'bg-teal-100 text-teal-800',
            self::Closed => 'bg-slate-100 text-slate-700',
            self::Archived => 'bg-slate-100 text-slate-500',
        };
    }

    public function isActive(): bool
    {
        return ! in_array($this, [self::Closed, self::Archived, self::Resolved], true);
    }

    /**
     * @return list<self>
     */
    public static function activeCases(): array
    {
        return array_filter(self::cases(), fn (self $s) => $s->isActive());
    }

    /**
     * @return list<string>
     */
    public static function activeValues(): array
    {
        return array_map(fn (self $s) => $s->value, self::activeCases());
    }
}
