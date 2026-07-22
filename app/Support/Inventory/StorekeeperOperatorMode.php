<?php

namespace App\Support\Inventory;

use App\Models\User;
use App\Support\Operator\OperatorModeKey;
use App\Support\Operator\OperatorModeRegistry;

/**
 * Operator mode is a guided Store Desk for warehouse/store staff.
 * Elevated managers/admins keep the full multi-tab Supply Chain workspace.
 */
class StorekeeperOperatorMode
{
    public const ELEVATED_ROLES = OperatorModeRegistry::ELEVATED_ROLES;

    public static function enabledFor(?User $user): bool
    {
        return OperatorModeRegistry::enabledFor($user, OperatorModeKey::Storekeeper);
    }

    public static function homeUrl(): string
    {
        return OperatorModeRegistry::homeUrl(OperatorModeKey::Storekeeper);
    }
}
