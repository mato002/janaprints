<?php

namespace App\Services\PrintingIntelligence;

use App\Models\Production\JobCostSheet;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\Quotation;

/**
 * Single revenue precedence for PI8/PI6 actuals (PI9.7).
 *
 * Job Cost Sheet → Sales Order → Quotation
 */
class RevenueResolutionService
{
    public function resolve(
        ProductionJobCard $jobCard,
        ?Quotation $quotation = null,
        ?JobCostSheet $sheet = null,
    ): ?float {
        $jobCard->loadMissing(['salesOrder', 'quotation']);

        if ($sheet !== null && (float) $sheet->revenue > 0) {
            return (float) $sheet->revenue;
        }

        if ($jobCard->salesOrder !== null && (float) $jobCard->salesOrder->total_amount > 0) {
            return (float) $jobCard->salesOrder->total_amount;
        }

        $quotation ??= $jobCard->quotation;

        if ($quotation !== null && (float) $quotation->total_amount > 0) {
            return (float) $quotation->total_amount;
        }

        return null;
    }
}
