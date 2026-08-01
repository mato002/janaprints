<?php

namespace App\Support\Navigation;

use Illuminate\Http\Request;

/**
 * Operator desks that may render standalone only with ?desk=1 (or turbo-frame embed).
 * Full-page visits redirect into the module workspace shell.
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

    public static function allowsStandalone(string $routeName, Request $request = null): bool
    {
        $request ??= request();

        if (! in_array($routeName, self::escapeRoutes(), true)) {
            return false;
        }

        return $request->boolean('desk');
    }
}
