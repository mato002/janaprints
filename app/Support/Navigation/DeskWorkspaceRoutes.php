<?php

namespace App\Support\Navigation;

use App\Support\Operator\OperatorModeKey;
use App\Support\Operator\OperatorModeRegistry;
use Illuminate\Http\Request;

/**
 * Operator desks that may render outside the module workspace shell.
 *
 * Designer / Sales / Store / Dispatch desks are always standalone.
 * Production floor is standalone for Production operators (or with ?desk=1);
 * managers/admins are redirected into the Production workspace shell.
 */
final class DeskWorkspaceRoutes
{
    /**
     * @return list<string>
     */
    public static function escapeRoutes(): array
    {
        return [
            'admin.store.desk',
            'admin.sales.desk',
            'admin.artwork.desk',
            'admin.procurement.desk',
            'admin.production.floor',
            'admin.dispatch.dashboard',
        ];
    }

    public static function allowsStandalone(string $routeName, ?Request $request = null): bool
    {
        $request ??= request();
        $user = $request->user();

        foreach (OperatorModeRegistry::modes() as $mode) {
            if ($mode->key === OperatorModeKey::Production) {
                continue;
            }

            if ($mode->matchesDeskRoute($routeName)) {
                return true;
            }
        }

        foreach (OperatorModeRegistry::modes() as $mode) {
            if (
                $mode->key === OperatorModeKey::Production
                && $mode->matchesDeskRoute($routeName)
                && (
                    OperatorModeRegistry::enabledFor($user, $mode->key)
                    || $request->boolean('desk')
                )
            ) {
                return true;
            }
        }

        if ($request->boolean('desk') && in_array($routeName, self::escapeRoutes(), true)) {
            return true;
        }

        return false;
    }
}
