<?php

namespace App\Events\Production;

use App\Enums\ProductionJobCardStatus;
use App\Models\Production\ProductionJobCard;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JobCardStatusChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public ProductionJobCard $jobCard,
        public ProductionJobCardStatus $status,
        public ?ProductionJobCardStatus $previousStatus = null,
    ) {}
}
