<?php

namespace App\Services\Website;

use App\Models\WebsiteSetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class WebsiteSettingsService
{
    public function tableExists(): bool
    {
        return Schema::hasTable('website_settings');
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember($this->cacheKey($key), 3600, function () use ($key, $default) {
            return $this->resolveUncached($key, $default);
        });
    }

    public function clearCache(): void
    {
        foreach (array_keys(config('website_cms.settings', [])) as $key) {
            Cache::forget($this->cacheKey($key));
        }
    }

    public function clearValue(string $key): void
    {
        if (! $this->tableExists()) {
            return;
        }

        WebsiteSetting::query()->where('key', $key)->update([
            'value' => null,
            'updated_by' => auth()->id(),
        ]);

        Cache::forget($this->cacheKey($key));
    }

    /**
     * @return Collection<int, WebsiteSetting>
     */
    public function syncRegistryForGroups(array $groups): Collection
    {
        if (! $this->tableExists()) {
            return collect();
        }

        $schema = config('website_cms.settings', []);

        foreach ($schema as $key => $meta) {
            if (! in_array($meta['group'], $groups, true)) {
                continue;
            }

            $fallback = $this->serializeConfigFallback($key, $meta['type']);

            WebsiteSetting::query()->firstOrCreate(
                ['key' => $key],
                [
                    'group' => $meta['group'],
                    'type' => $meta['type'],
                    'fallback_value' => $fallback,
                    'is_active' => true,
                ],
            );
        }

        return WebsiteSetting::query()
            ->whereIn('group', $groups)
            ->orderBy('key')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public function updateGroupSettings(array $groups, array $values, ?int $userId = null): void
    {
        $schema = collect(config('website_cms.settings', []))
            ->filter(fn (array $meta) => in_array($meta['group'], $groups, true));

        foreach ($schema as $key => $meta) {
            if (! array_key_exists($key, $values)) {
                continue;
            }

            $raw = $values[$key];
            $this->validateValue($raw, $meta, $key);

            $stored = $meta['type'] === 'json'
                ? json_encode($raw, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE)
                : ($meta['type'] === 'boolean'
                    ? ($raw ? '1' : '0')
                    : (string) $raw);

            $setting = WebsiteSetting::query()->firstOrNew(['key' => $key]);
            $setting->fill([
                'group' => $meta['group'],
                'type' => $meta['type'],
                'value' => $stored,
                'is_active' => true,
                'updated_by' => $userId,
            ]);

            if (! $setting->exists) {
                $setting->fallback_value = $this->serializeConfigFallback($key, $meta['type']);
                $setting->created_by = $userId;
            }

            $setting->save();
        }

        $this->clearCache();
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function schemaForGroups(array $groups, ?string $adminPage = null): array
    {
        return collect(config('website_cms.settings', []))
            ->filter(function (array $meta) use ($groups, $adminPage) {
                if (! in_array($meta['group'], $groups, true)) {
                    return false;
                }

                if ($adminPage === null) {
                    return true;
                }

                $pages = $meta['admin_pages'] ?? ['footer-contact', 'seo-global'];

                return in_array($adminPage, $pages, true);
            })
            ->all();
    }

    protected function resolveUncached(string $key, mixed $default = null): mixed
    {
        $setting = $this->findActiveSetting($key);

        if ($setting && $this->hasStoredValue($setting->value)) {
            return $this->castValue($setting->value, $setting->type);
        }

        if ($setting && $this->hasStoredValue($setting->fallback_value)) {
            return $this->castValue($setting->fallback_value, $setting->type);
        }

        $configFallback = $this->configFallback($key);

        if ($configFallback !== null) {
            return $configFallback;
        }

        return $default;
    }

    protected function findActiveSetting(string $key): ?WebsiteSetting
    {
        if (! $this->tableExists()) {
            return null;
        }

        return WebsiteSetting::query()
            ->where('key', $key)
            ->where('is_active', true)
            ->first();
    }

    protected function hasStoredValue(mixed $value): bool
    {
        return $value !== null && $value !== '';
    }

    protected function configFallback(string $key): mixed
    {
        $meta = $this->settingSchema($key);

        if (! $meta || empty($meta['fallback_config'])) {
            return null;
        }

        $value = config($meta['fallback_config']);

        if ($meta['type'] === 'json' && is_array($value)) {
            return $value;
        }

        if ($meta['type'] === 'boolean') {
            return $this->castValue(
                is_bool($value) ? ($value ? '1' : '0') : (string) $value,
                'boolean',
            );
        }

        return $value;
    }

    protected function serializeConfigFallback(string $key, string $type): ?string
    {
        $value = $this->configFallback($key);

        if ($value === null) {
            $meta = $this->settingSchema($key);

            if ($meta && $meta['type'] === 'boolean') {
                return '1';
            }

            return null;
        }

        if ($type === 'json') {
            return json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        }

        if ($type === 'boolean') {
            return $value ? '1' : '0';
        }

        return (string) $value;
    }

    protected function castValue(?string $value, string $type): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($type === 'json') {
            return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        }

        if ($type === 'boolean') {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }

        return $value;
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    protected function validateValue(mixed $value, array $meta, string $key): void
    {
        $optional = (bool) ($meta['optional'] ?? false);

        match ($meta['type']) {
            'json' => $this->assertValidJsonValue($value, $key),
            'url' => validator(
                ['value' => $value],
                ['value' => $optional ? ['nullable', 'url', 'max:2000'] : ['required', 'url', 'max:2000']],
            )->validate(),
            'email' => validator(
                ['value' => $value],
                ['value' => $optional ? ['nullable', 'email', 'max:255'] : ['required', 'email', 'max:255']],
            )->validate(),
            'phone' => validator(
                ['value' => $value],
                ['value' => $optional ? ['nullable', 'string', 'max:30'] : ['required', 'string', 'max:30']],
            )->validate(),
            'boolean' => validator(['value' => $value], ['value' => ['boolean']])->validate(),
            default => validator(
                ['value' => $value],
                ['value' => $optional ? ['nullable', 'string', 'max:5000'] : ['required', 'string', 'max:5000']],
            )->validate(),
        };
    }

    protected function assertValidJsonValue(mixed $value, string $key): void
    {
        if (is_array($value)) {
            return;
        }

        if (! is_string($value)) {
            throw new \InvalidArgumentException("Setting [{$key}] expects valid JSON.");
        }

        json_decode($value, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function settingSchema(string $key): ?array
    {
        $settings = config('website_cms.settings', []);

        return $settings[$key] ?? null;
    }

    protected function cacheKey(string $key): string
    {
        return 'website_settings.'.md5($key);
    }
}
