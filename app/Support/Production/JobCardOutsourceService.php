<?php

namespace App\Support\Production;

use App\Enums\ProductionJobCardStatus;
use App\Models\Production\ProductionJobCard;
use App\Models\Procurement\Vendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class JobCardOutsourceService
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function outsource(ProductionJobCard $jobCard, array $payload): ProductionJobCard
    {
        $vendor = Vendor::query()->findOrFail($payload['outsource_vendor_id']);

        if (! $vendor->is_production_vendor) {
            throw ValidationException::withMessages([
                'outsource_vendor_id' => __('Selected vendor is not flagged as a production vendor.'),
            ]);
        }

        if (! $jobCard->status->canTransitionTo(ProductionJobCardStatus::Outsourced)) {
            throw ValidationException::withMessages([
                'status' => __('Job card cannot be outsourced from its current status.'),
            ]);
        }

        return DB::transaction(function () use ($jobCard, $payload) {
            $jobCard->update([
                'outsource_vendor_id' => $payload['outsource_vendor_id'],
                'outsource_issue_date' => $payload['outsource_issue_date'],
                'outsource_expected_return' => $payload['outsource_expected_return'] ?? null,
                'outsource_quoted_cost' => $payload['outsource_quoted_cost'] ?? null,
                'outsource_notes' => $payload['outsource_notes'] ?? null,
                'outsourced_at' => now(),
            ]);

            $jobCard->transitionTo(ProductionJobCardStatus::Outsourced);

            return $jobCard->fresh(['outsourceVendor:id,vendor_name,vendor_code']);
        });
    }

    public function markReturned(ProductionJobCard $jobCard, ?float $actualCost = null): ProductionJobCard
    {
        if (! $jobCard->status->canTransitionTo(ProductionJobCardStatus::Returned)) {
            throw ValidationException::withMessages([
                'status' => __('Job card is not outsourced.'),
            ]);
        }

        return DB::transaction(function () use ($jobCard, $actualCost) {
            $updates = ['returned_at' => now()];

            if ($actualCost !== null) {
                $updates['outsource_actual_cost'] = $actualCost;
            }

            $jobCard->update($updates);
            $jobCard->transitionTo(ProductionJobCardStatus::Returned);

            return $jobCard->fresh(['outsourceVendor:id,vendor_name,vendor_code']);
        });
    }

    /**
     * @return array{quoted_cost: ?float, actual_cost: ?float, margin_impact: ?float}
     */
    public function costExposure(ProductionJobCard $jobCard): array
    {
        return [
            'quoted_cost' => $jobCard->outsource_quoted_cost !== null
                ? (float) $jobCard->outsource_quoted_cost
                : null,
            'actual_cost' => $jobCard->outsource_actual_cost !== null
                ? (float) $jobCard->outsource_actual_cost
                : null,
            'margin_impact' => $jobCard->outsource_actual_cost !== null
                ? (float) $jobCard->outsource_actual_cost
                : ($jobCard->outsource_quoted_cost !== null ? (float) $jobCard->outsource_quoted_cost : null),
        ];
    }
}
