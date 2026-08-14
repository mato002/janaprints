<?php

namespace App\Support\AccessControl;

/**
 * Progressive role-access presentation.
 * Role → Module access → Module matrix (never a global matrix).
 */
class RoleAccessWorkspace
{
    public function __construct(
        protected PermissionCatalog $catalog,
    ) {}

    /**
     * @param  list<string>  $assigned
     * @return array{
     *     modules: list<array<string, mixed>>,
     *     columns: list<array{key: string, label: string}>,
     *     granted: list<string>,
     *     uncatalogued: list<string>,
     *     access_summary: string
     * }
     */
    public function payload(array $assigned): array
    {
        $assignedSet = array_fill_keys($assigned, true);
        $standardColumns = config('permission_catalog.columns', []);
        $columnDefs = collect($standardColumns)
            ->map(fn (array $meta, string $key) => [
                'key' => $key,
                'label' => __($meta['label'] ?? ucfirst($key)),
            ])
            ->values()
            ->all();

        $modules = [];
        $catalogued = [];

        foreach (config('permission_catalog.modules', []) as $moduleKey => $module) {
            $permissions = [];
            $sectionsMap = [];

            foreach ($module['entities'] ?? [] as $entityKey => $entity) {
                $cells = [];
                $advanced = [];

                foreach ($entity['permissions'] ?? [] as $actionKey => $permission) {
                    if (! is_string($permission) || $permission === '') {
                        continue;
                    }

                    $permissions[] = $permission;
                    $catalogued[] = $permission;

                    if (array_key_exists($actionKey, $standardColumns)) {
                        $cells[$actionKey] = $permission;
                    } else {
                        $advanced[] = [
                            'key' => $permission,
                            'label' => __(ucwords(str_replace(['_', '-'], ' ', (string) $actionKey))),
                            'permission' => $permission,
                        ];
                    }
                }

                foreach ($entity['extra'] ?? [] as $extra) {
                    $permission = $extra['permission'] ?? null;
                    if (! is_string($permission) || $permission === '') {
                        continue;
                    }

                    $permissions[] = $permission;
                    $catalogued[] = $permission;
                    $advanced[] = [
                        'key' => $permission,
                        'label' => __($extra['label'] ?? $permission),
                        'permission' => $permission,
                    ];
                }

                if ($cells === [] && $advanced === []) {
                    continue;
                }

                $groupLabel = trim((string) ($entity['group'] ?? ''));
                if ($groupLabel === '') {
                    $groupLabel = __('General');
                }

                $sectionsMap[$groupLabel] ??= [
                    'key' => \Illuminate\Support\Str::slug($groupLabel),
                    'label' => $groupLabel,
                    'capabilities' => [],
                ];

                $rowPermissions = array_values(array_unique([
                    ...array_values(array_filter($cells)),
                    ...array_column($advanced, 'permission'),
                ]));

                $sectionsMap[$groupLabel]['capabilities'][] = [
                    'key' => "{$moduleKey}.{$entityKey}",
                    'label' => __($entity['label'] ?? $entityKey),
                    'cells' => $cells,
                    'advanced' => $advanced,
                    'permissions' => $rowPermissions,
                ];
            }

            $permissions = array_values(array_unique($permissions));
            $enabledCount = count(array_filter($permissions, fn (string $p) => isset($assignedSet[$p])));
            $totalCount = count($permissions);
            $status = match (true) {
                $totalCount === 0, $enabledCount === 0 => 'none',
                $enabledCount >= $totalCount => 'full',
                default => 'partial',
            };

            $sections = array_values($sectionsMap);
            $onlyGeneral = count($sections) === 1 && ($sections[0]['label'] ?? '') === __('General');

            $modules[] = [
                'key' => $moduleKey,
                'label' => __($module['label'] ?? $moduleKey),
                'enabled' => $enabledCount > 0,
                'status' => $status,
                'enabled_count' => $enabledCount,
                'total_count' => $totalCount,
                'permissions' => $permissions,
                'show_section_headers' => ! $onlyGeneral,
                'sections' => $sections,
            ];
        }

        $enabledLabels = collect($modules)
            ->filter(fn (array $module) => $module['enabled'])
            ->pluck('label')
            ->values()
            ->all();

        return [
            'modules' => $modules,
            'columns' => $columnDefs,
            'granted' => array_values(array_unique($assigned)),
            'uncatalogued' => array_values(array_diff($assigned, array_unique($catalogued))),
            'access_summary' => $this->summarizeAccess($enabledLabels, count($modules)),
        ];
    }

    /**
     * @param  list<string>  $labels
     */
    public function summarizeAccess(array $labels, int $totalModules): string
    {
        if ($labels === []) {
            return __('No modules');
        }

        if (count($labels) >= max(1, $totalModules - 1) && count($labels) >= 8) {
            return __('All modules');
        }

        if (count($labels) <= 3) {
            return implode(', ', $labels);
        }

        return implode(', ', array_slice($labels, 0, 3)).' +'.(count($labels) - 3);
    }
}
