<?php

namespace App\Support\Dashboard;

use App\Models\User;
use Illuminate\Support\Facades\Route;

/**
 * Quick links to focused operator desks for elevated admins and solo owners.
 * Frontline operators already land on their desk and do not need these shortcuts.
 */
class OperatorDeskShortcutsPresenter
{
    /**
     * @return list<array{key: string, label: string, description: string, url: string, icon: string}>
     */
    public function forUser(?User $user): array
    {
        if ($user === null || ! $this->shouldShow($user)) {
            return [];
        }

        $shortcuts = [];

        foreach ($this->definitions() as $definition) {
            if (! $this->userCanAccess($user, $definition)) {
                continue;
            }

            $route = $definition['route'];

            if (! Route::has($route)) {
                continue;
            }

            $shortcuts[] = [
                'key' => $definition['key'],
                'label' => $definition['label'],
                'description' => $definition['description'],
                'url' => route($route, $definition['route_params'] ?? []),
                'icon' => $definition['icon'],
            ];
        }

        return $shortcuts;
    }

    public function shouldShow(?User $user): bool
    {
        if ($user === null || ! $user->isStaffAccount()) {
            return false;
        }

        return ! $user->hasOperatorDeskMode();
    }

    /**
     * @return list<array{
     *     key: string,
     *     label: string,
     *     description: string,
     *     route: string,
     *     route_params?: array<string, mixed>,
     *     icon: string,
     *     permissions: list<string>
     * }>
     */
    protected function definitions(): array
    {
        return [
            [
                'key' => 'sales',
                'label' => __('Sales Desk'),
                'description' => __('Walk-in customer to production release'),
                'route' => 'admin.workspaces.commercial.section',
                'route_params' => ['section' => 'sales', 'tab' => 'sales-desk'],
                'icon' => 'shopping-cart',
                'permissions' => ['crm.customers.create', 'sales_orders.create'],
            ],
            [
                'key' => 'designer',
                'label' => __('Designer Desk'),
                'description' => __('Artwork queue, uploads, and approvals'),
                'route' => 'admin.workspaces.designer.section',
                'route_params' => ['section' => 'design', 'tab' => 'designer-desk'],
                'icon' => 'color-swatch',
                'permissions' => ['artwork.view'],
            ],
            [
                'key' => 'production',
                'label' => __('Production Floor'),
                'description' => __('Shop floor jobs and next-step actions'),
                'route' => 'admin.workspaces.production.section',
                'route_params' => ['section' => 'operations', 'tab' => 'production-floor'],
                'icon' => 'cog',
                'permissions' => ['production.view'],
            ],
            [
                'key' => 'dispatch',
                'label' => __('Dispatch Desk'),
                'description' => __('Ready jobs, delivery notes, and confirmations'),
                'route' => 'admin.workspaces.dispatch.section',
                'route_params' => ['section' => 'dispatch', 'tab' => 'dispatch-desk'],
                'icon' => 'truck',
                'permissions' => ['dispatch.view'],
            ],
            [
                'key' => 'buy',
                'label' => __('Buy Desk'),
                'description' => __('Purchase requests, suppliers, and receiving'),
                'route' => 'admin.workspaces.supply-chain.section',
                'route_params' => ['section' => 'procurement', 'tab' => 'buy-desk'],
                'icon' => 'truck',
                'permissions' => ['procurement.vendors.view'],
            ],
            [
                'key' => 'store',
                'label' => __('Store Desk'),
                'description' => __('Receive, issue, and count stock'),
                'route' => 'admin.workspaces.supply-chain.section',
                'route_params' => ['section' => 'store-operations', 'tab' => 'store-desk'],
                'icon' => 'cube',
                'permissions' => ['inventory.view'],
            ],
        ];
    }

    /**
     * @param  array{permissions: list<string>}  $definition
     */
    protected function userCanAccess(User $user, array $definition): bool
    {
        foreach ($definition['permissions'] as $permission) {
            if (! $user->can($permission)) {
                return false;
            }
        }

        return true;
    }
}
