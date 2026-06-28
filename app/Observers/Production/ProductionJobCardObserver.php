<?php

namespace App\Observers\Production;

use App\Enums\ProductionJobCardStatus;
use App\Events\Production\JobCardStatusChanged;
use App\Models\Production\ProductionJobCard;
use App\Support\Production\ProductionQueueService;

class ProductionJobCardObserver
{
    public function updating(ProductionJobCard $jobCard): void
    {
        if (! $jobCard->isDirty('status')) {
            return;
        }

        if ($jobCard->status !== ProductionJobCardStatus::Queued) {
            return;
        }

        app(ProductionQueueService::class)->assertQueuedHasActiveRecord($jobCard);
    }

    public function created(ProductionJobCard $jobCard): void
    {
        JobCardStatusChanged::dispatch($jobCard->fresh(), $jobCard->status, null);
    }

    public function updated(ProductionJobCard $jobCard): void
    {
        if (! $jobCard->wasChanged('status')) {
            return;
        }

        $original = $jobCard->getOriginal('status');
        $previous = $original instanceof ProductionJobCardStatus
            ? $original
            : ProductionJobCardStatus::tryFrom((string) $original);

        JobCardStatusChanged::dispatch($jobCard->fresh(), $jobCard->status, $previous);
    }
}
