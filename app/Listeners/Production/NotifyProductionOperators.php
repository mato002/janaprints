<?php

namespace App\Listeners\Production;

use App\Enums\ProductionJobCardStatus;
use App\Events\Production\JobCardStatusChanged;
use App\Services\Production\ProductionJobNotificationService;

class NotifyProductionOperators
{
    public function __construct(
        protected ProductionJobNotificationService $jobNotifications,
    ) {}

    public function handle(JobCardStatusChanged $event): void
    {
        if ($event->status !== ProductionJobCardStatus::Queued) {
            return;
        }

        $this->jobNotifications->notifyJobQueued($event->jobCard);
    }
}
