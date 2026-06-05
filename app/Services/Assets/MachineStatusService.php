<?php

namespace App\Services\Assets;

use App\Enums\ProductionMachineStatus;
use App\Models\Assets\MachineProfile;
use Illuminate\Support\Facades\DB;

class MachineStatusService
{
    public function __construct(
        protected MachineTimelineService $timeline,
        protected MachineCapacityService $capacity,
    ) {}

    public function changeStatus(MachineProfile $profile, ProductionMachineStatus $status, int $userId): MachineProfile
    {
        return DB::transaction(function () use ($profile, $status, $userId) {
            $previous = $profile->production_status;
            $profile->update(['production_status' => $status]);

            $this->timeline->record(
                $profile->asset,
                'status_changed',
                __('Machine status changed'),
                __('From :from to :to', ['from' => $previous->label(), 'to' => $status->label()]),
                $userId,
                ['from' => $previous->value, 'to' => $status->value],
            );

            return $profile->fresh();
        });
    }
}
