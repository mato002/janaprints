<?php

namespace App\Services\Dispatch;

use App\Enums\Dispatch\DeliveryNoteStatus;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Production\ProductionJobCard;
use Illuminate\Validation\ValidationException;

/**
 * Single authority for delivery note creation from production/dispatch entry points.
 */
class DeliveryNoteAuthority
{
    public function __construct(
        protected DeliveryNoteService $deliveryNotes,
    ) {}

    public function findActiveForJobCard(ProductionJobCard $jobCard): ?DeliveryNote
    {
        return DeliveryNote::query()
            ->where('company_id', $jobCard->company_id)
            ->where('production_job_card_id', $jobCard->id)
            ->whereNotIn('status', [DeliveryNoteStatus::Cancelled->value])
            ->orderByDesc('id')
            ->first();
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  list<array<string, mixed>>|null  $items
     */
    public function createDraftFromJobCard(
        ProductionJobCard $jobCard,
        array $attributes = [],
        ?array $items = null,
    ): DeliveryNoteCreationResult {
        $existing = $this->findActiveForJobCard($jobCard);

        if ($existing !== null) {
            return new DeliveryNoteCreationResult(
                $existing,
                wasExisting: true,
                message: __('An active delivery note already exists for this job.'),
            );
        }

        try {
            $note = $this->deliveryNotes->createDraftFromJobCard($jobCard, $attributes, $items);
        } catch (ValidationException $exception) {
            $existing = $this->findActiveForJobCard($jobCard->fresh());

            if ($existing !== null) {
                return new DeliveryNoteCreationResult(
                    $existing,
                    wasExisting: true,
                    message: __('An active delivery note already exists for this job.'),
                );
            }

            throw $exception;
        }

        return new DeliveryNoteCreationResult($note);
    }
}
