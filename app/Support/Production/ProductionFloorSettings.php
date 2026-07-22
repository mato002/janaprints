<?php

namespace App\Support\Production;

use App\Support\Platform\SystemSettingsService;
use App\Support\TenantContext;

class ProductionFloorSettings
{
    public function __construct(
        protected SystemSettingsService $settings,
        protected TenantContext $tenant,
    ) {}

    /**
     * Model B — planner assigns machine, press, and vendor before release; operator only starts work.
     * Model A (default) — operator picks the machine on the floor.
     */
    public function plannerAssignsMachines(?int $companyId = null, ?int $branchId = null): bool
    {
        $companyId ??= $this->tenant->companyId();
        $branchId ??= $this->tenant->branchId();

        if ($companyId === null) {
            return false;
        }

        return (bool) $this->settings->get(
            'production_planner_assigns_machines',
            false,
            $companyId,
            $branchId,
        );
    }

    public function operatorAssignsMachines(?int $companyId = null, ?int $branchId = null): bool
    {
        return ! $this->plannerAssignsMachines($companyId, $branchId);
    }
}
