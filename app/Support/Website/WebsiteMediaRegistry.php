<?php

namespace App\Support\Website;

use Illuminate\Support\Facades\Cache;

class WebsiteMediaRegistry
{
    protected const CACHE_KEY = 'website_media.registry.index';

    /**
     * @return array<string, array<string, mixed>>
     */
    public function slots(): array
    {
        return Cache::remember(self::CACHE_KEY, 3600, function () {
            return app(WebsiteContentBaselineBuilder::class)->mediaSlots();
        });
    }

    /**
     * @return array<string, mixed>|null
     */
    public function meta(string $slotKey): ?array
    {
        return $this->slots()[$slotKey] ?? null;
    }

    /**
     * @return list<string>
     */
    public function slotKeys(): array
    {
        return array_keys($this->slots());
    }

    public function slotKeyForPath(string $path): ?string
    {
        $normalized = $this->normalizePath($path);

        foreach ($this->slots() as $slotKey => $meta) {
            $fallback = $meta['fallback_path'] ?? null;

            if ($fallback && $this->normalizePath($fallback) === $normalized) {
                return $slotKey;
            }
        }

        return null;
    }

    public function slotKeyForPublicImageKey(string $key): ?string
    {
        if (isset($this->slots()[$key])) {
            return $key;
        }

        $publicImages = config('public-images', []);
        $path = $publicImages[$key] ?? null;

        if ($path) {
            return $this->slotKeyForPath($path) ?? $key;
        }

        return null;
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    protected function normalizePath(string $path): string
    {
        $path = trim($path);

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        return '/'.trim($path, '/');
    }
}
