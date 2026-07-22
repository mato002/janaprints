<?php

namespace App\Support\Production;

use App\Models\User;
use App\Support\Operator\OperatorModeKey;
use App\Support\Operator\OperatorModeRegistry;

/**
 * Operator mode is a POS-style Production Floor console for shop-floor staff.
 * Elevated managers/admins keep the full multi-tab Production workspace.
 */
class ProductionOperatorMode
{
    public const ELEVATED_ROLES = OperatorModeRegistry::ELEVATED_ROLES;

    public static function enabledFor(?User $user): bool
    {
        return OperatorModeRegistry::enabledFor($user, OperatorModeKey::Production);
    }

    public static function homeUrl(): string
    {
        return OperatorModeRegistry::homeUrl(OperatorModeKey::Production);
    }
}
