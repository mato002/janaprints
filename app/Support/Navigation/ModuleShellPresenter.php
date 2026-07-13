<?php

namespace App\Support\Navigation;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class ModuleShellPresenter
{
    public function __construct(
        protected ?WorkspaceNavigationResolver $navigation = null,
    ) {
        $this->navigation ??= app(WorkspaceNavigationResolver::class);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function moduleDefinitions(): array
    {
        return [
            'commercial' => [
                'config' => 'commercial_workspaces',
                'title' => __('Commercial'),
                'description' => __('CRM, sales, customer service, point of sale, and commercial reporting.'),
                'icon' => 'shopping-cart',
                'hub_route' => 'admin.workspaces.commercial',
                'section_route' => 'admin.workspaces.commercial.section',
                'type' => 'sectioned',
            ],
            'supply-chain' => [
                'config' => 'supply_chain_workspaces',
                'title' => __('Supply Chain'),
                'description' => __('Catalogue, store operations, procurement, inventory control, and reports.'),
                'icon' => 'cube',
                'hub_route' => 'admin.workspaces.supply-chain',
                'section_route' => 'admin.workspaces.supply-chain.section',
                'type' => 'sectioned',
            ],
            'accounting' => [
                'config' => 'accounting_workspaces',
                'title' => __('Accounting'),
                'description' => __('Finance command center organized into ledger, receivables, payables, tax, and setup workspaces.'),
                'icon' => 'currency-dollar',
                'hub_route' => 'admin.workspaces.accounting',
                'section_route' => 'admin.workspaces.accounting.section',
                'type' => 'sectioned',
            ],
            'administration' => [
                'config' => 'administration_workspaces',
                'title' => __('Administration'),
                'description' => __('Access control, organization structure, settings, and audit.'),
                'icon' => 'shield-check',
                'hub_route' => 'admin.workspaces.administration',
                'section_route' => 'admin.workspaces.administration.section',
                'type' => 'sectioned',
            ],
            'production' => [
                'config' => 'production_workspaces',
                'title' => __('Production'),
                'description' => __('Job cards, shop floor, quality, dispatch, and production intelligence.'),
                'icon' => 'cog',
                'hub_route' => 'admin.workspaces.production',
                'section_route' => 'admin.workspaces.production.section',
                'type' => 'sectioned',
            ],
            'assets' => [
                'config' => 'assets_workspaces',
                'title' => __('Assets'),
                'description' => __('Fixed assets, maintenance schedules, and depreciation.'),
                'icon' => 'chip',
                'hub_route' => 'admin.workspaces.assets',
                'section_route' => 'admin.workspaces.assets.section',
                'type' => 'grouped',
            ],
            'hr' => [
                'config' => 'hr_workspaces',
                'title' => __('HR'),
                'description' => __('Employees, attendance, leave, payroll, and HR records.'),
                'icon' => 'identification',
                'hub_route' => 'admin.workspaces.hr',
                'section_route' => 'admin.workspaces.hr.section',
                'type' => 'sectioned',
            ],
            'communications' => [
                'config' => 'communications_workspaces',
                'title' => __('Communications'),
                'description' => __('SMS, email, campaigns, templates, and notification logs.'),
                'icon' => 'inbox',
                'hub_route' => 'admin.workspaces.communications',
                'section_route' => 'admin.workspaces.communications.section',
                'type' => 'sectioned',
            ],
            'reports' => [
                'config' => 'reports_workspaces',
                'title' => __('Reports & Intelligence'),
                'description' => __('Executive dashboards, module reports, and KPI center.'),
                'icon' => 'chart-pie',
                'hub_route' => 'admin.workspaces.reports',
                'section_route' => 'admin.workspaces.reports.section',
                'type' => 'sectioned',
            ],
            'dispatch' => [
                'config' => 'dispatch_workspaces',
                'title' => __('Dispatch'),
                'description' => __('Delivery notes, dispatch lifecycle, and outbound delivery truth.'),
                'icon' => 'truck',
                'hub_route' => 'admin.workspaces.dispatch',
                'section_route' => 'admin.workspaces.dispatch.section',
                'type' => 'sectioned',
            ],
            'printing-intelligence' => [
                'config' => 'printing_intelligence_workspaces',
                'title' => __('Printing Intelligence'),
                'description' => __('Trusted cost bridge for materials, machines, ink, quotations, and production reality.'),
                'icon' => 'color-swatch',
                'hub_route' => 'admin.workspaces.printing-intelligence',
                'section_route' => 'admin.workspaces.printing-intelligence.section',
                'type' => 'sectioned',
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function presentDesk(string $moduleKey, ?string $primaryKey = null, ?string $tabKey = null, ?Request $request = null): ?array
    {
        $module = $this->moduleDefinitions()[$moduleKey] ?? null;

        if ($module === null) {
            return null;
        }

        $request ??= request();

        if (($module['type'] ?? '') === 'grouped') {
            return $this->presentGroupedDesk($moduleKey, $module, $primaryKey, $tabKey, $request);
        }

        return $this->presentSectionedDesk($moduleKey, $module, $primaryKey, $tabKey, $request);
    }

    /**
     * @return array{url: string}|null
     */
    public function defaultDesk(string $moduleKey): ?array
    {
        $desk = $this->presentDesk($moduleKey);

        if ($desk === null) {
            return null;
        }

        $primary = $desk['active_primary'] ?? null;
        $secondary = $desk['active_secondary'] ?? null;

        if ($primary === null) {
            return null;
        }

        $module = $this->moduleDefinitions()[$moduleKey];
        $catalog = $this->loadCatalog($module);
        $sectionRoute = $module['section_route'] ?? null;
        $primaryHasSection = isset($catalog['sections'][$primary['key'] ?? ''])
            || (($module['type'] ?? '') === 'grouped' && $this->groupedSectionExists($catalog, $primary['key'] ?? ''));

        if ($sectionRoute && Route::has($sectionRoute) && $primaryHasSection) {
            $url = route($sectionRoute, ['section' => $primary['key']]);

            if ($secondary !== null) {
                $url = $this->appendQuery($url, ['tab' => $secondary['key']]);
            }

            return ['url' => $this->navigation->appendPreservedQuery($url) ?? $url];
        }

        if (! $primaryHasSection) {
            return null;
        }

        $hubRoute = $module['hub_route'] ?? null;

        if ($hubRoute && Route::has($hubRoute)) {
            $params = ['workspace' => $primary['key']];

            if ($secondary !== null) {
                $params['tab'] = $secondary['key'];
            }

            $url = route($hubRoute, $params);

            return ['url' => $this->navigation->appendPreservedQuery($url) ?? $url];
        }

        return null;
    }

    /**
     * Resolve the module desk URL for a feature route registered in a workspace catalog.
     *
     * @param  array<string, mixed>  $routeParams
     */
    public function deskUrlForFeatureRoute(string $routeName, array $routeParams = []): ?string
    {
        foreach ($this->moduleDefinitions() as $moduleKey => $module) {
            $catalog = $this->loadCatalog($module);

            if ($catalog === null) {
                continue;
            }

            $url = $this->deskUrlForFeatureRouteInCatalog($moduleKey, $module, $catalog, $routeName, $routeParams);

            if ($url !== null) {
                return $url;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $module
     * @param  array<string, mixed>  $catalog
     * @param  array<string, mixed>  $routeParams
     */
    protected function deskUrlForFeatureRouteInCatalog(
        string $moduleKey,
        array $module,
        array $catalog,
        string $routeName,
        array $routeParams,
    ): ?string {
        if (($module['type'] ?? '') === 'grouped') {
            foreach ($catalog['groups'] ?? [] as $group) {
                $sectionKey = $this->groupKey($group);

                foreach ($group['items'] ?? [] as $item) {
                    if ($this->shouldSkipCrossModuleWorkspaceItem($module, $item)) {
                        continue;
                    }

                    if ($this->shouldSkipDeskRedirectItem($item)) {
                        continue;
                    }

                    if (! $this->featureRouteMatchesItem($routeName, $routeParams, $item)) {
                        continue;
                    }

                    return $this->buildDeskUrl(
                        $moduleKey,
                        $module,
                        $sectionKey,
                        $this->itemKey($item),
                    );
                }
            }

            return null;
        }

        foreach ($catalog['sections'] ?? [] as $sectionKey => $section) {
            foreach ($section['groups'] ?? [] as $group) {
                foreach ($group['items'] ?? [] as $item) {
                    if ($this->shouldSkipCrossModuleWorkspaceItem($module, $item)) {
                        continue;
                    }

                    if ($this->shouldSkipDeskRedirectItem($item)) {
                        continue;
                    }

                    if (! $this->featureRouteMatchesItem($routeName, $routeParams, $item)) {
                        continue;
                    }

                    return $this->buildDeskUrl(
                        $moduleKey,
                        $module,
                        $sectionKey,
                        $this->itemKey($item),
                    );
                }
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $module
     * @param  array<string, mixed>  $item
     */
    protected function shouldSkipCrossModuleWorkspaceItem(array $module, array $item): bool
    {
        $itemRoute = $item['route'] ?? null;

        if (! is_string($itemRoute) || ! str_starts_with($itemRoute, 'admin.workspaces.')) {
            return false;
        }

        $hubRoute = $module['hub_route'] ?? null;
        $sectionRoute = $module['section_route'] ?? null;

        return $itemRoute !== $hubRoute && $itemRoute !== $sectionRoute;
    }

    /**
     * Shared feature routes can appear in multiple module desks; skip items that
     * should not win canonical full-page redirects (e.g. Administration org mirror).
     *
     * @param  array<string, mixed>  $item
     */
    protected function shouldSkipDeskRedirectItem(array $item): bool
    {
        return ! empty($item['skip_desk_redirect']);
    }

    /**
     * @param  array<string, mixed>  $catalog
     */
    protected function groupedSectionExists(array $catalog, string $sectionKey): bool
    {
        foreach ($catalog['groups'] ?? [] as $group) {
            if ($this->groupKey($group) === $sectionKey) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $module
     */
    public function buildDeskUrl(string $moduleKey, array $module, string $sectionKey, ?string $tabKey = null): ?string
    {
        $sectionRoute = $module['section_route'] ?? null;

        if (! $sectionRoute || ! Route::has($sectionRoute)) {
            return null;
        }

        $url = route($sectionRoute, ['section' => $sectionKey]);

        if ($tabKey !== null && $tabKey !== '') {
            $url = $this->appendQuery($url, ['tab' => $tabKey]);
        }

        return $this->navigation->appendPreservedQuery($url);
    }

    /**
     * @param  array<string, mixed>  $routeParams
     * @param  array<string, mixed>  $item
     */
    protected function featureRouteMatchesItem(string $routeName, array $routeParams, array $item): bool
    {
        $itemRoute = $item['route'] ?? null;

        if ($itemRoute === $routeName && Route::has($itemRoute)) {
            $itemParams = $item['route_params'] ?? [];

            foreach ($itemParams as $key => $expected) {
                if (($routeParams[$key] ?? null) != $expected) {
                    return false;
                }
            }

            return true;
        }

        return $this->routeMatchesItem($routeName, $item, request());
    }

    /**
     * @return array{module: string, primary: string, tab: string}|null
     */
    public function resolveFromRoute(?string $routeName, ?Request $request = null): ?array
    {
        $routeName ??= Route::currentRouteName();
        $request ??= request();

        if (! $routeName) {
            return null;
        }

        foreach ($this->moduleDefinitions() as $moduleKey => $module) {
            $catalog = $this->loadCatalog($module);

            if ($catalog === null) {
                continue;
            }

            foreach ($catalog['hub'] ?? [] as $hubItem) {
                if ($this->routeMatchesItem($routeName, $hubItem, $request)) {
                    $primaryKey = $this->hubItemKey($hubItem);
                    $tab = $this->resolveTabForRoute($catalog, $primaryKey, $routeName, $request);

                    return [
                        'module' => $moduleKey,
                        'primary' => $primaryKey,
                        'tab' => $tab['key'] ?? $this->firstSecondaryKey($catalog, $primaryKey),
                    ];
                }
            }

            foreach ($catalog['sections'] ?? [] as $sectionKey => $section) {
                foreach ($section['groups'] ?? [] as $group) {
                    foreach ($group['items'] ?? [] as $item) {
                        if ($this->shouldSkipDeskRedirectItem($item)) {
                            continue;
                        }

                        if ($this->routeMatchesItem($routeName, $item, $request)) {
                            return [
                                'module' => $moduleKey,
                                'primary' => $sectionKey,
                                'tab' => $this->itemKey($item),
                            ];
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $module
     * @return array<string, mixed>|null
     */
    protected function presentSectionedDesk(string $moduleKey, array $module, ?string $primaryKey, ?string $tabKey, Request $request): ?array
    {
        $catalog = $this->loadCatalog($module);

        if ($catalog === null) {
            return null;
        }

        $primaryWorkspaces = $this->presentPrimaryWorkspaces($moduleKey, $module, $catalog);

        if ($primaryWorkspaces === []) {
            return null;
        }

        $activePrimary = $this->resolveActivePrimary($primaryWorkspaces, $primaryKey);
        $secondaryWorkspaces = $this->presentSecondaryWorkspaces($catalog, $activePrimary['key'] ?? null, $module);
        $activeSecondary = $this->resolveActiveSecondary($secondaryWorkspaces, $tabKey);
        $contentUrl = $this->resolveContentUrl($activeSecondary)
            ?? $this->resolveSectionHubContentUrl($catalog, $activePrimary)
            ?? $this->resolvePrimaryContentUrl($catalog, $activePrimary, $module);

        return $this->presentDeskPayload(
            $moduleKey,
            $module,
            $primaryWorkspaces,
            $activePrimary,
            $secondaryWorkspaces,
            $activeSecondary,
            $contentUrl,
        );
    }

    /**
     * @param  array<string, mixed>  $module
     * @return array<string, mixed>|null
     */
    protected function presentGroupedDesk(string $moduleKey, array $module, ?string $primaryKey, ?string $tabKey, Request $request): ?array
    {
        $catalog = $this->loadCatalog($module);

        if ($catalog === null) {
            return null;
        }

        $sections = [];

        foreach ($catalog['groups'] ?? [] as $group) {
            $key = $this->groupKey($group);
            $sections[$key] = [
                'title' => $group['label'] ?? $key,
                'description' => $group['label'] ?? '',
                'icon' => 'folder',
                'groups' => [
                    [
                        'label' => $group['label'] ?? '',
                        'items' => $group['items'] ?? [],
                    ],
                ],
            ];
        }

        $synthetic = ['hub' => [], 'sections' => $sections];

        foreach ($sections as $key => $section) {
            $synthetic['hub'][] = [
                'label' => $section['title'],
                'description' => $section['description'],
                'route' => $module['section_route'] ?? $module['hub_route'],
                'route_params' => ['section' => $key],
                'icon' => $section['icon'] ?? 'folder',
                'key' => $key,
            ];
        }

        return $this->presentDeskFromSynthetic($moduleKey, $module, $synthetic, $primaryKey, $tabKey);
    }

    /**
     * @param  array<string, mixed>  $module
     * @param  array<string, mixed>  $catalog
     * @return array<string, mixed>|null
     */
    protected function presentDeskFromSynthetic(string $moduleKey, array $module, array $catalog, ?string $primaryKey, ?string $tabKey): ?array
    {
        $primaryWorkspaces = $this->presentPrimaryWorkspaces($moduleKey, $module, $catalog);

        if ($primaryWorkspaces === []) {
            return null;
        }

        $activePrimary = $this->resolveActivePrimary($primaryWorkspaces, $primaryKey);
        $secondaryWorkspaces = $this->presentSecondaryWorkspaces($catalog, $activePrimary['key'] ?? null, $module);
        $activeSecondary = $this->resolveActiveSecondary($secondaryWorkspaces, $tabKey);

        return $this->presentDeskPayload(
            $moduleKey,
            $module,
            $primaryWorkspaces,
            $activePrimary,
            $secondaryWorkspaces,
            $activeSecondary,
            $this->resolveContentUrl($activeSecondary),
        );
    }

    /**
     * @param  list<array<string, mixed>>  $primaryWorkspaces
     * @param  list<array<string, mixed>>  $secondaryWorkspaces
     * @return array<string, mixed>
     */
    protected function presentDeskPayload(
        string $moduleKey,
        array $module,
        array $primaryWorkspaces,
        ?array $activePrimary,
        array $secondaryWorkspaces,
        ?array $activeSecondary,
        ?string $contentUrl,
    ): array {
        return [
            'module' => $moduleKey,
            'title' => $module['title'] ?? $moduleKey,
            'description' => $module['description'] ?? '',
            'icon' => $module['icon'] ?? 'home',
            'primary_workspaces' => $primaryWorkspaces,
            'active_primary' => $activePrimary,
            'secondary_workspaces' => $secondaryWorkspaces,
            'active_secondary' => $activeSecondary,
            'content_url' => $contentUrl,
            'hub_route' => $module['hub_route'] ?? null,
            'section_route' => $module['section_route'] ?? null,
        ];
    }

    /**
     * @param  array<string, mixed>  $module
     * @return array<string, mixed>|null
     */
    protected function loadCatalog(array $module): ?array
    {
        $configKey = $module['config'] ?? null;

        if (! $configKey) {
            return null;
        }

        $catalog = config($configKey);

        return is_array($catalog) ? $catalog : null;
    }

    /**
     * @param  array<string, mixed>  $module
     * @param  array<string, mixed>  $catalog
     * @return list<array<string, mixed>>
     */
    protected function presentPrimaryWorkspaces(string $moduleKey, array $module, array $catalog): array
    {
        $workspaces = [];

        foreach ($catalog['hub'] ?? [] as $item) {
            if (! $this->itemIsAccessible($item)) {
                continue;
            }

            $key = $this->hubItemKey($item);
            $deskHref = $this->resolvePrimaryHref($module, $item);
            $hasSecondaryTabs = $this->sectionHasSecondaryTabs($catalog, $key);
            $contentHref = $hasSecondaryTabs
                ? null
                : $this->resolvePrimaryContentUrl($catalog, ['key' => $key], $module);
            $href = $contentHref ?? $deskHref;
            $turboFrame = ($hasSecondaryTabs || $contentHref === null) ? 'erp-main' : 'module-workspace-content';

            $workspaces[] = [
                'key' => $key,
                'label' => $item['label'] ?? '',
                'description' => $item['description'] ?? '',
                'icon' => $item['icon'] ?? 'home',
                'href' => $href,
                'turbo_frame' => $turboFrame,
                'badge' => $item['badge'] ?? null,
                'active' => false,
            ];
        }

        return $workspaces;
    }

    /**
     * @param  array<string, mixed>  $catalog
     * @return list<array<string, mixed>>
     */
    /**
     * @param  array<string, mixed>|null  $module
     */
    protected function presentSecondaryWorkspaces(array $catalog, ?string $primaryKey, ?array $module = null): array
    {
        if ($primaryKey === null) {
            return [];
        }

        $section = $catalog['sections'][$primaryKey] ?? null;

        if ($section === null) {
            return [];
        }

        if (! empty($section['permission']) && ! $this->userCan($section['permission'])) {
            return [];
        }

        $tabs = [];

        foreach ($section['groups'] ?? [] as $group) {
            foreach ($group['items'] ?? [] as $item) {
                if (! $this->itemIsAccessible($item, includeComingSoon: false)) {
                    continue;
                }

                $key = $this->itemKey($item);
                $href = $this->resolveSecondaryHref($item, $primaryKey, $module);

                $tabs[] = [
                    'key' => $key,
                    'label' => $item['label'] ?? '',
                    'description' => $item['description'] ?? '',
                    'href' => $href,
                    'turbo_frame' => 'module-workspace-content',
                    'badge' => $item['count'] ?? null,
                    'coming_soon' => (bool) ($item['coming_soon'] ?? false),
                    'active' => false,
                ];
            }
        }

        return $tabs;
    }

    /**
     * @param  list<array<string, mixed>>  $primaryWorkspaces
     * @return array<string, mixed>|null
     */
    protected function resolveActivePrimary(array $primaryWorkspaces, ?string $primaryKey): ?array
    {
        if ($primaryWorkspaces === []) {
            return null;
        }

        if ($primaryKey !== null) {
            foreach ($primaryWorkspaces as $workspace) {
                if (($workspace['key'] ?? '') === $primaryKey) {
                    return array_merge($workspace, ['active' => true]);
                }
            }
        }

        return array_merge($primaryWorkspaces[0], ['active' => true]);
    }

    /**
     * @param  list<array<string, mixed>>  $secondaryWorkspaces
     * @return array<string, mixed>|null
     */
    protected function resolveActiveSecondary(array $secondaryWorkspaces, ?string $tabKey): ?array
    {
        if ($secondaryWorkspaces === []) {
            return null;
        }

        if ($tabKey !== null && $tabKey !== '') {
            $normalized = Str::slug($tabKey);

            foreach ($secondaryWorkspaces as $workspace) {
                $key = (string) ($workspace['key'] ?? '');

                if (
                    $key === $tabKey
                    || $key === $normalized
                    || Str::slug((string) ($workspace['label'] ?? '')) === $normalized
                ) {
                    return array_merge($workspace, ['active' => true]);
                }
            }
        }

        return array_merge($secondaryWorkspaces[0], ['active' => true]);
    }

    /**
     * @param  array<string, mixed>  $catalog
     * @param  array<string, mixed>|null  $activePrimary
     * @param  array<string, mixed>  $module
     */
    protected function resolvePrimaryContentUrl(array $catalog, ?array $activePrimary, array $module): ?string
    {
        if ($activePrimary === null) {
            return null;
        }

        $sectionRoute = $module['section_route'] ?? null;

        foreach ($catalog['hub'] ?? [] as $item) {
            if ($this->hubItemKey($item) !== ($activePrimary['key'] ?? null)) {
                continue;
            }

            $route = $item['route'] ?? null;

            if (! $route || ! Route::has($route) || $route === $sectionRoute) {
                return null;
            }

            $url = route($route, $item['route_params'] ?? []);

            return $this->navigation->appendPreservedQuery($this->appendQuery($url, ['embedded' => '1']));
        }

        return null;
    }

    /**
     * @param  array<string, mixed>|null  $activeSecondary
     */
    protected function resolveContentUrl(?array $activeSecondary): ?string
    {
        if ($activeSecondary === null || ! empty($activeSecondary['coming_soon'])) {
            return null;
        }

        return $activeSecondary['href'] ?? null;
    }

    /**
     * @param  array<string, mixed>  $catalog
     * @param  array<string, mixed>|null  $activePrimary
     */
    protected function resolveSectionHubContentUrl(array $catalog, ?array $activePrimary): ?string
    {
        if ($activePrimary === null) {
            return null;
        }

        $section = $catalog['sections'][$activePrimary['key'] ?? ''] ?? null;

        if ($section === null || ($section['presentation'] ?? '') !== 'hub') {
            return null;
        }

        return $this->embeddedFeatureUrl(
            $section['hub_route'] ?? null,
            $section['hub_route_params'] ?? [],
        );
    }

    /**
     * @param  array<string, mixed>  $params
     */
    protected function appendQuery(string $url, array $params): string
    {
        $filtered = array_filter($params, fn ($value) => $value !== null && $value !== '');

        if ($filtered === []) {
            return $url;
        }

        return $url.(str_contains($url, '?') ? '&' : '?').http_build_query($filtered);
    }

    /**
     * @param  array<string, mixed>  $module
     * @param  array<string, mixed>  $item
     */
    protected function resolvePrimaryHref(array $module, array $item): ?string
    {
        $route = $item['route'] ?? null;

        if (! $route || ! Route::has($route)) {
            return null;
        }

        $catalog = $this->loadCatalog($module) ?? [];
        $hubKey = $this->hubItemKey($item);
        $sectionRoute = $module['section_route'] ?? null;
        $hubRoute = $module['hub_route'] ?? null;
        $primaryHasSection = isset($catalog['sections'][$hubKey]);

        if ($sectionRoute && $route !== $sectionRoute && ! $primaryHasSection && $hubRoute && Route::has($hubRoute)) {
            return $this->navigation->appendPreservedQuery(route($hubRoute));
        }

        $params = $item['route_params'] ?? [];
        $url = route($route, $params);
        $defaultTab = $this->firstSecondaryKey($catalog, $hubKey);

        if ($defaultTab !== null && $sectionRoute === $route) {
            $url = $this->appendQuery($url, ['tab' => $defaultTab]);
        }

        return $this->navigation->appendPreservedQuery($url);
    }

    /**
     * @param  array<string, mixed>  $routeParams
     */
    public function embeddedFeatureUrl(?string $route, array $routeParams = []): ?string
    {
        if (! $route || ! Route::has($route)) {
            return null;
        }

        $url = route($route, $routeParams);

        return $this->navigation->appendPreservedQuery($this->appendQuery($url, ['embedded' => '1']));
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function resolveSecondaryHref(array $item, string $primaryKey, ?array $module = null): ?string
    {
        return $this->embeddedFeatureUrl(
            $item['route'] ?? null,
            $item['route_params'] ?? [],
        );
    }

    /**
     * @param  array<string, mixed>  $module
     */
    public function firstSecondaryKeyForSection(array $module, string $primaryKey): ?string
    {
        $catalog = $this->loadCatalog($module);

        if ($catalog === null) {
            return null;
        }

        return $this->firstSecondaryKey($catalog, $primaryKey);
    }

    /**
     * @param  array<string, mixed>  $catalog
     */
    protected function sectionHasSecondaryTabs(array $catalog, string $primaryKey): bool
    {
        $section = $catalog['sections'][$primaryKey] ?? null;

        if ($section === null) {
            return false;
        }

        if (($section['presentation'] ?? '') === 'hub') {
            return false;
        }

        foreach ($section['groups'] ?? [] as $group) {
            foreach ($group['items'] ?? [] as $item) {
                if ($this->itemIsAccessible($item, includeComingSoon: false)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $catalog
     */
    protected function firstSecondaryKey(array $catalog, ?string $primaryKey): ?string
    {
        if ($primaryKey === null) {
            return null;
        }

        $section = $catalog['sections'][$primaryKey] ?? null;

        if ($section === null) {
            return null;
        }

        foreach ($section['groups'] ?? [] as $group) {
            foreach ($group['items'] ?? [] as $item) {
                if ($this->itemIsAccessible($item)) {
                    return $this->itemKey($item);
                }
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $catalog
     * @return array{key: string}|null
     */
    protected function resolveTabForRoute(array $catalog, string $primaryKey, string $routeName, Request $request): ?array
    {
        $section = $catalog['sections'][$primaryKey] ?? null;

        if ($section === null) {
            return null;
        }

        foreach ($section['groups'] ?? [] as $group) {
            foreach ($group['items'] ?? [] as $item) {
                if ($this->routeMatchesItem($routeName, $item, $request)) {
                    return ['key' => $this->itemKey($item)];
                }
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function routeMatchesItem(string $routeName, array $item, Request $request): bool
    {
        $itemRoute = $item['route'] ?? null;

        if ($itemRoute && Route::has($itemRoute) && $routeName === $itemRoute) {
            return true;
        }

        foreach ($item['active_routes'] ?? [] as $pattern) {
            if ($pattern === $routeName) {
                return true;
            }

            if (str_contains($pattern, ':')) {
                [$patternRoute, $paramValue] = explode(':', $pattern, 2);

                if ($routeName === $patternRoute && $request->route('section') === $paramValue) {
                    return true;
                }

                continue;
            }

            if (str_ends_with($pattern, '.*') && str_starts_with($routeName, substr($pattern, 0, -2))) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function itemIsAccessible(array $item, bool $includeComingSoon = true): bool
    {
        if (! empty($item['coming_soon'])) {
            return $includeComingSoon;
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
     * @param  array<string, mixed>  $item
     */
    protected function itemKey(array $item): string
    {
        if (! empty($item['key'])) {
            return (string) $item['key'];
        }

        return Str::slug($item['label'] ?? 'item');
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function hubItemKey(array $item): string
    {
        if (! empty($item['key'])) {
            return (string) $item['key'];
        }

        $section = $item['route_params']['section'] ?? null;

        if ($section) {
            return (string) $section;
        }

        if (! empty($item['route']) && Route::has($item['route'])) {
            return Str::slug($item['label'] ?? 'hub');
        }

        return Str::slug($item['label'] ?? 'hub');
    }

    /**
     * @param  array<string, mixed>  $group
     */
    protected function groupKey(array $group): string
    {
        return Str::slug($group['label'] ?? 'group');
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
}
