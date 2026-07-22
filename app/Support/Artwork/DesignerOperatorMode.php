<?php

namespace App\Support\Artwork;

use App\Models\User;
use App\Support\Operator\OperatorModeKey;
use App\Support\Operator\OperatorModeRegistry;

/**
 * Operator mode is a focused Designer Desk for artwork staff.
 * Elevated managers/admins keep the full multi-tab workspace.
 */
class DesignerOperatorMode
{
    public const ELEVATED_ROLES = OperatorModeRegistry::ELEVATED_ROLES;

    public static function enabledFor(?User $user): bool
    {
        return OperatorModeRegistry::enabledFor($user, OperatorModeKey::Designer);
    }

    public static function homeUrl(): string
    {
        return OperatorModeRegistry::homeUrl(OperatorModeKey::Designer);
    }
}
