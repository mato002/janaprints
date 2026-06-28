<?php

namespace App\Listeners\Production;

use App\Enums\DomainCommunicationEvent;
use App\Enums\ProductionJobCardStatus;
use App\Events\Production\JobCardStatusChanged;
use App\Support\Communications\CommunicationEventDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;

class DispatchProductionCommunication implements ShouldQueue
{
    public function __construct(
        protected CommunicationEventDispatcher $dispatcher,
    ) {}

    public function handle(JobCardStatusChanged $event): void
    {
        $fromQc = in_array($event->previousStatus, [
            ProductionJobCardStatus::QualityCheck,
            ProductionJobCardStatus::AwaitingCustomerApproval,
        ], true);

        if ($event->status === ProductionJobCardStatus::ReadyForDispatch) {
            if ($fromQc) {
                $this->dispatcher->dispatch(DomainCommunicationEvent::QualityApproved, $event->jobCard);
            }

            return;
        }

        $domainEvent = match ($event->status) {
            ProductionJobCardStatus::Queued => DomainCommunicationEvent::JobQueued,
            ProductionJobCardStatus::InProduction => DomainCommunicationEvent::ProductionStarted,
            default => null,
        };

        if ($domainEvent === null) {
            return;
        }

        $this->dispatcher->dispatch($domainEvent, $event->jobCard);
    }
}
