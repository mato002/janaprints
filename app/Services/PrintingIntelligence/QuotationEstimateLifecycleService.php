<?php

namespace App\Services\PrintingIntelligence;

use App\Enums\QuotationEstimationStatus;
use App\Exceptions\PrintingIntelligence\AppliedEstimateImmutableException;
use App\Models\PrintingIntelligence\PrintQuotationEstimate;

class QuotationEstimateLifecycleService
{
    /**
     * @param  array{
     *     print_artwork_analysis_id: int,
     *     quantity: int,
     *     material_inventory_item_id: int|null
     * }  $lookup
     */
    public function findLatestForLookup(array $lookup): ?PrintQuotationEstimate
    {
        return PrintQuotationEstimate::query()
            ->where('print_artwork_analysis_id', $lookup['print_artwork_analysis_id'])
            ->where('quantity', $lookup['quantity'])
            ->where('material_inventory_item_id', $lookup['material_inventory_item_id'])
            ->orderByDesc('version')
            ->first();
    }

    /**
     * @param  array{
     *     print_artwork_analysis_id: int,
     *     quantity: int,
     *     material_inventory_item_id: int|null
     * }  $lookup
     */
    public function resolveWritableLookup(array $lookup): array
    {
        $latest = $this->findLatestForLookup($lookup);

        if ($latest === null) {
            return array_merge($lookup, ['version' => 1]);
        }

        if ($latest->applied_at !== null) {
            return array_merge($lookup, ['version' => (int) ($latest->version ?? 1) + 1]);
        }

        return array_merge($lookup, ['version' => (int) ($latest->version ?? 1)]);
    }

    public function assertMutable(PrintQuotationEstimate $estimate): void
    {
        if ($estimate->applied_at !== null) {
            throw AppliedEstimateImmutableException::forEstimate((int) $estimate->id);
        }
    }

    public function cloneEstimate(PrintQuotationEstimate $estimate): PrintQuotationEstimate
    {
        $nextVersion = PrintQuotationEstimate::query()
            ->where('print_artwork_analysis_id', $estimate->print_artwork_analysis_id)
            ->where('quantity', $estimate->quantity)
            ->where('material_inventory_item_id', $estimate->material_inventory_item_id)
            ->max('version');

        $clone = $estimate->replicate(['applied_at', 'applied_by']);
        $clone->version = (int) ($nextVersion ?? $estimate->version ?? 1) + 1;
        $clone->estimation_status = QuotationEstimationStatus::Completed;
        $clone->applied_at = null;
        $clone->applied_by = null;
        $clone->metadata = array_merge($estimate->metadata ?? [], [
            'cloned_from_estimate_id' => $estimate->id,
            'cloned_at' => now()->toIso8601String(),
        ]);
        $clone->save();

        return $clone;
    }
}
