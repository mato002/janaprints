<?php

namespace App\Support\Navigation;

use Illuminate\Support\Facades\Route;

class PrintingIntelligenceWorkspacePresenter
{
    /**
     * @return list<array<string, mixed>>
     */
    public function hubDefinitions(): array
    {
        return config('printing_intelligence_workspaces.hub', []);
    }

    public function isVisible(): bool
    {
        foreach ($this->hubDefinitions() as $item) {
            if ($this->itemIsAccessible($item)) {
                return true;
            }
        }

        return auth()->user()?->can('printing.intelligence.view') ?? false;
    }

    /**
     * @return list<string>
     */
    public function collectActiveRoutes(): array
    {
        $routes = [
            'admin.workspaces.printing-intelligence',
        ];

        foreach ($this->hubDefinitions() as $item) {
            $routes = array_merge($routes, $this->itemRoutePatterns($item));
        }

        return array_values(array_unique($routes));
    }

    /**
     * @return list<array{label: string, path: string, route: ?string, coming_soon: bool}>
     */
    public function flattenForSearch(): array
    {
        $flat = [];
        $workspaceTitle = __('Printing Intelligence');

        foreach ($this->hubDefinitions() as $item) {
            if (! $this->itemIsAccessible($item)) {
                continue;
            }

            $flat[] = [
                'label' => $item['label'] ?? '',
                'path' => "{$workspaceTitle} › ".($item['label'] ?? ''),
                'route' => $item['route'] ?? null,
                'coming_soon' => false,
            ];
        }

        return $flat;
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

    protected function itemIsAccessible(array $item): bool
    {
        if (! empty($item['route']) && ! Route::has($item['route'])) {
            return false;
        }

        if (! empty($item['permission']) && ! auth()->user()?->can($item['permission'])) {
            return false;
        }

        return true;
    }
}
