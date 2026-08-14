<?php

namespace App\Support\Navigation;

use App\Support\Platform\ModalFormRoutes;
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
                'description' => __('Customer acquisition · quotations · sales · customer care'),
                'icon' => 'shopping-cart',
                'hub_route' => 'admin.workspaces.commercial',
                'section_route' => 'admin.workspaces.commercial.section',
                'type' => 'sectioned',
            ],
            'supply-chain' => [
                'config' => 'supply_chain_workspaces',
                'title' => __('Supply Chain'),
                'description' => __('Catalogue · store · procurement · inventory'),
                'icon' => 'cube',
                'hub_route' => 'admin.workspaces.supply-chain',
                'section_route' => 'admin.workspaces.supply-chain.section',
                'type' => 'sectioned',
            ],
            'accounting' => [
                'config' => 'accounting_workspaces',
                'title' => __('Accounting'),
                'description' => __('Ledger · receivables · payables · tax · setup'),
                'icon' => 'currency-dollar',
                'hub_route' => 'admin.workspaces.accounting',
                'section_route' => 'admin.workspaces.accounting.section',
                'type' => 'sectioned',
            ],
            'administration' => [
                'config' => 'administration_workspaces',
                'title' => __('Administration'),
                'description' => __('Access · organization · settings · audit'),
                'icon' => 'shield-check',
                'hub_route' => 'admin.workspaces.administration',
                'section_route' => 'admin.workspaces.administration.section',
                'type' => 'sectioned',
            ],
            'designer' => [
                'config' => 'designer_workspaces',
                'title' => __('Designer Desk'),
                'description' => __('Artwork queue · uploads · register'),
                'icon' => 'color-swatch',
                'hub_route' => 'admin.workspaces.designer',
                'section_route' => 'admin.workspaces.designer.section',
                'type' => 'sectioned',
            ],
            'production' => [
                'config' => 'production_workspaces',
                'title' => __('Production'),
                'description' => __('Job cards · shop floor · quality · dispatch'),
                'icon' => 'cog',
                'hub_route' => 'admin.workspaces.production',
                'section_route' => 'admin.workspaces.production.section',
                'type' => 'sectioned',
            ],
            'assets' => [
                'config' => 'assets_workspaces',
                'title' => __('Assets'),
                'description' => __('Fixed assets · maintenance · depreciation'),
                'icon' => 'chip',
                'hub_route' => 'admin.workspaces.assets',
                'section_route' => 'admin.workspaces.assets.section',
                'type' => 'grouped',
            ],
            'hr' => [
                'config' => 'hr_workspaces',
                'title' => __('HR'),
                'description' => __('Employees · attendance · leave · payroll'),
                'icon' => 'identification',
                'hub_route' => 'admin.workspaces.hr',
                'section_route' => 'admin.workspaces.hr.section',
                'type' => 'sectioned',
            ],
            'communications' => [
                'config' => 'communications_workspaces',
                'title' => __('Communications'),
                'description' => __('SMS · email · campaigns · templates'),
                'icon' => 'inbox',
                'hub_route' => 'admin.workspaces.communications',
                'section_route' => 'admin.workspaces.communications.section',
                'type' => 'sectioned',
            ],
            'reports' => [
                'config' => 'reports_workspaces',
                'title' => __('Reports & Intelligence'),
                'description' => __('Dashboards · module reports · KPI center'),
                'icon' => 'chart-pie',
                'hub_route' => 'admin.workspaces.reports',
                'section_route' => 'admin.workspaces.reports.section',
                'type' => 'sectioned',
            ],
            'dispatch' => [
                'config' => 'dispatch_workspaces',
                'title' => __('Dispatch'),
                'description' => __('Delivery notes · lifecycle · outbound truth'),
                'icon' => 'truck',
                'hub_route' => 'admin.workspaces.dispatch',
                'section_route' => 'admin.workspaces.dispatch.section',
                'type' => 'sectioned',
            ],
            'printing-intelligence' => [
                'config' => 'printing_intelligence_workspaces',
                'title' => __('Printing Intelligence'),
                'description' => __('Materials · machines · ink · cost bridge'),
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

        $module = $this->moduleDefinitions()[$moduleKey];
        $catalog = $this->loadCatalog($module);
        $desk = $this->preferSectionedDesk($moduleKey, $desk, $module, $catalog);

        $primary = $desk['active_primary'] ?? null;
        $secondary = $desk['active_secondary'] ?? null;

        if ($primary === null) {
            return null;
        }

        // Full-page operator desks (Sales Desk, etc.) — same destination as dashboard shortcuts.
        if ($secondary !== null && ! empty($secondary['open_full']) && ! empty($secondary['href'])) {
            return ['url' => (string) $secondary['href']];
        }

        $sectionRoute = $module['section_route'] ?? null;
        $primaryHasSection = $this->primaryKeyHasSection($catalog, $module, $primary['key'] ?? null);

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
     * Default multi-tab shell landing — skips open_full operator desks.
     * Used when ?desk=1 requests the Commercial shell escape hatch.
     *
     * @return array{url: string}|null
     */
    public function defaultShellDesk(string $moduleKey): ?array
    {
        $desk = $this->presentDesk($moduleKey);

        if ($desk === null) {
            return null;
        }

        $module = $this->moduleDefinitions()[$moduleKey];
        $catalog = $this->loadCatalog($module);
        $desk = $this->preferSectionedDesk($moduleKey, $desk, $module, $catalog);

        $primary = $desk['active_primary'] ?? null;

        if ($primary === null) {
            return null;
        }

        $secondary = null;

        foreach ($desk['secondary_workspaces'] ?? [] as $tab) {
            if (! empty($tab['open_full']) || ! empty($tab['coming_soon']) || empty($tab['href'])) {
                continue;
            }

            $secondary = $tab;
            break;
        }

        $sectionRoute = $module['section_route'] ?? null;
        $primaryHasSection = $this->primaryKeyHasSection($catalog, $module, $primary['key'] ?? null);

        if ($sectionRoute && Route::has($sectionRoute) && $primaryHasSection) {
            $url = route($sectionRoute, ['section' => $primary['key']]);

            if ($secondary !== null) {
                $url = $this->appendQuery($url, ['tab' => $secondary['key']]);
            }

            $url = $this->appendQuery($url, ['desk' => 1]);

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

                    $url = $this->buildDeskUrl(
                        $moduleKey,
                        $module,
                        $sectionKey,
                        $this->itemKey($item),
                    );

                    $modeKey = $this->resolveModeKeyForRoute($item, $routeName);

                    if ($url !== null && $modeKey !== null) {
                        return $this->appendQuery($url, ['mode' => $modeKey]);
                    }

                    return $url;
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

                    $url = $this->buildDeskUrl(
                        $moduleKey,
                        $module,
                        $sectionKey,
                        $this->itemKey($item),
                    );

                    $modeKey = $this->resolveModeKeyForRoute($item, $routeName);

                    if ($url !== null && $modeKey !== null) {
                        return $this->appendQuery($url, ['mode' => $modeKey]);
                    }

                    return $url;
                }
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function resolveModeKeyForRoute(array $item, string $routeName): ?string
    {
        $request = request();
        $matched = null;

        foreach ($item['modes'] ?? [] as $mode) {
            if (! is_array($mode)) {
                continue;
            }

            if (! $this->routeMatchesItem($routeName, $mode, $request)) {
                continue;
            }

            $params = $mode['route_params'] ?? [];
            $paramsMatch = true;

            foreach ($params as $key => $expected) {
                if ((string) $request->query($key, $request->route($key)) !== (string) $expected) {
                    $paramsMatch = false;
                    break;
                }
            }

            // Prefer the most specific mode (with matching route_params).
            if ($params !== [] && $paramsMatch) {
                return (string) ($mode['key'] ?? Str::slug((string) ($mode['label'] ?? 'mode')));
            }

            if ($params === [] && $paramsMatch) {
                $matched ??= (string) ($mode['key'] ?? Str::slug((string) ($mode['label'] ?? 'mode')));
            }
        }

        return $matched;
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
        return ! empty($item['skip_desk_redirect']) || ! empty($item['open_full']);
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
        $secondaryWorkspaces = $this->presentSecondaryWorkspaces($catalog, $activePrimary['key'] ?? null, $module, $moduleKey);
        $activeSecondary = $this->resolveActiveSecondary($secondaryWorkspaces, $tabKey, $request);
        $contextWorkspaces = $activeSecondary['modes'] ?? [];
        $activeContext = $this->resolveActiveContext($contextWorkspaces, $this->resolveContextModeKey($request));
        $contentUrl = ($activeContext['content_href'] ?? null)
            ?? $this->resolveHubSectionFeatureContentUrl($catalog, $activePrimary, $tabKey, $module, $moduleKey)
            ?? $this->resolveContentUrl($activeSecondary)
            ?? $this->resolveSectionHubContentUrl($catalog, $activePrimary)
            ?? $this->resolvePrimaryContentUrl($catalog, $activePrimary, $module);

        $activeSecondary = $this->resolveActiveSecondaryByContentHref($secondaryWorkspaces, $contentUrl)
            ?? $activeSecondary;
        $contextWorkspaces = $activeSecondary['modes'] ?? [];
        $activeContext = $this->resolveActiveContext($contextWorkspaces, $this->resolveContextModeKey($request))
            ?? $activeContext;

        return $this->presentDeskPayload(
            $moduleKey,
            $module,
            $primaryWorkspaces,
            $activePrimary,
            $secondaryWorkspaces,
            $activeSecondary,
            $contentUrl,
            $contextWorkspaces,
            $activeContext,
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
        $secondaryWorkspaces = $this->presentSecondaryWorkspaces($catalog, $activePrimary['key'] ?? null, $module, $moduleKey);
        $activeSecondary = $this->resolveActiveSecondary($secondaryWorkspaces, $tabKey, $request);
        $contextWorkspaces = $activeSecondary['modes'] ?? [];
        $activeContext = $this->resolveActiveContext($contextWorkspaces, $this->resolveContextModeKey($request));
        $contentUrl = ($activeContext['content_href'] ?? null)
            ?? $this->resolveContentUrl($activeSecondary);

        $activeSecondary = $this->resolveActiveSecondaryByContentHref($secondaryWorkspaces, $contentUrl)
            ?? $activeSecondary;
        $contextWorkspaces = $activeSecondary['modes'] ?? [];
        $activeContext = $this->resolveActiveContext($contextWorkspaces, $this->resolveContextModeKey($request))
            ?? $activeContext;

        return $this->presentDeskPayload(
            $moduleKey,
            $module,
            $primaryWorkspaces,
            $activePrimary,
            $secondaryWorkspaces,
            $activeSecondary,
            $contentUrl,
            $contextWorkspaces,
            $activeContext,
        );
    }

    /**
     * @param  list<array<string, mixed>>  $primaryWorkspaces
     * @param  list<array<string, mixed>>  $secondaryWorkspaces
     * @param  list<array<string, mixed>>  $contextWorkspaces
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
        array $contextWorkspaces = [],
        ?array $activeContext = null,
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
            'secondary_toolbar_actions' => $this->presentToolbarActions($activeSecondary['toolbar_actions'] ?? []),
            'context_workspaces' => $contextWorkspaces,
            'active_context' => $activeContext,
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

            if (
                $moduleKey === 'production'
                && \App\Support\Production\ProductionDeskPersona::resolve(auth()->user())->operationsHubOnly()
                && ($this->hubItemKey($item) !== 'operations')
            ) {
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
     * @return list<array<string, mixed>>
     */
    protected function presentSecondaryWorkspaces(
        array $catalog,
        ?string $primaryKey,
        ?array $module = null,
        ?string $moduleKey = null,
    ): array {
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
                $openFull = ! empty($item['open_full']);
                $featureHref = $this->resolveFeatureHref($item);
                $embeddedHref = $openFull ? null : $this->resolveSecondaryHref($item, $primaryKey, $module);
                $deskHref = ($module !== null && $moduleKey !== null)
                    ? $this->buildDeskUrl($moduleKey, $module, $primaryKey, $key)
                    : null;

                $modes = ($module !== null && $moduleKey !== null)
                    ? $this->presentItemModes($item, $moduleKey, $module, $primaryKey, $key, $deskHref)
                    : [];

                // Operator desks (e.g. Sales Desk) open full-page like dashboard shortcuts.
                if ($openFull && $featureHref !== null) {
                    $tabs[] = [
                        'key' => $key,
                        'label' => $item['label'] ?? '',
                        'description' => $item['description'] ?? '',
                        'href' => $featureHref,
                        'content_href' => null,
                        'turbo_frame' => 'erp-main',
                        'open_full' => true,
                        'badge' => $item['count'] ?? null,
                        'coming_soon' => (bool) ($item['coming_soon'] ?? false),
                        'toolbar_actions' => $item['toolbar_actions'] ?? [],
                        'modes' => $modes,
                        'active' => false,
                    ];

                    continue;
                }

                $tabs[] = [
                    'key' => $key,
                    'label' => $item['label'] ?? '',
                    'description' => $item['description'] ?? '',
                    // Tab clicks restore the full module desk (shell + address bar).
                    'href' => $deskHref ?? $embeddedHref,
                    // Frame src stays on the embedded feature URL.
                    'content_href' => $embeddedHref,
                    'turbo_frame' => $deskHref !== null ? 'erp-main' : 'module-workspace-content',
                    'open_full' => false,
                    'badge' => $item['count'] ?? null,
                    'coming_soon' => (bool) ($item['coming_soon'] ?? false),
                    'toolbar_actions' => $item['toolbar_actions'] ?? [],
                    'modes' => $modes,
                    'active' => false,
                ];
            }
        }

        return $tabs;
    }

    /**
     * Fixed desk-mode tabs (outside the content frame) for items that declare modes.
     *
     * @param  array<string, mixed>  $item
     * @param  array<string, mixed>  $module
     * @return list<array<string, mixed>>
     */
    protected function presentItemModes(
        array $item,
        string $moduleKey,
        array $module,
        string $primaryKey,
        string $tabKey,
        ?string $deskHref,
    ): array {
        if ($moduleKey === 'production' && ($item['key'] ?? null) === 'production-floor') {
            $personaModes = \App\Support\Production\ProductionDeskPersona::resolve(auth()->user())
                ->operationsFloorModes();

            if ($personaModes !== []) {
                $item = [...$item, 'modes' => $personaModes];
            }
        }

        $modes = [];

        foreach ($item['modes'] ?? [] as $mode) {
            if (! is_array($mode) || ! $this->itemIsAccessible($mode, includeComingSoon: false)) {
                continue;
            }

            $modeKey = (string) ($mode['key'] ?? Str::slug((string) ($mode['label'] ?? 'mode')));
            $contentHref = $this->embeddedFeatureUrl(
                $mode['route'] ?? null,
                $mode['route_params'] ?? [],
            );

            if ($contentHref === null) {
                continue;
            }

            $href = $deskHref !== null
                ? $this->appendQuery($deskHref, ['mode' => $modeKey])
                : $contentHref;

            $modes[] = [
                'key' => $modeKey,
                'label' => $mode['label'] ?? $modeKey,
                'href' => $href,
                'content_href' => $contentHref,
                'turbo_frame' => 'erp-main',
                'active' => false,
            ];
        }

        return $modes;
    }

    /**
     * @param  list<array<string, mixed>>  $contextWorkspaces
     * @return array<string, mixed>|null
     */
    protected function resolveActiveContext(array $contextWorkspaces, mixed $modeKey): ?array
    {
        if ($contextWorkspaces === []) {
            return null;
        }

        $modeKey = is_string($modeKey) ? trim($modeKey) : '';

        if ($modeKey !== '') {
            foreach ($contextWorkspaces as $workspace) {
                if (($workspace['key'] ?? '') === $modeKey) {
                    return array_merge($workspace, ['active' => true]);
                }
            }
        }

        return array_merge($contextWorkspaces[0], ['active' => true]);
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
     * When hub starts with a feature-only primary (e.g. Accounting Dashboard),
     * land on the first section desk that has secondary tabs.
     *
     * @param  array<string, mixed>  $desk
     * @param  array<string, mixed>  $module
     * @param  array<string, mixed>|null  $catalog
     * @return array<string, mixed>
     */
    protected function preferSectionedDesk(string $moduleKey, array $desk, array $module, ?array $catalog): array
    {
        $primaryKey = $desk['active_primary']['key'] ?? null;

        if ($this->primaryKeyHasSection($catalog, $module, is_string($primaryKey) ? $primaryKey : null)) {
            return $desk;
        }

        foreach ($desk['primary_workspaces'] ?? [] as $workspace) {
            $key = $workspace['key'] ?? null;

            if (! is_string($key) || $key === '') {
                continue;
            }

            if (! $this->primaryKeyHasSection($catalog, $module, $key)) {
                continue;
            }

            $sectioned = $this->presentDesk($moduleKey, $key);

            return $sectioned ?? $desk;
        }

        return $desk;
    }

    /**
     * @param  array<string, mixed>|null  $catalog
     * @param  array<string, mixed>  $module
     */
    protected function primaryKeyHasSection(?array $catalog, array $module, ?string $primaryKey): bool
    {
        if ($catalog === null || $primaryKey === null || $primaryKey === '') {
            return false;
        }

        return isset($catalog['sections'][$primaryKey])
            || (($module['type'] ?? '') === 'grouped' && $this->groupedSectionExists($catalog, $primaryKey));
    }

    /**
     * @param  list<array<string, mixed>>  $secondaryWorkspaces
     * @return array<string, mixed>|null
     */
    protected function resolveActiveSecondary(array $secondaryWorkspaces, ?string $tabKey, ?Request $request = null): ?array
    {
        if ($secondaryWorkspaces === []) {
            return null;
        }

        $request ??= request();

        if ($tabKey !== null && $tabKey !== '') {
            $matched = $this->matchSecondaryWorkspace($secondaryWorkspaces, $tabKey);

            if ($matched !== null) {
                return $matched;
            }
        }

        $routeName = $request->route()?->getName();

        if (is_string($routeName) && $routeName !== '') {
            foreach ($secondaryWorkspaces as $workspace) {
                if ($this->routeMatchesSecondaryWorkspace($routeName, $workspace, $request)) {
                    return array_merge($workspace, ['active' => true]);
                }
            }
        }

        return array_merge($secondaryWorkspaces[0], ['active' => true]);
    }

    /**
     * @param  list<array<string, mixed>>  $secondaryWorkspaces
     * @return array<string, mixed>|null
     */
    protected function resolveActiveSecondaryByContentHref(array $secondaryWorkspaces, ?string $contentUrl): ?array
    {
        if ($contentUrl === null || $contentUrl === '') {
            return null;
        }

        $contentPath = $this->normalizeWorkspaceContentPath($contentUrl);

        if ($contentPath === null) {
            return null;
        }

        foreach ($secondaryWorkspaces as $workspace) {
            $href = $workspace['content_href'] ?? null;

            if (! is_string($href) || $href === '') {
                continue;
            }

            $workspacePath = $this->normalizeWorkspaceContentPath($href);

            if ($workspacePath === null) {
                continue;
            }

            if ($this->workspaceContentPathsMatch($contentPath, $workspacePath)) {
                return array_merge($workspace, ['active' => true]);
            }
        }

        return null;
    }

    protected function resolveContextModeKey(?Request $request = null): ?string
    {
        $request ??= request();

        $mode = $request->query('mode');

        if (is_string($mode) && $mode !== '') {
            return $mode;
        }

        $filter = $request->query('filter');

        if (is_string($filter) && $filter !== '' && $filter !== 'all') {
            return $filter;
        }

        return null;
    }

    /**
     * @param  list<array<string, mixed>>  $secondaryWorkspaces
     * @return array<string, mixed>|null
     */
    protected function matchSecondaryWorkspace(array $secondaryWorkspaces, string $tabKey): ?array
    {
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

        return null;
    }

    /**
     * @param  array<string, mixed>  $workspace
     */
    protected function routeMatchesSecondaryWorkspace(string $routeName, array $workspace, Request $request): bool
    {
        if ($this->routeMatchesItem($routeName, $workspace, $request)) {
            return true;
        }

        foreach ($workspace['modes'] ?? [] as $mode) {
            if (is_array($mode) && $this->routeMatchesItem($routeName, $mode, $request)) {
                return true;
            }
        }

        return false;
    }

    protected function normalizeWorkspaceContentPath(string $url): ?string
    {
        $path = parse_url($url, PHP_URL_PATH);

        return is_string($path) && $path !== '' ? $path : null;
    }

    protected function workspaceContentPathsMatch(string $contentPath, string $workspacePath): bool
    {
        if ($contentPath === $workspacePath) {
            return true;
        }

        return $workspacePath !== '/'
            && str_starts_with($contentPath, rtrim($workspacePath, '/').'/');
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

        // Never fall back to the desk href — frame src must stay on embedded feature URLs.
        return $activeSecondary['content_href'] ?? null;
    }

    /**
     * Hub sections keep features off the tab strip. A ?tab=feature-key still embeds that feature.
     *
     * @param  array<string, mixed>  $catalog
     * @param  array<string, mixed>|null  $activePrimary
     * @param  array<string, mixed>|null  $module
     */
    protected function resolveHubSectionFeatureContentUrl(
        array $catalog,
        ?array $activePrimary,
        ?string $tabKey,
        ?array $module = null,
        ?string $moduleKey = null,
    ): ?string {
        if ($activePrimary === null || $tabKey === null || $tabKey === '') {
            return null;
        }

        $section = $catalog['sections'][$activePrimary['key'] ?? ''] ?? null;

        if ($section === null || ($section['presentation'] ?? '') !== 'hub') {
            return null;
        }

        foreach ($section['groups'] ?? [] as $group) {
            foreach ($group['items'] ?? [] as $item) {
                if ($this->itemKey($item) !== $tabKey) {
                    continue;
                }

                if (! $this->itemIsAccessible($item, includeComingSoon: false)) {
                    return null;
                }

                return $this->resolveSecondaryHref($item, (string) $activePrimary['key'], $module);
            }
        }

        return null;
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
     * Full feature URL without ?embedded=1 (for open_full operator desks).
     *
     * @param  array<string, mixed>  $item
     */
    protected function resolveFeatureHref(array $item): ?string
    {
        $route = $item['route'] ?? null;

        if (! is_string($route) || ! Route::has($route)) {
            return null;
        }

        $url = route($route, $item['route_params'] ?? []);

        return $this->navigation->appendPreservedQuery($url) ?? $url;
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

    /**
     * @param  list<array<string, mixed>>  $actions
     * @return list<array<string, mixed>>
     */
    protected function presentToolbarActions(array $actions): array
    {
        $presented = [];

        foreach ($actions as $action) {
            if (! empty($action['coming_soon'])) {
                continue;
            }

            $route = $action['route'] ?? null;

            if (! $route || ! Route::has($route)) {
                continue;
            }

            if (! empty($action['permission']) && ! $this->userCan($action['permission'])) {
                continue;
            }

            $params = $action['route_params'] ?? [];

            $presented[] = [
                'label' => $action['label'] ?? '',
                'href' => $this->navigation->appendPreservedQuery(route($route, $params)),
                'modal' => (bool) ($action['modal'] ?? ModalFormRoutes::supports($route)),
                'variant' => $action['variant'] ?? 'primary',
            ];
        }

        return $presented;
    }
}
