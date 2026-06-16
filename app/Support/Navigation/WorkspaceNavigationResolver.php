<?php

namespace App\Support\Navigation;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class WorkspaceNavigationResolver
{
    public function __construct(
        protected WorkspaceNavigationRegistry $registry,
    ) {}

    /**
     * @return array{
     *     workspace_key: string,
     *     parent_workspace_key: ?string,
     *     workspace_title: string,
     *     parent_workspace_title: ?string,
     *     parent_url: ?string,
     *     show_back: bool,
     *     breadcrumbs: list<array{label: string, url?: string}>
     * }|null
     */
    public function resolve(?string $routeName = null, ?string $pageTitle = null, ?Request $request = null): ?array
    {
        $routeName ??= Route::currentRouteName();
        $request ??= request();

        if (! $routeName || $routeName === 'admin.dashboard') {
            return null;
        }

        $entry = $this->registry->resolve($routeName);

        if (! $entry) {
            return null;
        }

        if (in_array($routeName, [
            'admin.workspaces.accounting.section',
            'admin.workspaces.supply-chain.section',
            'admin.workspaces.commercial.section',
            'admin.workspaces.administration.section',
            'admin.workspaces.hr.section',
            'admin.workspaces.printing-intelligence.section',
        ], true)) {
            $section = $request->route('section');
            $sectionEntry = $this->registry->resolve("{$routeName}:{$section}");

            if ($sectionEntry) {
                $entry = $sectionEntry;
            }
        }

        $title = $pageTitle ?: ($entry['workspace_title'] ?? __('Page'));

        if (($entry['dynamic_section'] ?? false) && $pageTitle) {
            $title = $pageTitle;
        }

        $breadcrumbs = $this->buildBreadcrumbs($entry, $title, $request);
        $parentUrl = $this->buildParentUrl($entry, $request);

        return [
            'workspace_key' => $entry['workspace_key'],
            'parent_workspace_key' => $entry['parent_workspace_key'] ?? null,
            'workspace_title' => $title,
            'parent_workspace_title' => $entry['parent_workspace_title'] ?? null,
            'parent_url' => $parentUrl,
            'show_back' => $parentUrl !== null && ($entry['parent_workspace_title'] ?? '') !== '',
            'breadcrumbs' => $breadcrumbs,
        ];
    }

    /**
     * @param  array<string, mixed>  $entry
     * @return list<array{label: string, url?: string}>
     */
    protected function buildBreadcrumbs(array $entry, string $pageTitle, Request $request): array
    {
        $crumbs = [];

        foreach ($entry['ancestors'] ?? [] as $ancestor) {
            $crumbs[] = [
                'label' => $ancestor['label'],
                'url' => $this->routeUrl(
                    $ancestor['route'],
                    $ancestor['params'] ?? [],
                    $request,
                ),
            ];
        }

        if ($entry['inferred_child'] ?? false) {
            $parentRoute = $entry['parent_route'] ?? null;

            if ($parentRoute && Route::has($parentRoute)) {
                $crumbs[] = [
                    'label' => $entry['parent_workspace_title'] ?? '',
                    'url' => $this->routeUrl(
                        $parentRoute,
                        $entry['parent_route_params'] ?? [],
                        $request,
                    ),
                ];
            }
        }

        $crumbs[] = ['label' => $pageTitle];

        return $crumbs;
    }

    /**
     * @param  array<string, mixed>  $entry
     */
    protected function buildParentUrl(array $entry, Request $request): ?string
    {
        $parentRoute = $entry['parent_route'] ?? null;

        if (! $parentRoute || ! Route::has($parentRoute)) {
            return null;
        }

        $params = $entry['parent_route_params'] ?? [];

        return $this->routeUrl($parentRoute, $params, $request, preferStoredQuery: true);
    }

    /**
     * @param  array<string, mixed>  $params
     */
    protected function routeUrl(
        string $route,
        array $params = [],
        ?Request $request = null,
        bool $preferStoredQuery = false,
    ): string {
        $request ??= request();

        $url = route($route, $params);
        $query = $preferStoredQuery
            ? $this->queryForRoute($route, $request)
            : $this->filterPreservedQuery($request->query());

        if ($query === []) {
            return $url;
        }

        return $url.(str_contains($url, '?') ? '&' : '?').http_build_query($query);
    }

    /**
     * @return array<string, mixed>
     */
    public function queryForRoute(string $route, ?Request $request = null): array
    {
        $request ??= request();
        $stored = session()->get("workspace_nav.query.{$route}", []);

        if (is_array($stored) && $stored !== []) {
            return $this->filterPreservedQuery($stored);
        }

        return $this->filterPreservedQuery($request->query());
    }

    /**
     * @param  array<string, mixed>|null  $query
     * @return array<string, mixed>
     */
    public function filterPreservedQuery(?array $query = null): array
    {
        $query ??= request()->query();
        $keys = config('workspace_navigation.preserve_query_keys', []);

        return array_filter(
            $query,
            fn ($value, $key) => in_array($key, $keys, true) && $value !== null && $value !== '',
            ARRAY_FILTER_USE_BOTH,
        );
    }

    /**
     * @param  list<string>  $exclude
     */
    public function appendPreservedQuery(?string $url, ?Request $request = null, array $exclude = []): ?string
    {
        if (! $url) {
            return null;
        }

        $existing = [];
        $fragment = '';

        if (($hashPos = strpos($url, '#')) !== false) {
            $fragment = substr($url, $hashPos);
            $url = substr($url, 0, $hashPos);
        }

        if (($queryPos = strpos($url, '?')) !== false) {
            parse_str(substr($url, $queryPos + 1), $existing);
            $url = substr($url, 0, $queryPos);
        }

        $query = $this->filterPreservedQuery($request?->query());

        foreach ($exclude as $key) {
            unset($query[$key]);
        }

        foreach (array_keys($existing) as $key) {
            unset($query[$key]);
        }

        $merged = array_merge($existing, $query);

        if ($merged === []) {
            return $url.$fragment;
        }

        return $url.'?'.http_build_query($merged).$fragment;
    }
}
