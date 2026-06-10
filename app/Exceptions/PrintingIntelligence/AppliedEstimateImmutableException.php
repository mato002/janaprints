<?php

namespace App\Exceptions\PrintingIntelligence;

use RuntimeException;

class AppliedEstimateImmutableException extends RuntimeException
{
    public static function forEstimate(int $estimateId): self
    {
        return new self(__('Quotation estimate #:id has been applied and cannot be modified. Clone a new version instead.', [
            'id' => $estimateId,
        ]));
    }
}
