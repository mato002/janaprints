<?php

namespace App\Enums;

enum QuotationStatus: string
{
    case Draft = 'draft';
    case PendingApproval = 'pending_approval';
    case Sent = 'sent';
    case Viewed = 'viewed';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Expired = 'expired';
    case Converted = 'converted';

    /**
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::PendingApproval],
            self::PendingApproval => [self::Sent, self::Draft],
            self::Sent => [self::Viewed, self::Rejected, self::Expired],
            self::Viewed => [self::Accepted, self::Rejected, self::Expired],
            self::Accepted => [self::Converted],
            self::Rejected, self::Expired, self::Converted => [],
        };
    }

    public function canTransitionTo(self $status): bool
    {
        return in_array($status, $this->allowedTransitions(), true);
    }

    public function isEditable(): bool
    {
        return in_array($this, [self::Draft, self::PendingApproval], true);
    }
}
