<?php

namespace App\Support\Navigation;

use Illuminate\Support\Facades\Route;

class WorkspaceNavigationRegistry
{
    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $byRoute = [];

    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $byPattern = [];

    protected bool $built = false;

    public function resolve(string $routeName): ?array
    {
        $this->ensureBuilt();

        if (isset($this->byRoute[$routeName])) {
            return $this->byRoute[$routeName];
        }

        $inferred = $this->inferFromRoute($routeName);

        if ($inferred) {
            return $inferred;
        }

        foreach ($this->byPattern as $pattern => $entry) {
            if ($this->routeMatchesPattern($routeName, $pattern)) {
                return $entry;
            }
        }

        return null;
    }

    protected function ensureBuilt(): void
    {
        if ($this->built) {
            return;
        }

        foreach (config('workspaces', []) as $workspaceKey => $definition) {
            if (in_array($workspaceKey, ['accounting', 'supply-chain', 'commercial', 'administration', 'designer'], true) || ! empty($definition['managed_by'])) {
                continue;
            }

            $this->registerWorkspace($workspaceKey, $definition);
        }

        $this->registerAccounting();
        $this->registerSupplyChain();
        $this->registerCommercial();
        $this->registerAdministration();
        $this->registerDesigner();
        $this->registerHr();
        $this->registerPrintingIntelligence();

        $this->built = true;
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    protected function registerWorkspace(string $workspaceKey, array $definition): void
    {
        $hubRoute = "admin.workspaces.{$workspaceKey}";
        $hubTitle = $definition['title'] ?? $workspaceKey;

        $this->registerEntry($hubRoute, [
            'workspace_key' => $workspaceKey,
            'parent_workspace_key' => null,
            'workspace_title' => $hubTitle,
            'parent_workspace_title' => null,
            'parent_route' => null,
            'parent_route_params' => [],
            'ancestors' => [],
        ]);

        foreach ($definition['groups'] ?? [] as $group) {
            $groupLabel = $group['label'] ?? '';

            foreach ($group['items'] ?? [] as $item) {
                if (! empty($item['coming_soon'])) {
                    continue;
                }

                $this->registerFeatureItem($workspaceKey, $hubRoute, $hubTitle, $groupLabel, $item, null);
            }
        }
    }

    protected function registerAccounting(): void
    {
        $workspaceKey = 'accounting';
        $hubRoute = 'admin.workspaces.accounting';
        $hubTitle = __('Accounting');

        $this->registerEntry($hubRoute, [
            'workspace_key' => $workspaceKey,
            'parent_workspace_key' => null,
            'workspace_title' => $hubTitle,
            'parent_workspace_title' => null,
            'parent_route' => null,
            'parent_route_params' => [],
            'ancestors' => [],
        ]);

        $presenter = app(AccountingWorkspacePresenter::class);

        foreach ($presenter->hubDefinitions() as $hubItem) {
            $route = $hubItem['route'] ?? null;

            if (! $route || ! Route::has($route)) {
                continue;
            }

            if ($route === 'admin.accounting.dashboard') {
                $this->registerEntry($route, [
                    'workspace_key' => $workspaceKey,
                    'parent_workspace_key' => $workspaceKey,
                    'workspace_title' => $hubItem['label'] ?? __('Dashboard'),
                    'parent_workspace_title' => $hubTitle,
                    'parent_route' => $hubRoute,
                    'parent_route_params' => [],
                    'ancestors' => [
                        $this->ancestor($hubTitle, $hubRoute),
                    ],
                ]);

                continue;
            }

            if ($route === 'admin.workspaces.accounting.section') {
                continue;
            }
        }

        $this->registerEntry('admin.workspaces.accounting.section', [
            'workspace_key' => $workspaceKey,
            'parent_workspace_key' => $workspaceKey,
            'workspace_title' => null,
            'parent_workspace_title' => $hubTitle,
            'parent_route' => $hubRoute,
            'parent_route_params' => [],
            'ancestors' => [
                $this->ancestor($hubTitle, $hubRoute),
            ],
            'dynamic_section' => true,
        ]);

        foreach ($presenter->sectionDefinitions() as $sectionKey => $section) {
            $sectionRoute = 'admin.workspaces.accounting.section';
            $sectionTitle = $section['title'] ?? $sectionKey;

            $this->registerEntry("admin.workspaces.accounting.section:{$sectionKey}", [
                'workspace_key' => "{$workspaceKey}:{$sectionKey}",
                'parent_workspace_key' => $workspaceKey,
                'workspace_title' => $sectionTitle,
                'parent_workspace_title' => $hubTitle,
                'parent_route' => $hubRoute,
                'parent_route_params' => [],
                'ancestors' => [
                    $this->ancestor($hubTitle, $hubRoute),
                ],
                'route' => $sectionRoute,
                'route_params' => ['section' => $sectionKey],
            ]);

            foreach ($section['groups'] ?? [] as $group) {
                $groupLabel = $group['label'] ?? '';

                foreach ($group['items'] ?? [] as $item) {
                    if (! empty($item['coming_soon'])) {
                        continue;
                    }

                    $this->registerFeatureItem(
                        $workspaceKey,
                        $hubRoute,
                        $hubTitle,
                        $groupLabel,
                        $item,
                        $sectionKey,
                        $sectionTitle,
                        $sectionRoute,
                    );
                }
            }
        }
    }

    protected function registerSupplyChain(): void
    {
        $workspaceKey = 'supply-chain';
        $hubRoute = 'admin.workspaces.supply-chain';
        $hubTitle = __('Supply Chain');

        $this->registerEntry($hubRoute, [
            'workspace_key' => $workspaceKey,
            'parent_workspace_key' => null,
            'workspace_title' => $hubTitle,
            'parent_workspace_title' => null,
            'parent_route' => null,
            'parent_route_params' => [],
            'ancestors' => [],
        ]);

        $presenter = app(SupplyChainWorkspacePresenter::class);

        $this->registerEntry('admin.workspaces.supply-chain.section', [
            'workspace_key' => $workspaceKey,
            'parent_workspace_key' => $workspaceKey,
            'workspace_title' => null,
            'parent_workspace_title' => $hubTitle,
            'parent_route' => $hubRoute,
            'parent_route_params' => [],
            'ancestors' => [
                $this->ancestor($hubTitle, $hubRoute),
            ],
            'dynamic_section' => true,
        ]);

        foreach ($presenter->sectionDefinitions() as $sectionKey => $section) {
            $sectionRoute = 'admin.workspaces.supply-chain.section';
            $sectionTitle = $section['title'] ?? $sectionKey;

            $this->registerEntry("admin.workspaces.supply-chain.section:{$sectionKey}", [
                'workspace_key' => "{$workspaceKey}:{$sectionKey}",
                'parent_workspace_key' => $workspaceKey,
                'workspace_title' => $sectionTitle,
                'parent_workspace_title' => $hubTitle,
                'parent_route' => $hubRoute,
                'parent_route_params' => [],
                'ancestors' => [
                    $this->ancestor($hubTitle, $hubRoute),
                ],
                'route' => $sectionRoute,
                'route_params' => ['section' => $sectionKey],
            ]);

            foreach ($section['groups'] ?? [] as $group) {
                $groupLabel = $group['label'] ?? '';

                foreach ($group['items'] ?? [] as $item) {
                    if (! empty($item['coming_soon'])) {
                        continue;
                    }

                    $this->registerFeatureItem(
                        $workspaceKey,
                        $hubRoute,
                        $hubTitle,
                        $groupLabel,
                        $item,
                        $sectionKey,
                        $sectionTitle,
                        $sectionRoute,
                    );
                }
            }
        }
    }

    protected function registerCommercial(): void
    {
        $workspaceKey = 'commercial';
        $hubRoute = 'admin.workspaces.commercial';
        $hubTitle = __('Commercial');

        $this->registerEntry($hubRoute, [
            'workspace_key' => $workspaceKey,
            'parent_workspace_key' => null,
            'workspace_title' => $hubTitle,
            'parent_workspace_title' => null,
            'parent_route' => null,
            'parent_route_params' => [],
            'ancestors' => [],
        ]);

        $presenter = app(CommercialWorkspacePresenter::class);

        $this->registerEntry('admin.workspaces.commercial.section', [
            'workspace_key' => $workspaceKey,
            'parent_workspace_key' => $workspaceKey,
            'workspace_title' => null,
            'parent_workspace_title' => $hubTitle,
            'parent_route' => $hubRoute,
            'parent_route_params' => [],
            'ancestors' => [
                $this->ancestor($hubTitle, $hubRoute),
            ],
            'dynamic_section' => true,
        ]);

        foreach ($presenter->sectionDefinitions() as $sectionKey => $section) {
            $sectionRoute = 'admin.workspaces.commercial.section';
            $sectionTitle = $section['title'] ?? $sectionKey;

            $this->registerEntry("admin.workspaces.commercial.section:{$sectionKey}", [
                'workspace_key' => "{$workspaceKey}:{$sectionKey}",
                'parent_workspace_key' => $workspaceKey,
                'workspace_title' => $sectionTitle,
                'parent_workspace_title' => $hubTitle,
                'parent_route' => $hubRoute,
                'parent_route_params' => [],
                'ancestors' => [
                    $this->ancestor($hubTitle, $hubRoute),
                ],
                'route' => $sectionRoute,
                'route_params' => ['section' => $sectionKey],
            ]);

            foreach ($section['groups'] ?? [] as $group) {
                $groupLabel = $group['label'] ?? '';

                foreach ($group['items'] ?? [] as $item) {
                    if (! empty($item['coming_soon'])) {
                        continue;
                    }

                    $this->registerFeatureItem(
                        $workspaceKey,
                        $hubRoute,
                        $hubTitle,
                        $groupLabel,
                        $item,
                        $sectionKey,
                        $sectionTitle,
                        $sectionRoute,
                    );
                }
            }
        }
    }

    protected function registerDesigner(): void
    {
        $workspaceKey = 'designer';
        $hubRoute = 'admin.workspaces.designer';
        $hubTitle = __('Designer Desk');

        $this->registerEntry($hubRoute, [
            'workspace_key' => $workspaceKey,
            'parent_workspace_key' => null,
            'workspace_title' => $hubTitle,
            'parent_workspace_title' => null,
            'parent_route' => null,
            'parent_route_params' => [],
            'ancestors' => [],
        ]);

        $presenter = app(DesignerWorkspacePresenter::class);

        $this->registerEntry('admin.workspaces.designer.section', [
            'workspace_key' => $workspaceKey,
            'parent_workspace_key' => $workspaceKey,
            'workspace_title' => null,
            'parent_workspace_title' => $hubTitle,
            'parent_route' => $hubRoute,
            'parent_route_params' => [],
            'ancestors' => [
                $this->ancestor($hubTitle, $hubRoute),
            ],
            'dynamic_section' => true,
        ]);

        foreach ($presenter->sectionDefinitions() as $sectionKey => $section) {
            $sectionRoute = 'admin.workspaces.designer.section';
            $sectionTitle = $section['title'] ?? $sectionKey;

            $this->registerEntry("admin.workspaces.designer.section:{$sectionKey}", [
                'workspace_key' => "{$workspaceKey}:{$sectionKey}",
                'parent_workspace_key' => $workspaceKey,
                'workspace_title' => $sectionTitle,
                'parent_workspace_title' => $hubTitle,
                'parent_route' => $hubRoute,
                'parent_route_params' => [],
                'ancestors' => [
                    $this->ancestor($hubTitle, $hubRoute),
                ],
                'route' => $sectionRoute,
                'route_params' => ['section' => $sectionKey],
            ]);

            foreach ($section['groups'] ?? [] as $group) {
                $groupLabel = $group['label'] ?? '';

                foreach ($group['items'] ?? [] as $item) {
                    if (! empty($item['coming_soon'])) {
                        continue;
                    }

                    $this->registerFeatureItem(
                        $workspaceKey,
                        $hubRoute,
                        $hubTitle,
                        $groupLabel,
                        $item,
                        $sectionKey,
                        $sectionTitle,
                        $sectionRoute,
                    );
                }
            }
        }
    }

    protected function registerAdministration(): void
    {
        $workspaceKey = 'administration';
        $hubRoute = 'admin.workspaces.administration';
        $hubTitle = __('Administration');

        $this->registerEntry($hubRoute, [
            'workspace_key' => $workspaceKey,
            'parent_workspace_key' => null,
            'workspace_title' => $hubTitle,
            'parent_workspace_title' => null,
            'parent_route' => null,
            'parent_route_params' => [],
            'ancestors' => [],
        ]);

        $presenter = app(AdministrationWorkspacePresenter::class);

        $this->registerEntry('admin.workspaces.administration.section', [
            'workspace_key' => $workspaceKey,
            'parent_workspace_key' => $workspaceKey,
            'workspace_title' => null,
            'parent_workspace_title' => $hubTitle,
            'parent_route' => $hubRoute,
            'parent_route_params' => [],
            'ancestors' => [
                $this->ancestor($hubTitle, $hubRoute),
            ],
            'dynamic_section' => true,
        ]);

        foreach ($presenter->sectionDefinitions() as $sectionKey => $section) {
            $sectionRoute = 'admin.workspaces.administration.section';
            $sectionTitle = $section['title'] ?? $sectionKey;

            $this->registerEntry("admin.workspaces.administration.section:{$sectionKey}", [
                'workspace_key' => "{$workspaceKey}:{$sectionKey}",
                'parent_workspace_key' => $workspaceKey,
                'workspace_title' => $sectionTitle,
                'parent_workspace_title' => $hubTitle,
                'parent_route' => $hubRoute,
                'parent_route_params' => [],
                'ancestors' => [
                    $this->ancestor($hubTitle, $hubRoute),
                ],
                'route' => $sectionRoute,
                'route_params' => ['section' => $sectionKey],
            ]);

            foreach ($section['groups'] ?? [] as $group) {
                $groupLabel = $group['label'] ?? '';

                foreach ($group['items'] ?? [] as $item) {
                    if (! empty($item['coming_soon'])) {
                        continue;
                    }

                    $this->registerFeatureItem(
                        $workspaceKey,
                        $hubRoute,
                        $hubTitle,
                        $groupLabel,
                        $item,
                        $sectionKey,
                        $sectionTitle,
                        $sectionRoute,
                    );
                }
            }
        }
    }

    protected function registerHr(): void
    {
        $workspaceKey = 'hr';
        $hubRoute = 'admin.workspaces.hr';
        $hubTitle = __('HR');
        $catalog = config('hr_workspaces');

        if (! is_array($catalog)) {
            return;
        }

        $this->registerEntry($hubRoute, [
            'workspace_key' => $workspaceKey,
            'parent_workspace_key' => null,
            'workspace_title' => $hubTitle,
            'parent_workspace_title' => null,
            'parent_route' => null,
            'parent_route_params' => [],
            'ancestors' => [],
        ]);

        $this->registerEntry('admin.workspaces.hr.section', [
            'workspace_key' => $workspaceKey,
            'parent_workspace_key' => $workspaceKey,
            'workspace_title' => null,
            'parent_workspace_title' => $hubTitle,
            'parent_route' => $hubRoute,
            'parent_route_params' => [],
            'ancestors' => [
                $this->ancestor($hubTitle, $hubRoute),
            ],
            'dynamic_section' => true,
        ]);

        foreach ($catalog['sections'] ?? [] as $sectionKey => $section) {
            $sectionRoute = 'admin.workspaces.hr.section';
            $sectionTitle = $section['title'] ?? $sectionKey;

            $this->registerEntry("admin.workspaces.hr.section:{$sectionKey}", [
                'workspace_key' => "{$workspaceKey}:{$sectionKey}",
                'parent_workspace_key' => $workspaceKey,
                'workspace_title' => $sectionTitle,
                'parent_workspace_title' => $hubTitle,
                'parent_route' => $hubRoute,
                'parent_route_params' => [],
                'ancestors' => [
                    $this->ancestor($hubTitle, $hubRoute),
                ],
                'route' => $sectionRoute,
                'route_params' => ['section' => $sectionKey],
            ]);

            foreach ($section['groups'] ?? [] as $group) {
                $groupLabel = $group['label'] ?? '';

                foreach ($group['items'] ?? [] as $item) {
                    if (! empty($item['coming_soon'])) {
                        continue;
                    }

                    $this->registerFeatureItem(
                        $workspaceKey,
                        $hubRoute,
                        $hubTitle,
                        $groupLabel,
                        $item,
                        $sectionKey,
                        $sectionTitle,
                        $sectionRoute,
                    );
                }
            }
        }
    }

    protected function registerPrintingIntelligence(): void
    {
        $workspaceKey = 'printing-intelligence';
        $hubRoute = 'admin.workspaces.printing-intelligence';
        $hubTitle = __('Printing Intelligence');
        $catalog = config('printing_intelligence_workspaces');

        if (! is_array($catalog)) {
            return;
        }

        $this->registerEntry($hubRoute, [
            'workspace_key' => $workspaceKey,
            'parent_workspace_key' => null,
            'workspace_title' => $hubTitle,
            'parent_workspace_title' => null,
            'parent_route' => null,
            'parent_route_params' => [],
            'ancestors' => [],
        ]);

        $this->registerEntry('admin.workspaces.printing-intelligence.section', [
            'workspace_key' => $workspaceKey,
            'parent_workspace_key' => $workspaceKey,
            'workspace_title' => null,
            'parent_workspace_title' => $hubTitle,
            'parent_route' => $hubRoute,
            'parent_route_params' => [],
            'ancestors' => [
                $this->ancestor($hubTitle, $hubRoute),
            ],
            'dynamic_section' => true,
        ]);

        foreach ($catalog['sections'] ?? [] as $sectionKey => $section) {
            $sectionRoute = 'admin.workspaces.printing-intelligence.section';
            $sectionTitle = $section['title'] ?? $sectionKey;

            $this->registerEntry("admin.workspaces.printing-intelligence.section:{$sectionKey}", [
                'workspace_key' => "{$workspaceKey}:{$sectionKey}",
                'parent_workspace_key' => $workspaceKey,
                'workspace_title' => $sectionTitle,
                'parent_workspace_title' => $hubTitle,
                'parent_route' => $hubRoute,
                'parent_route_params' => [],
                'ancestors' => [
                    $this->ancestor($hubTitle, $hubRoute),
                ],
                'route' => $sectionRoute,
                'route_params' => ['section' => $sectionKey],
            ]);

            foreach ($section['groups'] ?? [] as $group) {
                $groupLabel = $group['label'] ?? '';

                foreach ($group['items'] ?? [] as $item) {
                    if (! empty($item['coming_soon'])) {
                        continue;
                    }

                    $this->registerFeatureItem(
                        $workspaceKey,
                        $hubRoute,
                        $hubTitle,
                        $groupLabel,
                        $item,
                        $sectionKey,
                        $sectionTitle,
                        $sectionRoute,
                    );
                }
            }
        }
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function registerFeatureItem(
        string $workspaceKey,
        string $hubRoute,
        string $hubTitle,
        string $groupLabel,
        array $item,
        ?string $sectionKey = null,
        ?string $sectionTitle = null,
        ?string $sectionRoute = null,
    ): void {
        $route = $item['route'] ?? null;

        if (! $route || ! Route::has($route)) {
            return;
        }

        $label = $item['label'] ?? '';
        $ancestors = [$this->ancestor($hubTitle, $hubRoute)];

        $parentRoute = $hubRoute;
        $parentParams = [];
        $parentKey = $workspaceKey;
        $parentTitle = $hubTitle;

        if ($sectionKey !== null) {
            $parentRoute = $sectionRoute ?? match ($workspaceKey) {
                'supply-chain' => 'admin.workspaces.supply-chain.section',
                'commercial' => 'admin.workspaces.commercial.section',
                'administration' => 'admin.workspaces.administration.section',
                'hr' => 'admin.workspaces.hr.section',
                'printing-intelligence' => 'admin.workspaces.printing-intelligence.section',
                default => 'admin.workspaces.accounting.section',
            };
            $parentParams = ['section' => $sectionKey];
            $parentKey = "{$workspaceKey}:{$sectionKey}";
            $sectionLabel = $sectionTitle ?? $sectionKey;
            $useGroup = $workspaceKey === 'accounting' && $sectionKey === 'general-ledger' && $groupLabel !== '';
            $parentTitle = $useGroup ? $groupLabel : $sectionLabel;
            $breadcrumbLabel = $parentTitle;
            $ancestors[] = $this->ancestor($breadcrumbLabel, $parentRoute, $parentParams);
        } elseif ($groupLabel !== '') {
            $parentTitle = $groupLabel;
        }

        $entry = [
            'workspace_key' => $sectionKey ? "{$workspaceKey}:{$sectionKey}:{$route}" : "{$workspaceKey}:{$route}",
            'parent_workspace_key' => $parentKey,
            'workspace_title' => $label,
            'parent_workspace_title' => $parentTitle,
            'parent_route' => $parentRoute,
            'parent_route_params' => $parentParams,
            'ancestors' => $ancestors,
            'group_label' => $groupLabel,
        ];

        $this->registerEntry($route, $entry);

        foreach ($item['active_routes'] ?? [] as $pattern) {
            if ($pattern === $route) {
                continue;
            }

            if (! str_contains($pattern, '*') && ! str_contains($pattern, ':') && Route::has($pattern)) {
                $this->registerEntry($pattern, $entry);
            } else {
                $this->registerPattern($pattern, $entry);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $entry
     */
    protected function registerEntry(string $route, array $entry): void
    {
        $this->byRoute[$route] = $entry;
    }

    /**
     * @param  array<string, mixed>  $entry
     */
    protected function registerPattern(string $pattern, array $entry): void
    {
        $this->byPattern[$pattern] = $entry;
    }

    protected function inferFromRoute(string $routeName): ?array
    {
        $parentRoute = $this->inferParentRouteName($routeName);

        if (! $parentRoute) {
            return null;
        }

        $parent = $this->resolve($parentRoute);

        if (! $parent) {
            return null;
        }

        return [
            'workspace_key' => "{$parent['workspace_key']}:{$routeName}",
            'parent_workspace_key' => $parent['workspace_key'],
            'workspace_title' => null,
            'parent_workspace_title' => $parent['workspace_title'] ?? '',
            'parent_route' => $parentRoute,
            'parent_route_params' => $parent['parent_route_params'] ?? [],
            'ancestors' => $parent['ancestors'] ?? [],
            'inferred_child' => true,
        ];
    }

    protected function inferParentRouteName(string $routeName): ?string
    {
        if (preg_match('/^(.+)\.(show|edit|create|store|update)$/', $routeName, $matches)) {
            $base = $matches[1];

            if (Route::has($base)) {
                return $base;
            }

            if (Route::has($base.'.index')) {
                return $base.'.index';
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array{label: string, route: string, params: array<string, mixed>}
     */
    protected function ancestor(string $label, string $route, array $params = []): array
    {
        return [
            'label' => $label,
            'route' => $route,
            'params' => $params,
        ];
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
}
