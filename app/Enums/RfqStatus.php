<?php

namespace App\Enums;

enum RfqStatus: string
{
    case Draft = 'draft';
    case Open = 'open';
    case Closed = 'closed';
    case AwaitingComparison = 'awaiting_comparison';
    case Awarded = 'awarded';
    case ConvertedToPo = 'converted_to_po';
    case Cancelled = 'cancelled';

    public function isEditable(): bool
    {
        return $this === self::Draft;
    }

    public function canIssue(): bool
    {
        return $this === self::Draft;
    }

    public function canReceiveResponses(): bool
    {
        return in_array($this, [self::Open, self::Closed], true);
    }

    public function canCompare(): bool
    {
        return in_array($this, [self::Closed, self::AwaitingComparison, self::Open], true);
    }

    public function canAward(): bool
    {
        return in_array($this, [self::AwaitingComparison, self::Closed], true);
    }

    public function canConvert(): bool
    {
        return $this === self::Awarded;
    }
}
