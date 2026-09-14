<?php

namespace App\Support\Production;

use App\Support\Platform\SystemSettingsService;

class ProductionQcSettings
{
    public function __construct(
        protected SystemSettingsService $settings,
    ) {}

    public function qcRequired(int $companyId, ?int $branchId = null): bool
    {
        return filter_var(
            $this->settings->get(
                'production_qc_required',
                false,
                $companyId,
                $branchId,
            ),
            FILTER_VALIDATE_BOOLEAN,
        );
    }
}
