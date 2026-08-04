<?php

namespace App\Support\Operator;

use App\Models\User;

/**
 * Single source of truth for focused operator desks (Production Floor, Designer Desk, Sales Desk, Store Desk).
 */
final class OperatorModeRegistry
{
    /**
     * Roles that always keep the full multi-tab workspace.
     *
     * @var list<string>
     */
    public const ELEVATED_ROLES = [
        'Super Admin',
        'Company Admin',
        'Branch Manager',
    ];

    /**
     * @return list<OperatorModeDefinition>
     */
    public static function modes(): array
    {
        static $modes = null;

        return $modes ??= [
            new OperatorModeDefinition(
                key: OperatorModeKey::Production,
                role: 'Production',
                permissions: ['production.view'],
                homeRoute: 'admin.production.floor',
                fromKey: 'production-floor',
                returnQueryFlag: 'production_floor_return',
                navRemap: [
                    'dashboard' => [
                        'label' => 'Production Floor',
                        'route' => 'admin.production.floor',
                        'route_params' => ['view' => 'queue'],
                        'icon' => 'cog',
                        'active_routes' => [
                            'admin.workspaces.production.section:operations',
                            'admin.production.floor',
                            'admin.production.home',
                            'admin.production.floor.*',
                            'admin.production.job-cards.*',
                            'admin.production.queue.*',
                            'admin.production.outputs.*',
                        ],
                    ],
                    'production' => [
                        'label' => 'Production Floor',
                        'route' => 'admin.production.floor',
                        'route_params' => ['view' => 'queue'],
                        'icon' => 'cog',
                        'active_routes' => [
                            'admin.workspaces.production.section:operations',
                            'admin.production.floor',
                            'admin.production.home',
                            'admin.production.floor.*',
                            'admin.production.job-cards.*',
                            'admin.production.queue.*',
                            'admin.production.outputs.*',
                        ],
                    ],
                ],
                homeRouteParams: ['view' => 'queue'],
                sidebarAllowedRoutes: [
                    'admin.production.floor',
                    'admin.production.home',
                    'admin.workspaces.production',
                    'admin.workspaces.production.section',
                ],
            ),
            new OperatorModeDefinition(
                key: OperatorModeKey::Designer,
                role: 'Designer',
                permissions: ['artwork.view'],
                homeRoute: 'admin.artwork.desk',
                fromKey: 'designer-desk',
                returnQueryFlag: 'designer_desk_return',
                navRemap: [
                    'dashboard' => [
                        'label' => 'Designer Desk',
                        'route' => 'admin.artwork.desk',
                        'route_params' => [],
                        'icon' => 'color-swatch',
                        'active_routes' => [
                            'admin.workspaces.commercial.section:sales',
                            'admin.artwork.desk',
                            'admin.artwork.desk.*',
                            'admin.artwork.show',
                            'admin.artwork.edit',
                        ],
                    ],
                    'commercial' => [
                        'label' => 'Designer Desk',
                        'route' => 'admin.artwork.desk',
                        'route_params' => [],
                        'icon' => 'color-swatch',
                        'active_routes' => [
                            'admin.workspaces.commercial.section:sales',
                            'admin.artwork.desk',
                            'admin.artwork.desk.*',
                            'admin.artwork.show',
                            'admin.artwork.edit',
                        ],
                    ],
                ],
                homeRouteParams: [],
            ),
            new OperatorModeDefinition(
                key: OperatorModeKey::Sales,
                role: 'Sales',
                permissions: ['crm.customers.create', 'sales_orders.create'],
                homeRoute: 'admin.sales.desk',
                fromKey: 'sales-desk',
                returnQueryFlag: 'sales_desk_return',
                navRemap: [
                    'dashboard' => [
                        'label' => 'Sales Desk',
                        'route' => 'admin.sales.desk',
                        'route_params' => [],
                        'icon' => 'shopping-cart',
                        'active_routes' => [
                            'admin.workspaces.commercial.section:sales',
                            'admin.sales.desk',
                            'admin.sales.desk.*',
                            'admin.quotations.*',
                            'admin.sales-orders.*',
                            'admin.artwork.*',
                            'admin.commercial.approvals.*',
                        ],
                    ],
                    'commercial' => [
                        'label' => 'Sales Desk',
                        'route' => 'admin.sales.desk',
                        'route_params' => [],
                        'icon' => 'shopping-cart',
                        'active_routes' => [
                            'admin.workspaces.commercial.section:sales',
                            'admin.sales.desk',
                            'admin.sales.desk.*',
                            'admin.quotations.*',
                            'admin.sales-orders.*',
                            'admin.artwork.*',
                            'admin.commercial.approvals.*',
                        ],
                    ],
                ],
                homeRouteParams: [],
                disabledWhen: [OperatorModeKey::Production],
            ),
            new OperatorModeDefinition(
                key: OperatorModeKey::Dispatch,
                role: 'Dispatch',
                permissions: ['dispatch.view'],
                homeRoute: 'admin.workspaces.dispatch.section',
                fromKey: 'dispatch-desk',
                returnQueryFlag: 'dispatch_desk_return',
                navRemap: [
                    'dashboard' => [
                        'label' => 'Dispatch Desk',
                        'route' => 'admin.workspaces.dispatch.section',
                        'route_params' => ['section' => 'dispatch', 'tab' => 'dispatch-desk'],
                        'icon' => 'truck',
                        'active_routes' => [
                            'admin.workspaces.dispatch.section:dispatch',
                            'admin.dispatch.dashboard',
                            'admin.dispatch.*',
                        ],
                    ],
                    'production' => [
                        'label' => 'Dispatch Desk',
                        'route' => 'admin.workspaces.dispatch.section',
                        'route_params' => ['section' => 'dispatch', 'tab' => 'dispatch-desk'],
                        'icon' => 'truck',
                        'active_routes' => [
                            'admin.workspaces.dispatch.section:dispatch',
                            'admin.dispatch.dashboard',
                            'admin.dispatch.*',
                        ],
                    ],
                    'dispatch' => [
                        'label' => 'Dispatch Desk',
                        'route' => 'admin.workspaces.dispatch.section',
                        'route_params' => ['section' => 'dispatch', 'tab' => 'dispatch-desk'],
                        'icon' => 'truck',
                        'active_routes' => [
                            'admin.workspaces.dispatch.section:dispatch',
                            'admin.dispatch.dashboard',
                            'admin.dispatch.*',
                        ],
                    ],
                ],
                homeRouteParams: ['section' => 'dispatch', 'tab' => 'dispatch-desk'],
            ),
            new OperatorModeDefinition(
                key: OperatorModeKey::Storekeeper,
                role: 'Storekeeper',
                permissions: ['inventory.view'],
                homeRoute: 'admin.workspaces.supply-chain.section',
                fromKey: 'store-desk',
                returnQueryFlag: 'store_desk_return',
                navRemap: [
                    'dashboard' => [
                        'label' => 'Store Desk',
                        'route' => 'admin.workspaces.supply-chain.section',
                        'route_params' => ['section' => 'store-operations', 'tab' => 'store-desk'],
                        'icon' => 'cube',
                        'active_routes' => [
                            'admin.workspaces.supply-chain.section:store-operations',
                            'admin.store.desk',
                            'admin.store.desk.*',
                            'admin.inventory.store.balances',
                            'admin.inventory.receipts.*',
                            'admin.inventory.issues.*',
                            'admin.inventory.transfers.*',
                            'admin.inventory.adjustments.*',
                            'admin.inventory.movements.*',
                            'admin.inventory.alerts.*',
                        ],
                    ],
                    'supply-chain' => [
                        'label' => 'Store Desk',
                        'route' => 'admin.workspaces.supply-chain.section',
                        'route_params' => ['section' => 'store-operations', 'tab' => 'store-desk'],
                        'icon' => 'cube',
                        'active_routes' => [
                            'admin.workspaces.supply-chain.section:store-operations',
                            'admin.store.desk',
                            'admin.store.desk.*',
                            'admin.inventory.store.balances',
                            'admin.inventory.receipts.*',
                            'admin.inventory.issues.*',
                            'admin.inventory.transfers.*',
                            'admin.inventory.adjustments.*',
                            'admin.inventory.movements.*',
                            'admin.inventory.alerts.*',
                        ],
                    ],
                ],
                homeRouteParams: ['section' => 'store-operations', 'tab' => 'store-desk'],
            ),
        ];
    }

    public static function definition(OperatorModeKey $key): OperatorModeDefinition
    {
        foreach (self::modes() as $mode) {
            if ($mode->key === $key) {
                return $mode;
            }
        }

        throw new \InvalidArgumentException("Unknown operator mode [{$key->value}].");
    }

    public static function enabledFor(?User $user, OperatorModeKey $key): bool
    {
        if ($user === null || ! $user->isStaffAccount()) {
            return false;
        }

        if ($user->hasAnyRole(self::ELEVATED_ROLES)) {
            return false;
        }

        $mode = self::definition($key);

        foreach ($mode->disabledWhen as $blockingKey) {
            if (self::enabledFor($user, $blockingKey)) {
                return false;
            }
        }

        foreach ($mode->permissions as $permission) {
            if (! $user->can($permission)) {
                return false;
            }
        }

        return $user->hasRole($mode->role);
    }

    public static function homeUrl(OperatorModeKey $key, array $params = []): string
    {
        $mode = self::definition($key);

        return route($mode->homeRoute, $params !== [] ? $params : $mode->homeRouteParams);
    }

    public static function resolveHomeUrl(?User $user): ?string
    {
        if ($user === null) {
            return null;
        }

        foreach (self::modes() as $mode) {
            if (self::enabledFor($user, $mode->key)) {
                return self::homeUrl($mode->key);
            }
        }

        return null;
    }

    public static function hasAnyOperatorMode(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        foreach (self::modes() as $mode) {
            if (self::enabledFor($user, $mode->key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, array<string, mixed>>|null
     */
    public static function navigationRemap(?User $user): ?array
    {
        if ($user === null) {
            return null;
        }

        foreach (self::modes() as $mode) {
            if (self::enabledFor($user, $mode->key)) {
                return $mode->navRemap;
            }
        }

        return null;
    }

    public static function matchesDeskRoute(OperatorModeKey $key, string $routeName): bool
    {
        return self::definition($key)->matchesDeskRoute($routeName);
    }

    /**
     * @return array{from: string, flag: string, route: string}
     */
    public static function returnConfig(OperatorModeKey $key): array
    {
        $mode = self::definition($key);

        return [
            'from' => $mode->fromKey,
            'flag' => $mode->returnQueryFlag,
            'route' => $mode->homeRoute,
        ];
    }
}
