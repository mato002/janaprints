<?php

namespace App\Support\Navigation;

use Illuminate\Support\Facades\Route;

class HrWorkspacePresenter
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
        return config('hr_workspaces.hub', []);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function sectionDefinitions(): array
    {
        return config('hr_workspaces.sections', []);
    }

    public function sectionExists(string $section): bool
    {
        return array_key_exists($section, $this->sectionDefinitions());
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

    /**
     * @return list<string>
     */
    public function collectActiveRoutes(): array
    {
        $routes = [
            'admin.workspaces.hr',
            'admin.workspaces.hr.section',
            'admin.hr.dashboard',
        ];

        foreach ($this->hubDefinitions() as $item) {
            $routes = array_merge($routes, $this->itemRoutePatterns($item));
        }

        foreach ($this->sectionDefinitions() as $section => $definition) {
            $routes[] = "admin.workspaces.hr.section:{$section}";

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
        $workspaceTitle = __('HR');

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
                        'route_params' => $item['route_params'] ?? [],
                        'coming_soon' => false,
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
