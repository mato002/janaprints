<?php

namespace App\Support\Production;

use App\Support\Platform\SystemSettingsService;

/**
 * Maker vs checker for inventory-backed production steps.
 *
 * When enforced (checker), material readiness, consumption, and finished-goods
 * posting block queue / complete / dispatch. When off (maker), those steps stay
 * available on the job card but do not stop production.
 */
class ProductionInventoryControlSettings
{
    public function __construct(
        protected SystemSettingsService $settings,
    ) {}

    public function enforced(?int $companyId, ?int $branchId = null): bool
    {
        if ($companyId === null) {
            return true;
        }

        return filter_var(
            $this->settings->get(
                'production_inventory_controls_enforced',
                true,
                $companyId,
                $branchId,
            ),
            FILTER_VALIDATE_BOOLEAN,
        );
    }

    public function materialReadinessRequired(?int $companyId, ?int $branchId = null): bool
    {
        return $this->enforced($companyId, $branchId);
    }

    public function materialConsumptionRequired(?int $companyId, ?int $branchId = null): bool
    {
        return $this->enforced($companyId, $branchId);
    }

    public function finishedGoodsRequiredBeforeDispatch(?int $companyId, ?int $branchId = null): bool
    {
        return $this->enforced($companyId, $branchId);
    }
}
