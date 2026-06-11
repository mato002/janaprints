<?php

namespace App\Services\Website;

use App\Models\WebsiteMediaItem;
use App\Support\Website\WebsiteMediaRegistry;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class WebsiteMediaResolver
{
    public function __construct(
        protected WebsiteMediaService $media,
        protected WebsiteMediaRegistry $registry,
    ) {}

    public function resolvePath(string $slotKey): string
    {
        return Cache::remember($this->cacheKey('path', $slotKey), 3600, function () use ($slotKey) {
            return $this->resolvePathUncached($slotKey);
        });
    }

    public function resolveAlt(string $slotKey): string
    {
        return Cache::remember($this->cacheKey('alt', $slotKey), 3600, function () use ($slotKey) {
            return $this->resolveAltUncached($slotKey);
        });
    }

    public function resolveSource(string $source, ?string $fallbackKey = 'default'): string
    {
        $source = trim($source);

        if ($source === '') {
            return $this->resolvePath($fallbackKey ?? 'default');
        }

        $publicImages = config('public-images', []);

        if (isset($publicImages[$source])) {
            return $this->resolvePath($source);
        }

        $slotKey = $this->registry->slotKeyForPath($source);

        if ($slotKey) {
            return $this->resolvePath($slotKey);
        }

        if (str_starts_with($source, 'http') || str_starts_with($source, '/')) {
            return $source;
        }

        return $this->resolvePath($fallbackKey ?? 'default');
    }

    public function resolveAltForSource(string $source, string $slotKey = '', string $defaultAlt = ''): string
    {
        if ($slotKey !== '') {
            $alt = $this->resolveAltUncached($slotKey);

            if ($alt !== '' && $alt !== $slotKey) {
                return $alt;
            }
        }

        $resolvedSlot = $slotKey !== ''
            ? $slotKey
            : ($this->registry->slotKeyForPath($source) ?? (isset(config('public-images', [])[$source]) ? $source : null));

        if ($resolvedSlot) {
            return $this->resolveAltUncached($resolvedSlot);
        }

        return $defaultAlt;
    }

    public function hasUploadedImage(string $slotKey): bool
    {
        $item = $this->findActiveItem($slotKey);

        return (bool) ($item?->image_path);
    }

    public function clearCache(): void
    {
        $this->registry->clearCache();

        foreach ($this->registry->slotKeys() as $slotKey) {
            Cache::forget($this->cacheKey('path', $slotKey));
            Cache::forget($this->cacheKey('alt', $slotKey));
        }

        Cache::forget($this->cacheKey('path', 'default'));
        Cache::forget($this->cacheKey('alt', 'default'));
    }

    protected function resolvePathUncached(string $slotKey): string
    {
        $item = $this->findActiveItem($slotKey);

        if ($item?->image_path) {
            return $this->toPublicPath($item->image_path);
        }

        $configFallback = $this->configFallbackPath($slotKey, $item);

        if ($configFallback) {
            return $configFallback;
        }

        return (string) config('public-images.default', '/images/storefront/facility/production-floor.jpg');
    }

    protected function resolveAltUncached(string $slotKey): string
    {
        $item = $this->findActiveItem($slotKey);

        if ($item?->alt_text) {
            return $item->alt_text;
        }

        $meta = $this->registry->meta($slotKey);

        return (string) ($meta['alt_text'] ?? $meta['label'] ?? $slotKey);
    }

    protected function findActiveItem(string $slotKey): ?WebsiteMediaItem
    {
        if (! $this->media->tableExists()) {
            return null;
        }

        return WebsiteMediaItem::query()
            ->where('slot_key', $slotKey)
            ->where('is_active', true)
            ->first();
    }

    protected function configFallbackPath(string $slotKey, ?WebsiteMediaItem $item): ?string
    {
        if ($item?->fallback_path) {
            return $item->fallback_path;
        }

        $meta = $this->registry->meta($slotKey);

        if (! empty($meta['fallback_path'])) {
            return $meta['fallback_path'];
        }

        $publicImages = config('public-images', []);

        if (isset($publicImages[$slotKey])) {
            return $publicImages[$slotKey];
        }

        foreach ($this->registry->slots() as $key => $slotMeta) {
            if ($key === $slotKey && ! empty($slotMeta['fallback_path'])) {
                return $slotMeta['fallback_path'];
            }
        }

        $imageKey = $slotKey;

        if (isset($publicImages[$imageKey])) {
            return $publicImages[$imageKey];
        }

        return null;
    }

    protected function toPublicPath(string $path): string
    {
        if (str_starts_with($path, 'http') || str_starts_with($path, '/')) {
            return $path;
        }

        return '/storage/'.$path;
    }

    protected function cacheKey(string $type, string $slotKey): string
    {
        return 'website_media.'.$type.'.'.md5($slotKey);
    }
}
