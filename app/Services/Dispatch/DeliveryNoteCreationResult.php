<?php

namespace App\Services\Dispatch;

use App\Models\Dispatch\DeliveryNote;

class DeliveryNoteCreationResult
{
    public function __construct(
        public DeliveryNote $note,
        public bool $wasExisting = false,
        public ?string $message = null,
    ) {}
}
