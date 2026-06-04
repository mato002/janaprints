<?php

namespace App\Support\Navigation;

use Illuminate\Support\Facades\Route;

class CommercialWorkspacePresenter
{
    public function __construct(
        protected ?WorkspaceNavigationResolver $navigation = null,
    ) {
        $this->navigation ??= app(WorkspaceNavigationResolver::class);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function hubDefinitions(): array
    {
        return config('commercial_workspaces.hub', []);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function sectionDefinitions(): array
    {
        return config('commercial_workspaces.sections', []);
    }

    public function sectionExists(string $section): bool
    {
        return array_key_exists($section, $this->sectionDefinitions());
    }

    /**
     * @return array{title: string, description: string, icon: string, items: list<array<string, mixed>>}|null
     */
    public function presentHub(): ?array
    {
        $items = array_map(
            fn (array $item) => $this->presentHubItem($item),
            $this->filterItems($this->hubDefinitions(), includeComingSoon: true),
        );

        if ($items === []) {
            return null;
        }

        return [
            'title' => __('Commercial'),
            'description' => __('CRM, sales, customer service, point of sale, and commercial reporting.'),
            'icon' => 'shopping-cart',
            'items' => $items,
        ];
    }

    /**
     * @return array{key: string, title: string, description: string, icon: string, groups: list<array{label: string, items: list<array<string, mixed>>}>}|null
     */
    public function presentSection(string $section): ?array
    {
        $definition = $this->sectionDefinitions()[$section] ?? null;

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
                'items' => array_map(fn (array $item) => $this->presentFeatureItem($item), $items),
            ];
        }

        if ($groups === []) {
            return null;
        }

        return [
            'key' => $section,
            'title' => $definition['title'],
            'description' => $definition['description'],
            'icon' => $definition['icon'] ?? 'home',
            'groups' => $groups,
        ];
    }

    public function isVisible(): bool
    {
        foreach ($this->hubDefinitions() as $item) {
            if ($this->itemIsAccessible($item)) {
                return true;
            }
        }

        return false;
    }

    public function sectionIsVisible(string $section): bool
    {
        return $this->presentSection($section) !== null;
    }

    /**
     * @return list<string>
     */
    public function collectActiveRoutes(): array
    {
        $routes = [
            'admin.workspaces.commercial',
            'admin.workspaces.commercial.section',
        ];

        foreach ($this->hubDefinitions() as $item) {
            $routes = array_merge($routes, $this->itemRoutePatterns($item));
        }

        foreach ($this->sectionDefinitions() as $section => $definition) {
            $routes[] = "admin.workspaces.commercial.section:{$section}";

            foreach ($definition['groups'] ?? [] as $group) {
                foreach ($group['items'] ?? [] as $item) {
                    $routes = array_merge($routes, $this->itemRoutePatterns($item));
                }
            }
        }

        return array_values(array_unique($routes));
    }

    /**
     * @return list<array{label: string, path: string, route: ?string, coming_soon: bool}>
     */
    public function flattenForSearch(): array
    {
        $flat = [];
        $workspaceTitle = __('Commercial');

        foreach ($this->hubDefinitions() as $item) {
            if (! $this->itemIsAccessible($item)) {
                continue;
            }

            $flat[] = [
                'label' => $item['label'] ?? '',
                'path' => "{$workspaceTitle} › ".($item['label'] ?? ''),
                'route' => $item['route'] ?? null,
                'route_params' => $item['route_params'] ?? [],
                'coming_soon' => false,
            ];
        }

        foreach ($this->sectionDefinitions() as $sectionKey => $definition) {
            $sectionTitle = $definition['title'] ?? $sectionKey;

            foreach ($definition['groups'] ?? [] as $group) {
                $groupLabel = $group['label'] ?? '';

                foreach ($this->filterItems($group['items'] ?? []) as $item) {
                    $flat[] = [
                        'label' => $item['label'] ?? '',
                        'path' => "{$workspaceTitle} › {$sectionTitle} › {$groupLabel} › ".($item['label'] ?? ''),
                        'route' => $item['route'] ?? null,
                        'route_params' => [],
                        'coming_soon' => (bool) ($item['coming_soon'] ?? false),
                    ];
                }
            }
        }

        return $flat;
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return list<array<string, mixed>>
     */
    protected function filterItems(array $items, bool $includeComingSoon = false): array
    {
        $filtered = [];

        foreach ($items as $item) {
            if (! empty($item['coming_soon'])) {
                if ($includeComingSoon) {
                    $filtered[] = $item;
                }

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
     * @param  array<string, mixed>  $item
     * @return list<string>
     */
    protected function itemRoutePatterns(array $item): array
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

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    protected function presentHubItem(array $item): array
    {
        $href = $this->resolveHref($item);

        return [
            'id' => md5(($item['label'] ?? '').($item['route'] ?? '')),
            'label' => $item['label'] ?? '',
            'description' => $item['description'] ?? '',
            'icon' => $item['icon'] ?? 'home',
            'href' => $href,
            'comingSoon' => $href === null,
            'statusLabel' => $href === null ? __('Coming Soon') : __('Active'),
            'statusVariant' => $href === null ? 'neutral' : 'success',
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    protected function presentFeatureItem(array $item): array
    {
        $comingSoon = (bool) ($item['coming_soon'] ?? false);
        $href = $comingSoon ? null : $this->resolveHref($item);

        return [
            'id' => md5(($item['label'] ?? '').($item['route'] ?? 'soon')),
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
     */
    protected function resolveHref(array $item): ?string
    {
        $route = $item['route'] ?? null;

        if (! $route || ! Route::has($route)) {
            return null;
        }

        $params = $item['route_params'] ?? [];

        return $this->navigation->appendPreservedQuery(route($route, $params));
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
