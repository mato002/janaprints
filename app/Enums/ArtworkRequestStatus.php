<?php

namespace App\Enums;

enum ArtworkRequestStatus: string
{
    case Requested = 'requested';
    case InDesign = 'in_design';
    case Submitted = 'submitted';
    case Approved = 'approved';
    case RevisionRequested = 'revision_requested';
    case Rejected = 'rejected';

    /**
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Requested => [self::InDesign],
            self::InDesign => [self::Submitted],
            self::Submitted => [self::Approved, self::RevisionRequested, self::Rejected],
            self::RevisionRequested => [self::InDesign],
            self::Approved, self::Rejected => [],
        };
    }

    public function canTransitionTo(self $status): bool
    {
        return in_array($status, $this->allowedTransitions(), true);
    }

    public function isEditable(): bool
    {
        return in_array($this, [self::Requested, self::InDesign, self::RevisionRequested], true);
    }
}
