<?php

namespace App\Support\Production;

use App\Models\User;
use App\Support\Operator\OperatorModeKey;
use App\Support\Operator\OperatorModeRegistry;

/**
 * Operator mode is a focused Production desk for shop-floor staff.
 * Elevated managers/admins keep the full multi-tab Production workspace.
 */
class ProductionOperatorMode
{
    public const ELEVATED_ROLES = OperatorModeRegistry::ELEVATED_ROLES;

    public static function enabledFor(?User $user): bool
    {
        return OperatorModeRegistry::enabledFor($user, OperatorModeKey::Production);
    }

    public static function homeUrl(?User $user = null): string
    {
        return ProductionDeskPersona::resolve($user ?? auth()->user())->defaultFloorUrl();
    }

    public static function persona(?User $user = null): ProductionDeskPersona
    {
        return ProductionDeskPersona::resolve($user ?? auth()->user());
    }
}
