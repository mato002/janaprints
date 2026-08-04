<?php

namespace App\Support\Production;

use App\Models\User;
use App\Support\Operator\OperatorModeKey;
use App\Support\Operator\OperatorModeRegistry;

/**
 * Role-based Production desk experience.
 * Features stay in the system; each persona only sees the views meant for them.
 */
enum ProductionDeskPersona: string
{
    case Admin = 'admin';
    case Manager = 'manager';
    case Operator = 'operator';

    public static function resolve(?User $user): self
    {
        if ($user === null || ! $user->isStaffAccount()) {
            return self::Admin;
        }

        if ($user->hasRole(['Super Admin', 'Company Admin'])) {
            return self::Admin;
        }

        if ($user->hasRole(['Branch Manager', 'Production Manager'])) {
            return self::Manager;
        }

        if (OperatorModeRegistry::enabledFor($user, OperatorModeKey::Production)) {
            return self::Operator;
        }

        return self::Admin;
    }

    public function isAdmin(): bool
    {
        return $this === self::Admin;
    }

    public function isManager(): bool
    {
        return $this === self::Manager;
    }

    public function isOperator(): bool
    {
        return $this === self::Operator;
    }

    public function usesDepartmentOperationsModes(): bool
    {
        return $this === self::Operator || $this === self::Manager;
    }

    public function operationsHubOnly(): bool
    {
        return $this === self::Operator;
    }

    /**
     * Desk context modes for Operations → Production Floor.
     *
     * @return list<array{key: string, label: string, route: string, route_params: array<string, mixed>, active_routes?: list<string>}>
     */
    public function operationsFloorModes(): array
    {
        if ($this === self::Admin) {
            return [];
        }

        $modes = [];

        if ($this === self::Manager) {
            $modes[] = [
                'key' => 'overview',
                'label' => __('Overview'),
                'route' => 'admin.production.floor',
                'route_params' => [],
                'active_routes' => ['admin.production.floor', 'admin.production.home'],
            ];
        }

        foreach (app(DepartmentQueueRegistry::class)->availableDepartments() as $slug => $department) {
            $modes[] = [
                'key' => $slug,
                'label' => $department['label'],
                'route' => 'admin.production.floor',
                'route_params' => [
                    'view' => ProductionFloorDeskViews::QUEUE,
                    'department' => $slug,
                ],
                'active_routes' => ['admin.production.queue.*'],
            ];
        }

        return $modes;
    }

    /**
     * Standalone floor desk tabs (when not inside the module workspace shell).
     *
     * @return list<array{key: string, label: string, url: string}>
     */
    public function standaloneFloorModes(?string $activeDepartment = null): array
    {
        if ($this === self::Admin) {
            return [];
        }

        $modes = [];

        if ($this === self::Manager) {
            $modes[] = [
                'key' => ProductionFloorDeskViews::FLOOR,
                'label' => __('Overview'),
                'url' => ProductionFloorDeskViews::floorUrl(ProductionFloorDeskViews::FLOOR),
            ];
        }

        foreach (app(DepartmentQueueRegistry::class)->availableDepartments() as $slug => $department) {
            $modes[] = [
                'key' => $slug,
                'label' => $department['label'],
                'url' => ProductionFloorDeskViews::queueIndexUrl($slug),
            ];
        }

        return $modes;
    }

    public function defaultFloorUrl(): string
    {
        if ($this === self::Admin) {
            return ProductionFloorDeskViews::floorUrl();
        }

        if ($this === self::Manager) {
            return ProductionFloorDeskViews::floorUrl(ProductionFloorDeskViews::FLOOR);
        }

        $first = array_key_first(app(DepartmentQueueRegistry::class)->availableDepartments());

        return $first
            ? ProductionFloorDeskViews::queueIndexUrl($first)
            : ProductionFloorDeskViews::queueIndexUrl();
    }

    public function prefersQueueLanding(): bool
    {
        return $this === self::Operator;
    }
}
