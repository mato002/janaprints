<?php

namespace App\Support\Navigation;

use App\Support\Sales\SalesOperatorMode;
use App\Support\Platform\ModalFormRoutes;
use Illuminate\Support\Facades\Route;

class WorkspacePresenter
{
    public function __construct(
        protected ?AccountingWorkspacePresenter $accounting = null,
        protected ?SupplyChainWorkspacePresenter $supplyChain = null,
        protected ?CommercialWorkspacePresenter $commercial = null,
        protected ?ProductionWorkspacePresenter $production = null,
        protected ?PrintingIntelligenceWorkspacePresenter $printingIntelligence = null,
        protected ?AdministrationWorkspacePresenter $administration = null,
        protected ?AssetsWorkspacePresenter $assets = null,
        protected ?HrWorkspacePresenter $hr = null,
        protected ?WorkspaceNavigationResolver $navigation = null,
    ) {
        $this->accounting ??= app(AccountingWorkspacePresenter::class);
        $this->supplyChain ??= app(SupplyChainWorkspacePresenter::class);
        $this->commercial ??= app(CommercialWorkspacePresenter::class);
        $this->production ??= app(ProductionWorkspacePresenter::class);
        $this->printingIntelligence ??= app(PrintingIntelligenceWorkspacePresenter::class);
        $this->administration ??= app(AdministrationWorkspacePresenter::class);
        $this->assets ??= app(AssetsWorkspacePresenter::class);
        $this->hr ??= app(HrWorkspacePresenter::class);
        $this->navigation ??= app(WorkspaceNavigationResolver::class);
    }
    /**
     * @return array<string, array<string, mixed>>
     */
    public function definitions(): array
    {
        return config('workspaces', []);
    }

    public function exists(string $key): bool
    {
        return array_key_exists($key, $this->definitions());
    }

    /**
     * @return array{title: string, description: string, icon: string, groups: list<array{label: string, items: list<array<string, mixed>>}>}|null
     */
    public function present(string $key): ?array
    {
        $definition = $this->definitions()[$key] ?? null;

        if ($definition === null) {
            return null;
        }

        $groups = [];

        foreach ($definition['groups'] ?? [] as $group) {
            $items = $this->filterItems($group['items'] ?? []);

            if ($items === []) {
                continue;
            }

            $groups[] = [
                'label' => $group['label'],
                'items' => array_map(fn (array $item) => $this->presentItem($item), $items),
            ];
        }

        return [
            'key' => $key,
            'title' => $definition['title'],
            'description' => $definition['description'],
            'icon' => $definition['icon'] ?? 'home',
            'groups' => $groups,
        ];
    }

    public function isVisible(string $key): bool
    {
        if ($key === 'accounting') {
            return $this->accounting->isVisible();
        }

        if ($key === 'supply-chain') {
            return $this->supplyChain->isVisible();
        }

        if ($key === 'commercial') {
            return $this->commercial->isVisible();
        }

        if ($key === 'designer') {
            return app(DesignerWorkspacePresenter::class)->isVisible();
        }

        if ($key === 'production') {
            return $this->production->isVisible();
        }

        if ($key === 'printing-intelligence') {
            return $this->printingIntelligence->isVisible();
        }

        if ($key === 'administration') {
            return $this->administration->isVisible();
        }

        if ($key === 'assets') {
            return $this->assets->isVisible();
        }

        if ($key === 'hr') {
            return $this->hr->isVisible();
        }

        $definition = $this->definitions()[$key] ?? null;

        if ($definition === null) {
            return false;
        }

        if (! empty($definition['managed_by'])) {
            return $this->isManagedCatalogVisible((string) $definition['managed_by']);
        }

        $hasAccessibleFeature = false;
        $onlyComingSoon = true;

        foreach ($definition['groups'] ?? [] as $group) {
            foreach ($group['items'] ?? [] as $item) {
                if (! empty($item['coming_soon'])) {
                    continue;
                }

                $onlyComingSoon = false;

                if ($this->itemIsAccessible($item)) {
                    $hasAccessibleFeature = true;
                }
            }
        }

        if ($hasAccessibleFeature) {
            return true;
        }

        return $onlyComingSoon && $this->workspaceHasComingSoon($definition);
    }

    /**
     * @return list<string>
     */
    public function collectActiveRoutes(string $key): array
    {
        if ($key === 'accounting') {
            return $this->accounting->collectActiveRoutes();
        }

        if ($key === 'supply-chain') {
            return $this->supplyChain->collectActiveRoutes();
        }

        if ($key === 'commercial') {
            return $this->commercial->collectActiveRoutes();
        }

        if ($key === 'production') {
            return $this->production->collectActiveRoutes();
        }

        if ($key === 'printing-intelligence') {
            return $this->printingIntelligence->collectActiveRoutes();
        }

        if ($key === 'administration') {
            return $this->administration->collectActiveRoutes();
        }

        if ($key === 'assets') {
            return $this->assets->collectActiveRoutes();
        }

        $definition = $this->definitions()[$key] ?? null;

        if ($definition !== null && ! empty($definition['managed_by'])) {
            return $this->collectManagedCatalogActiveRoutes($key, (string) $definition['managed_by']);
        }

        if ($key === 'hr') {
            return $this->hr->collectActiveRoutes();
        }

        $routes = ["admin.workspaces.{$key}"];

        foreach ($this->definitions()[$key]['groups'] ?? [] as $group) {
            foreach ($group['items'] ?? [] as $item) {
                if (! empty($item['coming_soon'])) {
                    continue;
                }

                if (! empty($item['route']) && Route::has($item['route'])) {
                    $routes[] = $item['route'];
                }

                foreach ($item['active_routes'] ?? [] as $pattern) {
                    $routes[] = $pattern;
                }
            }
        }

        return array_values(array_unique($routes));
    }

    /**
     * @return list<array{label: string, path: string, route: ?string, coming_soon: bool}>
     */
    public function flattenForSearch(?string $workspaceKey = null): array
    {
        $flat = [];
        $definitions = $workspaceKey !== null
            ? [$workspaceKey => $this->definitions()[$workspaceKey] ?? []]
            : $this->definitions();

        foreach ($definitions as $key => $definition) {
            if ($definition === []) {
                continue;
            }

            if ($key === 'accounting') {
                $flat = array_merge($flat, $this->accounting->flattenForSearch());

                continue;
            }

            if ($key === 'supply-chain') {
                $flat = array_merge($flat, $this->supplyChain->flattenForSearch());

                continue;
            }

            if ($key === 'commercial') {
                $flat = array_merge($flat, $this->commercial->flattenForSearch());

                continue;
            }

            if ($key === 'production') {
                $flat = array_merge($flat, $this->production->flattenForSearch());

                continue;
            }

            if ($key === 'printing-intelligence') {
                $flat = array_merge($flat, $this->printingIntelligence->flattenForSearch());

                continue;
            }

            if ($key === 'administration') {
                $flat = array_merge($flat, $this->administration->flattenForSearch());

                continue;
            }

            if ($key === 'assets') {
                $flat = array_merge($flat, $this->assets->flattenForSearch());

                continue;
            }

            if ($key === 'hr') {
                $flat = array_merge($flat, $this->hr->flattenForSearch());

                continue;
            }

            $workspaceTitle = $definition['title'] ?? $key;

            foreach ($definition['groups'] ?? [] as $group) {
                $groupLabel = $group['label'] ?? '';

                foreach ($this->filterItems($group['items'] ?? []) as $item) {
                    $flat[] = [
                        'label' => $item['label'] ?? '',
                        'path' => "{$workspaceTitle} › {$groupLabel} › ".($item['label'] ?? ''),
                        'route' => $item['route'] ?? null,
                        'route_params' => $item['route_params'] ?? [],
                        'coming_soon' => (bool) ($item['coming_soon'] ?? false),
                    ];
                }
            }
        }

        return $flat;
    }

    /**
     * @return list<array{label: string, route: ?string, coming_soon: bool, model: ?string}>
     */
    public function quickCreateForRoute(?string $currentRoute): array
    {
        $workspaceKey = $this->resolveWorkspaceForRoute($currentRoute);
        $definition = $workspaceKey !== null ? ($this->definitions()[$workspaceKey] ?? null) : null;
        $items = $definition['quick_create'] ?? $this->defaultQuickCreate();

        $presented = array_values(array_filter(
            array_map(fn (array $item) => $this->presentQuickCreateItem($item), $items),
            fn (array $item) => $item['visible'],
        ));

        if ($presented !== [] || ! SalesOperatorMode::enabledFor(auth()->user())) {
            return $presented;
        }

        $commercialItems = $this->definitions()['commercial']['quick_create'] ?? $this->defaultQuickCreate();

        return array_values(array_filter(
            array_map(fn (array $item) => $this->presentQuickCreateItem($item), $commercialItems),
            fn (array $item) => $item['visible'],
        ));
    }

    public function resolveWorkspaceForRoute(?string $currentRoute): ?string
    {
        if ($currentRoute === null || $currentRoute === '') {
            return null;
        }

        if (in_array($currentRoute, [
            'admin.workspaces.accounting.section',
            'admin.workspaces.supply-chain.section',
            'admin.workspaces.commercial.section',
            'admin.workspaces.administration.section',
            'admin.workspaces.hr.section',
            'admin.workspaces.production.section',
            'admin.workspaces.assets.section',
            'admin.workspaces.communications.section',
            'admin.workspaces.reports.section',
            'admin.workspaces.printing-intelligence.section',
        ], true)) {
            if (str_contains($currentRoute, 'supply-chain')) {
                return 'supply-chain';
            }

            if (str_contains($currentRoute, 'commercial')) {
                return 'commercial';
            }

            if (str_contains($currentRoute, 'administration')) {
                return 'administration';
            }

            if (str_contains($currentRoute, 'printing-intelligence')) {
                return 'printing-intelligence';
            }

            if (str_contains($currentRoute, 'hr')) {
                return 'hr';
            }

            if (str_contains($currentRoute, 'production')) {
                return 'production';
            }

            if (str_contains($currentRoute, 'assets')) {
                return 'assets';
            }

            if (str_contains($currentRoute, 'communications')) {
                return 'communications';
            }

            if (str_contains($currentRoute, 'reports')) {
                return 'reports';
            }

            return 'accounting';
        }

        if (str_starts_with($currentRoute, 'admin.workspaces.')) {
            $key = str_replace('admin.workspaces.', '', $currentRoute);

            if (str_starts_with($key, 'accounting')) {
                return 'accounting';
            }

            if (str_starts_with($key, 'supply-chain')) {
                return 'supply-chain';
            }

            if (str_starts_with($key, 'commercial')) {
                return 'commercial';
            }

            if (str_starts_with($key, 'administration')) {
                return 'administration';
            }

            if (str_starts_with($key, 'printing-intelligence')) {
                return 'printing-intelligence';
            }

            if (str_starts_with($key, 'hr')) {
                return 'hr';
            }

            if (str_starts_with($key, 'production')) {
                return 'production';
            }

            if (str_starts_with($key, 'assets')) {
                return 'assets';
            }

            if (str_starts_with($key, 'communications')) {
                return 'communications';
            }

            if (str_starts_with($key, 'reports')) {
                return 'reports';
            }

            return $key;
        }

        foreach ($this->definitions() as $key => $definition) {
            foreach ($this->collectActiveRoutes($key) as $pattern) {
                if ($pattern === $currentRoute || $this->routeMatchesPattern($currentRoute, $pattern)) {
                    return $key;
                }
            }
        }

        return null;
    }

    /**
     * @return list<array{label: string, route: ?string, coming_soon: bool, model: ?string, visible: bool}>
     */
    protected function defaultQuickCreate(): array
    {
        return [
            ['label' => 'Customer', 'route' => 'admin.crm.customers.create', 'permission' => 'crm.customers.create'],
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return list<array<string, mixed>>
     */
    protected function filterItems(array $items): array
    {
        $filtered = [];

        foreach ($items as $item) {
            if (! empty($item['coming_soon'])) {
                $filtered[] = $item;

                continue;
            }

            if (! $this->itemIsAccessible($item)) {
                continue;
            }

            $filtered[] = $item;
        }

        return $filtered;
    }

    protected function itemIsAccessible(array $item): bool
    {
        if (! empty($item['coming_soon'])) {
            return true;
        }

        if (! empty($item['route']) && ! Route::has($item['route'])) {
            return false;
        }

        if (! empty($item['permission']) && ! $this->userCan($item['permission'])) {
            return false;
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    protected function isVisibleFromDefinition(?array $definition): bool
    {
        if ($definition === null) {
            return false;
        }

        $hasAccessibleFeature = false;
        $onlyComingSoon = true;

        foreach ($definition['groups'] ?? [] as $group) {
            foreach ($group['items'] ?? [] as $item) {
                if (! empty($item['coming_soon'])) {
                    continue;
                }

                $onlyComingSoon = false;

                if ($this->itemIsAccessible($item)) {
                    $hasAccessibleFeature = true;
                }
            }
        }

        if ($hasAccessibleFeature) {
            return true;
        }

        return $onlyComingSoon && $this->workspaceHasComingSoon($definition);
    }

    protected function workspaceHasComingSoon(array $definition): bool
    {
        foreach ($definition['groups'] ?? [] as $group) {
            foreach ($group['items'] ?? [] as $item) {
                if (! empty($item['coming_soon'])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    protected function presentItem(array $item): array
    {
        $comingSoon = (bool) ($item['coming_soon'] ?? false);
        $route = $item['route'] ?? null;
        $href = null;

        if (! $comingSoon && $route && Route::has($route)) {
            $href = $this->navigation->appendPreservedQuery(route($route));
        }

        return [
            'id' => md5(($item['label'] ?? '').($route ?? 'soon')),
            'label' => $item['label'] ?? '',
            'description' => $item['description'] ?? '',
            'icon' => $item['icon'] ?? 'home',
            'href' => $href,
            'comingSoon' => $comingSoon || $href === null,
            'statusLabel' => $comingSoon || $href === null ? __('Coming Soon') : __('Active'),
            'statusVariant' => $comingSoon || $href === null ? 'neutral' : 'success',
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array{label: string, route: ?string, coming_soon: bool, model: ?string, visible: bool}
     */
    protected function presentQuickCreateItem(array $item): array
    {
        $comingSoon = (bool) ($item['coming_soon'] ?? false);
        $route = $item['route'] ?? null;
        $permission = $item['permission'] ?? null;
        $visible = true;

        if ($comingSoon) {
            $visible = true;
        } elseif ($permission !== null && ! $this->userCan($permission)) {
            $visible = false;
        } elseif ($route !== null && ! Route::has($route)) {
            $visible = false;
        }

        return [
            'label' => $item['label'] ?? '',
            'route' => $route,
            'route_params' => $item['route_params'] ?? [],
            'coming_soon' => $comingSoon,
            'model' => $item['model'] ?? null,
            'visible' => $visible,
            'modal' => (bool) ($item['modal'] ?? ModalFormRoutes::supports($route)),
        ];
    }

    protected function userCan(string $permission): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if (str_contains($permission, '|')) {
            foreach (explode('|', $permission) as $segment) {
                if ($user->can(trim($segment))) {
                    return true;
                }
            }

            return false;
        }

        return $user->can($permission);
    }

    protected function routeMatchesPattern(string $currentRoute, string $pattern): bool
    {
        if ($pattern === $currentRoute) {
            return true;
        }

        if (str_contains($pattern, ':')) {
            [$routeName, $paramValue] = explode(':', $pattern, 2);

            if ($currentRoute !== $routeName) {
                return false;
            }

            return request()->route('section') === $paramValue;
        }

        if (str_ends_with($pattern, '.*')) {
            $prefix = substr($pattern, 0, -2);

            return str_starts_with($currentRoute, $prefix);
        }

        return false;
    }

    protected function isManagedCatalogVisible(string $configKey): bool
    {
        $catalog = config($configKey, []);

        if (! is_array($catalog)) {
            return false;
        }

        foreach ($catalog['hub'] ?? [] as $item) {
            if ($this->itemIsAccessible($item)) {
                return true;
            }
        }

        foreach ($catalog['groups'] ?? [] as $group) {
            foreach ($group['items'] ?? [] as $item) {
                if ($this->itemIsAccessible($item)) {
                    return true;
                }
            }
        }

        foreach ($catalog['sections'] ?? [] as $section) {
            if (! empty($section['permission']) && ! $this->userCan($section['permission'])) {
                continue;
            }

            foreach ($section['groups'] ?? [] as $group) {
                foreach ($group['items'] ?? [] as $item) {
                    if ($this->itemIsAccessible($item)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @return list<string>
     */
    protected function collectManagedCatalogActiveRoutes(string $workspaceKey, string $configKey): array
    {
        $catalog = config($configKey, []);
        $routes = ["admin.workspaces.{$workspaceKey}"];
        $sectionRoute = "admin.workspaces.{$workspaceKey}.section";

        if (Route::has($sectionRoute)) {
            $routes[] = $sectionRoute;
        }

        foreach ($catalog['hub'] ?? [] as $item) {
            $routes = array_merge($routes, $this->collectCatalogItemRoutes($item));

            $sectionKey = $item['route_params']['section'] ?? null;

            if ($sectionKey !== null) {
                $routes[] = "{$sectionRoute}:{$sectionKey}";
            }
        }

        foreach ($catalog['sections'] ?? [] as $sectionKey => $section) {
            $routes[] = "{$sectionRoute}:{$sectionKey}";

            foreach ($section['groups'] ?? [] as $group) {
                foreach ($group['items'] ?? [] as $item) {
                    $routes = array_merge($routes, $this->collectCatalogItemRoutes($item));
                }
            }
        }

        foreach ($catalog['groups'] ?? [] as $group) {
            foreach ($group['items'] ?? [] as $item) {
                $routes = array_merge($routes, $this->collectCatalogItemRoutes($item));
            }
        }

        return array_values(array_unique($routes));
    }

    /**
     * @param  array<string, mixed>  $item
     * @return list<string>
     */
    protected function collectCatalogItemRoutes(array $item): array
    {
        $routes = [];

        if (! empty($item['route']) && Route::has($item['route'])) {
            $routes[] = $item['route'];
        }

        foreach ($item['active_routes'] ?? [] as $pattern) {
            $routes[] = $pattern;
        }

        return $routes;
    }
}
