<?php

namespace App\Support\Navigation;

use Illuminate\Support\Facades\Route;

class DesignerWorkspacePresenter
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
        return config('designer_workspaces.hub', []);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function sectionDefinitions(): array
    {
        return config('designer_workspaces.sections', []);
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
            'admin.workspaces.designer',
            'admin.workspaces.designer.section',
        ];

        foreach ($this->hubDefinitions() as $item) {
            $routes = array_merge($routes, $this->itemRoutePatterns($item));
        }

        foreach ($this->sectionDefinitions() as $section => $definition) {
            $routes[] = "admin.workspaces.designer.section:{$section}";

            foreach ($definition['groups'] ?? [] as $group) {
                foreach ($group['items'] ?? [] as $item) {
                    $routes = array_merge($routes, $this->itemRoutePatterns($item));
                }
            }
        }

        return array_values(array_unique($routes));
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
