<?php

namespace App\Events\Dispatch;

use App\Models\Dispatch\DeliveryNote;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeliveryNoteDelivered
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public DeliveryNote $deliveryNote,
    ) {}
}
