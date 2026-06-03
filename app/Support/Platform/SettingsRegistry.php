<?php

namespace App\Support\Platform;

use InvalidArgumentException;

class SettingsRegistry
{
    /**
     * @return array<string, array{label: string, description: string|null, settings: array<string, array<string, mixed>>}>
     */
    public function sections(): array
    {
        return config('settings_registry.sections', []);
    }

    /**
     * @return list<string>
     */
    public function sectionSlugs(): array
    {
        return array_keys($this->sections());
    }

    public function hasSection(string $slug): bool
    {
        return array_key_exists($slug, $this->sections());
    }

    /**
     * @return array{label: string, description: string|null, settings: array<string, array<string, mixed>>}
     */
    public function section(string $slug): array
    {
        if (! $this->hasSection($slug)) {
            throw new InvalidArgumentException("Unknown settings section [{$slug}].");
        }

        return $this->sections()[$slug];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function definition(string $sectionSlug, string $key): ?array
    {
        return $this->section($sectionSlug)['settings'][$key] ?? null;
    }

    public function supportsScope(array $definition, string $scope): bool
    {
        return in_array($scope, $definition['scopes'] ?? [], true);
    }
}
