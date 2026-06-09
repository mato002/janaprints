<?php

namespace App\Support\Production;

use App\Support\Platform\SystemSettingsService;

class ProductionSchedulingSettings
{
    public function __construct(
        protected SystemSettingsService $settings,
    ) {}

    public function autoScheduleOnCreate(int $companyId, ?int $branchId): bool
    {
        return (bool) $this->settings->get(
            'production_auto_schedule_on_create',
            false,
            $companyId,
            $branchId,
        );
    }

    public function defaultJobDurationDays(): int
    {
        return max(1, (int) config('production.scheduling.default_job_duration_days', 3));
    }

    public function workCenterCapacity(): int
    {
        return max(1, (int) config('production.scheduling.default_work_center_capacity', 5));
    }
}
