<?php

namespace App\Listeners\Production;

use App\Events\Production\JobCardStatusChanged;
use App\Support\Production\ProductionQueueService;

class SyncProductionQueueFromJobCard
{
    public function __construct(
        protected ProductionQueueService $queues,
    ) {}

    public function handle(JobCardStatusChanged $event): void
    {
        $this->queues->syncFromJobStatus($event->jobCard, $event->status);
    }
}
