<?php

namespace App\Support\Communications;

use App\Enums\DomainCommunicationEvent;
use App\Events\Communications\DomainCommunicationEventRaised;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CommunicationEventDispatcher
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function dispatch(
        DomainCommunicationEvent $event,
        Model $subject,
        ?User $actor = null,
        array $metadata = [],
    ): void {
        DomainCommunicationEventRaised::dispatch($event, $subject, $actor, $metadata);
    }
}
