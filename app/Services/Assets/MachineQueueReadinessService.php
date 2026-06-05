<?php

namespace App\Services\Assets;

use App\Models\Assets\MachineProfile;

class MachineQueueReadinessService
{
    public function __construct(
        protected MachineCapacityService $capacity,
        protected MachineAvailabilityService $availability,
    ) {}

    /**
     * @return array{
     *     jobs_assigned: int,
     *     jobs_waiting: int,
     *     capacity_remaining: float,
     *     availability: array<string, mixed>,
     *     ready: bool
     * }
     */
    public function readiness(MachineProfile $profile): array
    {
        $metrics = $this->capacity->profileMetrics($profile);
        $availability = $this->availability->evaluate($profile);

        return [
            'jobs_assigned' => $metrics['assigned_jobs'],
            'jobs_waiting' => $metrics['assigned_jobs'],
            'capacity_remaining' => $metrics['capacity_remaining'],
            'availability' => $availability,
            'ready' => $availability['state']->value !== 'unavailable',
        ];
    }
}
