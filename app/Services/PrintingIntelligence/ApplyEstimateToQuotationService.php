<?php

namespace App\Services\PrintingIntelligence;

use App\Enums\QuotationEstimationStatus;
use App\Models\PrintingIntelligence\PrintQuotationEstimate;
use App\Models\Sales\Quotation;
use App\Models\User;
use InvalidArgumentException;

class ApplyEstimateToQuotationService
{
    public function apply(PrintQuotationEstimate $estimate, Quotation $quotation, User $user): Quotation
    {
        if (! config('printing_intelligence.allow_apply_to_quotation', true)) {
            throw new InvalidArgumentException(__('Applying estimates to quotations is disabled.'));
        }

        if ((int) $estimate->company_id !== (int) $quotation->company_id) {
            throw new InvalidArgumentException(__('Estimate and quotation belong to different companies.'));
        }

        if (! in_array($estimate->estimation_status, [
            QuotationEstimationStatus::Completed,
            QuotationEstimationStatus::ManualReview,
        ], true)) {
            throw new InvalidArgumentException(__('Only completed or manual-review estimates can be applied.'));
        }

        $originalTotal = (float) $quotation->total_amount;
        $originalSubtotal = (float) $quotation->subtotal;

        $quotation->update([
            'estimated_material_cost' => (float) ($estimate->estimated_material_cost ?? 0),
            'estimated_ink_cost' => (float) ($estimate->estimated_ink_cost ?? 0),
            'estimated_machine_cost' => (float) ($estimate->estimated_machine_cost ?? 0),
            'estimated_labour_cost' => (float) ($estimate->estimated_labour_cost ?? 0),
            'estimated_overhead_cost' => (float) ($estimate->estimated_overhead_cost ?? 0),
            'estimated_total_cost' => (float) ($estimate->estimated_total_cost ?? 0),
            'estimated_margin_percent' => $estimate->expected_margin_percent,
            'recommended_price' => (float) ($estimate->recommended_selling_price ?? 0),
            'confidence_score' => $estimate->confidence_score,
            'estimation_version' => $estimate->formula_version,
        ]);

        $quotation->refresh();

        if ((float) $quotation->total_amount !== $originalTotal || (float) $quotation->subtotal !== $originalSubtotal) {
            throw new InvalidArgumentException(__('Quotation totals must not change when applying advisory estimate.'));
        }

        $estimate->update([
            'quotation_id' => $quotation->id,
            'estimation_status' => QuotationEstimationStatus::AppliedToQuotation,
            'applied_at' => now(),
            'applied_by' => $user->id,
        ]);

        return $quotation->fresh();
    }
}
