<?php

namespace App\Support\Production;

use App\Support\Platform\SystemSettingsService;

/**
 * Company/branch switches for production progress gates.
 *
 * When a control is off, the related step stays on the job card but does not
 * stop release, complete, dispatch, or delivery notes.
 */
class ProductionProcessControlSettings
{
    public const OPERATIONS_KEY = 'production_operations_required_before_dispatch';

    public const ARTWORK_KEY = 'artwork_requires_customer_approval';

    public function __construct(
        protected SystemSettingsService $settings,
        protected ProductionQcSettings $qc,
        protected ProductionInventoryControlSettings $inventory,
    ) {}

    public function qcRequired(?int $companyId, ?int $branchId = null): bool
    {
        if ($companyId === null) {
            return false;
        }

        return $this->qc->qcRequired($companyId, $branchId);
    }

    public function operationsRequiredBeforeDispatch(?int $companyId, ?int $branchId = null): bool
    {
        return $this->boolSetting(self::OPERATIONS_KEY, true, $companyId, $branchId);
    }

    public function artworkApprovalRequired(?int $companyId, ?int $branchId = null): bool
    {
        return $this->boolSetting(self::ARTWORK_KEY, true, $companyId, $branchId);
    }

    public function inventoryEnforced(?int $companyId, ?int $branchId = null): bool
    {
        return $this->inventory->enforced($companyId, $branchId);
    }

    protected function boolSetting(string $key, bool $default, ?int $companyId, ?int $branchId): bool
    {
        if ($companyId === null) {
            return $default;
        }

        return filter_var(
            $this->settings->get($key, $default, $companyId, $branchId),
            FILTER_VALIDATE_BOOLEAN,
        );
    }
}
