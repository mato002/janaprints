<?php

namespace App\Support\Discovery;

use App\Support\Navigation\ModuleShellPresenter;
use App\Support\Navigation\WorkspaceNavigationResolver;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class FeatureRegistry
{
    public function __construct(
        protected ?ModuleShellPresenter $moduleShell = null,
        protected ?WorkspaceNavigationResolver $navigation = null,
    ) {
        $this->moduleShell ??= app(ModuleShellPresenter::class);
        $this->navigation ??= app(WorkspaceNavigationResolver::class);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function index(?string $moduleKey = null): array
    {
        $entries = [];

        foreach ($this->moduleShell->moduleDefinitions() as $key => $module) {
            if ($moduleKey !== null && $key !== $moduleKey) {
                continue;
            }

            $catalog = config($module['config'] ?? '');

            if (! is_array($catalog) || $catalog === []) {
                continue;
            }

            $moduleTitle = (string) ($module['title'] ?? $key);
            $entries = array_merge($entries, $this->collectFromCatalog($key, $moduleTitle, $catalog, $module));
        }

        return $this->deduplicateEntries($entries);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function search(string $query, ?string $moduleKey = null): array
    {
        $tokens = $this->tokenize($query);

        if ($tokens === []) {
            return [];
        }

        return array_values(array_filter(
            $this->index($moduleKey),
            fn (array $entry) => $this->entryMatchesTokens($entry, $tokens),
        ));
    }

    /**
     * @param  array<string, mixed>  $module
     * @param  array<string, mixed>  $catalog
     * @return list<array<string, mixed>>
     */
    protected function collectFromCatalog(string $moduleKey, string $moduleTitle, array $catalog, array $module): array
    {
        $entries = [];

        foreach ($catalog['hub'] ?? [] as $hubItem) {
            if (! $this->itemIsAccessible($hubItem)) {
                continue;
            }

            $workspaceLabel = (string) ($hubItem['label'] ?? '');
            $segments = [$moduleTitle, $workspaceLabel];

            $entries[] = $this->makeEntry(
                moduleKey: $moduleKey,
                module: $moduleTitle,
                workspace: $workspaceLabel,
                subWorkspace: null,
                group: null,
                item: $hubItem,
                pathSegments: $segments,
                category: 'workspaces',
            );
        }

        foreach ($catalog['sections'] ?? [] as $sectionKey => $section) {
            if (! empty($section['permission']) && ! $this->userCan($section['permission'])) {
                continue;
            }

            $sectionTitle = (string) ($section['title'] ?? $sectionKey);

            foreach ($section['groups'] ?? [] as $group) {
                $groupLabel = (string) ($group['label'] ?? '');

                foreach ($group['items'] ?? [] as $item) {
                    if (! $this->itemIsAccessible($item, includeComingSoon: false)) {
                        continue;
                    }

                    $segments = array_values(array_filter([
                        $moduleTitle,
                        $sectionTitle,
                        $groupLabel !== '' ? $groupLabel : null,
                        (string) ($item['label'] ?? ''),
                    ]));

                    $entries[] = $this->makeEntry(
                        moduleKey: $moduleKey,
                        module: $moduleTitle,
                        workspace: $sectionTitle,
                        subWorkspace: $groupLabel !== '' ? $groupLabel : null,
                        group: $groupLabel,
                        item: $item,
                        pathSegments: $segments,
                        category: $this->resolveCategory($item, $moduleKey),
                    );
                }
            }
        }

        foreach ($catalog['groups'] ?? [] as $group) {
            $groupLabel = (string) ($group['label'] ?? '');

            foreach ($group['items'] ?? [] as $item) {
                if (! $this->itemIsAccessible($item, includeComingSoon: false)) {
                    continue;
                }

                $segments = array_values(array_filter([
                    $moduleTitle,
                    $groupLabel !== '' ? $groupLabel : null,
                    (string) ($item['label'] ?? ''),
                ]));

                $entries[] = $this->makeEntry(
                    moduleKey: $moduleKey,
                    module: $moduleTitle,
                    workspace: $groupLabel !== '' ? $groupLabel : $moduleTitle,
                    subWorkspace: null,
                    group: $groupLabel,
                    item: $item,
                    pathSegments: $segments,
                    category: $this->resolveCategory($item, $moduleKey),
                );
            }
        }

        return $entries;
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  list<string>  $pathSegments
     * @return array<string, mixed>
     */
    protected function makeEntry(
        string $moduleKey,
        string $module,
        string $workspace,
        ?string $subWorkspace,
        ?string $group,
        array $item,
        array $pathSegments,
        string $category,
    ): array {
        $label = (string) ($item['label'] ?? '');
        $description = (string) ($item['description'] ?? '');
        $route = $item['route'] ?? null;
        $routeParams = $item['route_params'] ?? [];
        $url = null;

        if ($route && Route::has($route) && empty($item['coming_soon'])) {
            $url = $this->navigation->appendPreservedQuery(route($route, $routeParams));
        }

        $keywords = $this->buildKeywords($label, $description, $pathSegments, $item);

        return [
            'id' => $this->entryId($moduleKey, $route, $routeParams, $label, $pathSegments),
            'label' => $label,
            'description' => $description,
            'module' => $module,
            'module_key' => $moduleKey,
            'workspace' => $workspace,
            'sub_workspace' => $subWorkspace,
            'group' => $group,
            'path' => implode(' → ', $pathSegments),
            'path_segments' => $pathSegments,
            'route' => $route,
            'route_params' => $routeParams,
            'permission' => $item['permission'] ?? null,
            'category' => $category,
            'keywords' => $keywords,
            'search_text' => implode(' ', $keywords),
            'icon' => $item['icon'] ?? 'home',
            'url' => $url,
            'coming_soon' => (bool) ($item['coming_soon'] ?? false),
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  list<string>  $pathSegments
     * @return list<string>
     */
    protected function buildKeywords(string $label, string $description, array $pathSegments, array $item): array
    {
        $tokens = [];

        foreach ([$label, $description, ...$pathSegments] as $value) {
            $tokens = array_merge($tokens, $this->tokenize($value));
        }

        foreach ($item['keywords'] ?? [] as $keyword) {
            $tokens = array_merge($tokens, $this->tokenize((string) $keyword));
        }

        $aliases = config('feature_registry.keyword_aliases', []);

        foreach ($tokens as $token) {
            foreach ($aliases[$token] ?? [] as $alias) {
                $tokens = array_merge($tokens, $this->tokenize($alias));
            }
        }

        return array_values(array_unique(array_filter($tokens)));
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function resolveCategory(array $item, string $moduleKey): string
    {
        if ($moduleKey === 'reports') {
            return 'reports';
        }

        $haystack = strtolower(implode(' ', [
            (string) ($item['label'] ?? ''),
            (string) ($item['description'] ?? ''),
            (string) ($item['route'] ?? ''),
        ]));

        foreach (config('feature_registry.category_rules', []) as $category => $signals) {
            foreach ($signals as $signal) {
                if (str_contains($haystack, $signal)) {
                    return $category;
                }
            }
        }

        return 'features';
    }

    /**
     * @param  list<array<string, mixed>>  $entries
     * @return list<array<string, mixed>>
     */
    protected function deduplicateEntries(array $entries): array
    {
        $seen = [];
        $unique = [];

        foreach ($entries as $entry) {
            $id = (string) ($entry['id'] ?? '');

            if ($id === '' || isset($seen[$id])) {
                continue;
            }

            $seen[$id] = true;
            $unique[] = $entry;
        }

        return $unique;
    }

    /**
     * @param  array<string, mixed>  $entry
     * @param  list<string>  $tokens
     */
    protected function entryMatchesTokens(array $entry, array $tokens): bool
    {
        if (! empty($entry['coming_soon']) || empty($entry['url'])) {
            return false;
        }

        $haystack = strtolower((string) ($entry['search_text'] ?? ''));

        foreach ($tokens as $token) {
            if (! str_contains($haystack, $token)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return list<string>
     */
    protected function tokenize(string $value): array
    {
        $normalized = Str::lower(Str::ascii($value));
        $normalized = preg_replace('/[^a-z0-9]+/i', ' ', $normalized) ?? '';
        $parts = preg_split('/\s+/', trim($normalized)) ?: [];

        return array_values(array_filter($parts, fn (string $part) => $part !== ''));
    }

    /**
     * @param  array<string, mixed>  $routeParams
     * @param  list<string>  $pathSegments
     */
    protected function entryId(
        string $moduleKey,
        ?string $route,
        array $routeParams,
        string $label,
        array $pathSegments,
    ): string {
        $payload = implode('|', [
            $moduleKey,
            (string) $route,
            json_encode($routeParams),
            $label,
            implode('>', $pathSegments),
        ]);

        return hash('sha256', $payload);
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
