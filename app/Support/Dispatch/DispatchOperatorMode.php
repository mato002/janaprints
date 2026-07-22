<?php

namespace App\Support\Dispatch;

use App\Models\User;
use App\Support\Operator\OperatorModeKey;
use App\Support\Operator\OperatorModeRegistry;

/**
 * Operator mode is a focused Dispatch Desk for outbound fulfilment staff.
 */
class DispatchOperatorMode
{
    public const ELEVATED_ROLES = OperatorModeRegistry::ELEVATED_ROLES;

    public static function enabledFor(?User $user): bool
    {
        return OperatorModeRegistry::enabledFor($user, OperatorModeKey::Dispatch);
    }

    public static function homeUrl(): string
    {
        return OperatorModeRegistry::homeUrl(OperatorModeKey::Dispatch);
    }
}
