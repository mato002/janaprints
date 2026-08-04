<?php

namespace App\Support\Operator;

readonly class OperatorModeDefinition
{
    /**
     * @param  list<string>  $permissions
     * @param  list<OperatorModeKey>  $disabledWhen
     * @param  array<string, array<string, mixed>>  $navRemap
     * @param  array<string, mixed>  $homeRouteParams
     * @param  list<string>|null  $sidebarAllowedRoutes  When set, sidebar keeps only these route names after remap.
     */
    public function __construct(
        public OperatorModeKey $key,
        public string $role,
        public array $permissions,
        public string $homeRoute,
        public string $fromKey,
        public string $returnQueryFlag,
        public array $navRemap,
        public array $disabledWhen = [],
        public array $homeRouteParams = [],
        public ?array $sidebarAllowedRoutes = null,
    ) {}

    public function matchesDeskRoute(string $routeName): bool
    {
        return match ($this->key) {
            OperatorModeKey::Production => $routeName === 'admin.production.floor'
                || $routeName === 'admin.production.home'
                || str_starts_with($routeName, 'admin.production.floor.'),
            OperatorModeKey::Sales => $routeName === 'admin.sales.desk'
                || str_starts_with($routeName, 'admin.sales.desk.'),
            OperatorModeKey::Storekeeper => $routeName === 'admin.store.desk'
                || str_starts_with($routeName, 'admin.store.desk.'),
            OperatorModeKey::Designer => $routeName === 'admin.artwork.desk'
                || str_starts_with($routeName, 'admin.artwork.desk.'),
            OperatorModeKey::Dispatch => $routeName === 'admin.dispatch.dashboard'
                || str_starts_with($routeName, 'admin.dispatch.'),
        };
    }

    public function isArtworkFeatureRoute(string $routeName): bool
    {
        if ($this->key !== OperatorModeKey::Designer) {
            return false;
        }

        if ($this->matchesDeskRoute($routeName)) {
            return false;
        }

        return $routeName === 'admin.artwork.dashboard'
            || $routeName === 'admin.artwork.index'
            || str_starts_with($routeName, 'admin.artwork.');
    }
}
