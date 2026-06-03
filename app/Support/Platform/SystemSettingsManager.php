<?php

namespace App\Support\Platform;

use App\Models\Platform\SystemSetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SystemSettingsManager
{
    public function __construct(
        protected SettingsRegistry $registry,
        protected SystemSettingsService $settings,
    ) {}

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function rowsForSection(string $sectionSlug, int $companyId, ?int $branchId): Collection
    {
        $section = $this->registry->section($sectionSlug);
        $stored = $this->storedValues($companyId, $branchId, array_keys($section['settings']));

        return collect($section['settings'])->map(function (array $definition, string $key) use ($stored, $companyId, $branchId) {
            $companyValue = $stored['company'][$key] ?? null;
            $branchValue = $branchId ? ($stored['branch'][$key] ?? null) : null;
            $effective = $this->settings->get($key, $definition['default'] ?? null, $companyId, $branchId);

            return [
                'key' => $key,
                'label' => $definition['label'],
                'description' => $definition['description'] ?? null,
                'type' => $definition['type'] ?? 'string',
                'scopes' => $definition['scopes'] ?? ['company'],
                'default' => $definition['default'] ?? null,
                'company_value' => $companyValue,
                'branch_value' => $branchValue,
                'effective_value' => $effective,
                'has_company_override' => array_key_exists($key, $stored['company']),
                'has_branch_override' => $branchId && array_key_exists($key, $stored['branch']),
            ];
        })->values();
    }

    /**
     * @param  array<string, array{company?: mixed, branch?: mixed}>  $payload
     */
    public function saveSection(string $sectionSlug, array $payload, int $companyId, ?int $branchId): void
    {
        $section = $this->registry->section($sectionSlug);

        foreach ($section['settings'] as $key => $definition) {
            $values = $payload[$key] ?? [];
            $type = $definition['type'] ?? 'string';

            if ($this->registry->supportsScope($definition, 'company') && array_key_exists('company', $values)) {
                $this->persistScopedValue($key, $values['company'], $companyId, null, $type);
            }

            if ($branchId && $this->registry->supportsScope($definition, 'branch') && array_key_exists('branch', $values)) {
                $this->persistScopedValue($key, $values['branch'], $companyId, $branchId, $type);
            }
        }
    }

    protected function persistScopedValue(
        string $key,
        mixed $rawValue,
        int $companyId,
        ?int $branchId,
        string $type,
    ): void {
        if ($this->isEmptyOverride($rawValue, $type)) {
            $this->deleteScoped($key, $companyId, $branchId);

            return;
        }

        $this->settings->set(
            $key,
            $this->castValue($rawValue, $type),
            $companyId,
            $branchId,
            $type,
        );
    }

    protected function isEmptyOverride(mixed $value, string $type): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        if ($type === 'boolean' && $value === 'inherit') {
            return true;
        }

        return false;
    }

    protected function castValue(mixed $value, string $type): mixed
    {
        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'float' => (float) $value,
            default => (string) $value,
        };
    }

    protected function deleteScoped(string $key, int $companyId, ?int $branchId): void
    {
        SystemSetting::query()
            ->where('key', $key)
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->delete();

        Cache::forget($this->cacheKey($key, $companyId, $branchId));

        if ($branchId !== null) {
            Cache::forget($this->cacheKey($key, $companyId, null));
        }
    }

    /**
     * @param  list<string>  $keys
     * @return array{company: array<string, mixed>, branch: array<string, mixed>}
     */
    protected function storedValues(int $companyId, ?int $branchId, array $keys): array
    {
        $rows = SystemSetting::query()
            ->whereIn('key', $keys)
            ->where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->whereNull('branch_id');
                if ($branchId) {
                    $query->orWhere('branch_id', $branchId);
                }
            })
            ->get();

        $company = [];
        $branch = [];

        foreach ($rows as $row) {
            $value = $row->value['data'] ?? null;
            if ($row->branch_id === null) {
                $company[$row->key] = $value;
            } elseif ($branchId && (int) $row->branch_id === $branchId) {
                $branch[$row->key] = $value;
            }
        }

        return compact('company', 'branch');
    }

    protected function cacheKey(string $key, ?int $companyId, ?int $branchId): string
    {
        return sprintf(
            'platform:settings:%s:%s:%s',
            $companyId ?? 'global',
            $branchId ?? 'all',
            $key,
        );
    }
}
