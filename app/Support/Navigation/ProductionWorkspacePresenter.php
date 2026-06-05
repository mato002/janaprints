<?php

namespace App\Support\Navigation;

use App\Support\Production\ProductionWorkspaceCountService;
use Illuminate\Support\Facades\Route;

class ProductionWorkspacePresenter
{
    public function __construct(
        protected ?WorkspaceNavigationResolver $navigation = null,
        protected ?ProductionWorkspaceCountService $counts = null,
    ) {
        $this->navigation ??= app(WorkspaceNavigationResolver::class);
        $this->counts ??= app(ProductionWorkspaceCountService::class);
    }

    /**
     * @return list<array{label: string, items: list<array<string, mixed>>}>
     */
    public function groupDefinitions(): array
    {
        return config('production_workspaces.groups', []);
    }

    /**
     * @return array{key: string, title: string, description: string, icon: string, groups: list<array{label: string, items: list<array<string, mixed>>}>}|null
     */
    public function presentHub(): ?array
    {
        $groups = [];

        foreach ($this->groupDefinitions() as $group) {
            $items = $this->filterItems($group['items'] ?? []);

            if ($items === []) {
                continue;
            }

            $groups[] = [
                'label' => $group['label'],
                'items' => array_map(fn (array $item) => $this->presentItem($item), $items),
            ];
        }

        if ($groups === []) {
            return null;
        }

        return [
            'key' => 'production',
            'title' => __('Production'),
            'description' => __('Job cards, scheduling, work centers, quality, dispatch, and production intelligence.'),
            'icon' => 'cog',
            'groups' => $groups,
        ];
    }

    public function isVisible(): bool
    {
        foreach ($this->groupDefinitions() as $group) {
            foreach ($group['items'] ?? [] as $item) {
                if ($this->itemIsAccessible($item)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return list<string>
     */
    public function collectActiveRoutes(): array
    {
        $routes = [
            'admin.workspaces.production',
        ];

        foreach ($this->groupDefinitions() as $group) {
            foreach ($group['items'] ?? [] as $item) {
                $routes = array_merge($routes, $this->itemRoutePatterns($item));
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
        $workspaceTitle = __('Production');

        foreach ($this->groupDefinitions() as $group) {
            $groupLabel = $group['label'] ?? '';

            foreach ($this->filterItems($group['items'] ?? []) as $item) {
                $flat[] = [
                    'label' => $item['label'] ?? '',
                    'path' => "{$workspaceTitle} › {$groupLabel} › ".($item['label'] ?? ''),
                    'route' => $item['route'] ?? null,
                    'route_params' => [],
                    'coming_soon' => false,
                ];
            }
        }

        return $flat;
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return list<array<string, mixed>>
     */
    protected function filterItems(array $items): array
    {
        $filtered = [];

        foreach ($items as $item) {
            if (! $this->itemIsAccessible($item)) {
                continue;
            }

            $filtered[] = $item;
        }

        return $filtered;
    }

    protected function itemIsAccessible(array $item): bool
    {
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
    protected function presentItem(array $item): array
    {
        $route = $item['route'] ?? null;
        $href = null;

        if ($route && Route::has($route)) {
            $href = $this->navigation->appendPreservedQuery(route($route));
        }

        $countKey = $item['count_key'] ?? null;
        $count = $this->counts->resolve($countKey);

        return [
            'id' => md5(($item['label'] ?? '').($route ?? '')),
            'label' => $item['label'] ?? '',
            'description' => $item['description'] ?? '',
            'icon' => $item['icon'] ?? 'home',
            'href' => $href,
            'count' => $count,
            'comingSoon' => $href === null,
            'statusLabel' => $href === null ? __('Coming Soon') : __('Active'),
            'statusVariant' => $href === null ? 'neutral' : 'success',
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
}
