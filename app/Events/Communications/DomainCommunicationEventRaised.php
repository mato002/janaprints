<?php

namespace App\Events\Communications;

use App\Enums\DomainCommunicationEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DomainCommunicationEventRaised
{
    use Dispatchable, SerializesModels;

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public DomainCommunicationEvent $event,
        public Model $subject,
        public ?User $actor = null,
        public array $metadata = [],
    ) {}
}
