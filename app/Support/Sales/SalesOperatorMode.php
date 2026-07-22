<?php

namespace App\Support\Sales;

use App\Models\User;
use App\Support\Operator\OperatorModeKey;
use App\Support\Operator\OperatorModeRegistry;

/**
 * Operator mode is a guided Sales Desk for front-desk / walk-in staff.
 * Elevated managers/admins keep the full multi-tab Commercial workspace.
 */
class SalesOperatorMode
{
    public const ELEVATED_ROLES = OperatorModeRegistry::ELEVATED_ROLES;

    public static function enabledFor(?User $user): bool
    {
        return OperatorModeRegistry::enabledFor($user, OperatorModeKey::Sales);
    }

    public static function homeUrl(): string
    {
        return OperatorModeRegistry::homeUrl(OperatorModeKey::Sales);
    }
}
