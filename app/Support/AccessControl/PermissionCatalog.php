<?php

namespace App\Support\AccessControl;

use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;

class PermissionCatalog
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function matrixSections(): Collection
    {
        return collect(config('permission_catalog.modules', []))
            ->map(function (array $module, string $moduleKey) {
                return [
                    'module_key' => $moduleKey,
                    'module_label' => $module['label'],
                    'rows' => collect($module['entities'] ?? [])
                        ->map(function (array $entity, string $entityKey) use ($moduleKey) {
                            return [
                                'row_key' => "{$moduleKey}.{$entityKey}",
                                'entity_label' => $entity['label'],
                                'cells' => $this->cellsForEntity($entity),
                                'extra' => $entity['extra'] ?? [],
                            ];
                        })
                        ->values()
                        ->all(),
                ];
            })
            ->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function groupedModules(?array $assigned = null): Collection
    {
        $assigned = $assigned !== null ? collect($assigned) : null;

        return collect(config('permission_catalog.modules', []))
            ->map(function (array $module, string $moduleKey) use ($assigned) {
                $entities = collect($module['entities'] ?? [])
                    ->map(function (array $entity) use ($assigned) {
                        $items = collect($entity['permissions'] ?? [])
                            ->map(fn (string $permission, string $column) => [
                                'column' => $column,
                                'label' => config("permission_catalog.columns.{$column}.label", ucfirst($column)),
                                'permission' => $permission,
                                'checked' => $assigned?->contains($permission) ?? false,
                            ])
                            ->values()
                            ->all();

                        $extras = collect($entity['extra'] ?? [])
                            ->map(fn (array $extra) => [
                                'label' => $extra['label'],
                                'permission' => $extra['permission'],
                                'checked' => $assigned?->contains($extra['permission']) ?? false,
                            ])
                            ->values()
                            ->all();

                        return [
                            'entity_label' => $entity['label'],
                            'items' => $items,
                            'extra' => $extras,
                        ];
                    })
                    ->filter(fn (array $entity) => $entity['items'] !== [] || $entity['extra'] !== [])
                    ->values()
                    ->all();

                return [
                    'module_key' => $moduleKey,
                    'module_label' => $module['label'],
                    'entities' => $entities,
                ];
            })
            ->filter(fn (array $module) => $module['entities'] !== [])
            ->values();
    }

    /**
     * @return list<string>
     */
    public function allCatalogPermissions(): array
    {
        $permissions = [];

        foreach (config('permission_catalog.modules', []) as $module) {
            foreach ($module['entities'] ?? [] as $entity) {
                foreach ($entity['permissions'] ?? [] as $permission) {
                    $permissions[] = $permission;
                }
                foreach ($entity['extra'] ?? [] as $extra) {
                    $permissions[] = $extra['permission'];
                }
            }
        }

        return array_values(array_unique($permissions));
    }

    /**
     * @param  list<string>  $assigned
     * @return array<string, bool>
     */
    public function matrixState(array $assigned): array
    {
        $state = [];

        foreach ($this->matrixSections() as $section) {
            foreach ($section['rows'] as $row) {
                foreach ($row['cells'] as $column => $cell) {
                    if ($cell['permission']) {
                        $state[$cell['permission']] = in_array($cell['permission'], $assigned, true);
                    }
                }
                foreach ($row['extra'] as $extra) {
                    $state[$extra['permission']] = in_array($extra['permission'], $assigned, true);
                }
            }
        }

        return $state;
    }

    /**
     * @param  list<string>  $assigned
     * @return list<string>
     */
    public function uncataloguedAssigned(array $assigned): array
    {
        return array_values(array_diff($assigned, $this->allCatalogPermissions()));
    }

    /**
     * @return list<array{key: string, label: string, type: string}>
     */
    public function allFilterColumns(): array
    {
        return collect(config('permission_catalog.columns', []))
            ->map(fn (array $meta, string $key) => [
                'key' => $key,
                'label' => $meta['label'],
                'type' => 'standard',
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{key: string, label: string, type: string}>
     */
    public function columnsForModule(string $moduleKey): array
    {
        $module = config("permission_catalog.modules.{$moduleKey}", []);
        $standardKeys = [];
        $extraColumns = [];

        foreach ($module['entities'] ?? [] as $entity) {
            foreach ($entity['permissions'] ?? [] as $columnKey => $permission) {
                $standardKeys[$columnKey] = true;
            }

            foreach ($entity['extra'] ?? [] as $extra) {
                $extraColumns[$extra['permission']] = $extra['label'];
            }
        }

        $columns = [];

        foreach (config('permission_catalog.columns', []) as $columnKey => $meta) {
            if (isset($standardKeys[$columnKey])) {
                $columns[] = [
                    'key' => $columnKey,
                    'label' => $meta['label'],
                    'type' => 'standard',
                ];
            }
        }

        foreach ($extraColumns as $permission => $label) {
            $columns[] = [
                'key' => $permission,
                'label' => $label,
                'type' => 'extra',
            ];
        }

        return $columns;
    }

    /**
     * @param  list<string>  $assigned
     * @return array{modules_enabled: int, permissions_enabled: int}
     */
    public function roleSummaryStats(array $assigned): array
    {
        $state = $this->matrixState($assigned);
        $modulesEnabled = 0;

        foreach ($this->matrixSections() as $section) {
            $moduleHasPermission = false;

            foreach ($section['rows'] as $row) {
                foreach ($row['cells'] as $cell) {
                    if ($cell['permission'] && ($state[$cell['permission']] ?? false)) {
                        $moduleHasPermission = true;
                        break 2;
                    }
                }

                foreach ($row['extra'] as $extra) {
                    if ($state[$extra['permission']] ?? false) {
                        $moduleHasPermission = true;
                        break 2;
                    }
                }
            }

            if ($moduleHasPermission) {
                $modulesEnabled++;
            }
        }

        return [
            'modules_enabled' => $modulesEnabled,
            'permissions_enabled' => count(array_filter($state)) + count($this->uncataloguedAssigned($assigned)),
        ];
    }

    /**
     * @param  list<string>  $assigned
     * @return list<array{key: string, label: string, enabled: bool}>
     */
    public function moduleCoverage(array $assigned): array
    {
        $state = $this->matrixState($assigned);

        return $this->matrixSections()
            ->map(function (array $section) use ($state) {
                $enabled = false;

                foreach ($section['rows'] as $row) {
                    foreach ($row['cells'] as $cell) {
                        if ($cell['permission'] && ($state[$cell['permission']] ?? false)) {
                            $enabled = true;
                            break 2;
                        }
                    }

                    foreach ($row['extra'] as $extra) {
                        if ($state[$extra['permission']] ?? false) {
                            $enabled = true;
                            break 2;
                        }
                    }
                }

                return [
                    'key' => $section['module_key'],
                    'label' => __($section['module_label']),
                    'enabled' => $enabled,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  list<string>  $assigned
     * @return list<string>
     */
    public function enabledModuleLabels(array $assigned): array
    {
        return collect($this->moduleCoverage($assigned))
            ->filter(fn (array $module) => $module['enabled'])
            ->pluck('label')
            ->values()
            ->all();
    }

    /**
     * @param  list<string>  $assigned
     * @return array{
     *     modules: list<array<string, mixed>>,
     *     allColumns: list<array<string, string>>,
     *     granted: list<string>,
     *     uncatalogued: list<string>
     * }
     */
    public function workspacePayload(array $assigned): array
    {
        $granted = array_values(array_keys(array_filter($this->matrixState($assigned))));

        $modules = $this->matrixSections()
            ->map(function (array $section) {
                $columns = collect($this->columnsForModule($section['module_key']))
                    ->map(fn (array $column) => [
                        'key' => $column['key'],
                        'label' => __($column['label']),
                        'type' => $column['type'],
                    ])
                    ->values()
                    ->all();

                return [
                    'key' => $section['module_key'],
                    'label' => __($section['module_label']),
                    'columns' => $columns,
                    'rows' => collect($section['rows'])
                        ->map(function (array $row) use ($columns) {
                            $cells = [];

                            foreach ($columns as $column) {
                                $cells[$column['key']] = $this->permissionForColumn($row, $column);
                            }

                            return [
                                'key' => $row['row_key'],
                                'label' => __($row['entity_label']),
                                'cells' => $cells,
                            ];
                        })
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();

        return [
            'modules' => $modules,
            'allColumns' => collect($this->allFilterColumns())
                ->map(fn (array $column) => [
                    'key' => $column['key'],
                    'label' => __($column['label']),
                    'type' => $column['type'],
                ])
                ->values()
                ->all(),
            'granted' => $granted,
            'uncatalogued' => $this->uncataloguedAssigned($assigned),
        ];
    }

    public function permissionExists(string $name): bool
    {
        return Permission::query()->where('name', $name)->where('guard_name', 'web')->exists();
    }

    /**
     * @param  array<string, mixed>  $entity
     * @return array<string, array{label: string, permission: string|null}>
     */
    protected function cellsForEntity(array $entity): array
    {
        $cells = [];

        foreach (config('permission_catalog.columns', []) as $column => $meta) {
            $cells[$column] = [
                'label' => $meta['label'],
                'permission' => $entity['permissions'][$column] ?? null,
            ];
        }

        return $cells;
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array{key: string, label: string, type: string}  $column
     */
    protected function permissionForColumn(array $row, array $column): ?string
    {
        if ($column['type'] === 'standard') {
            return $row['cells'][$column['key']]['permission'] ?? null;
        }

        foreach ($row['extra'] as $extra) {
            if ($extra['permission'] === $column['key']) {
                return $extra['permission'];
            }
        }

        return null;
    }
}
