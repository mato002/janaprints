<?php

namespace App\Support\Platform;

use Closure;
use Illuminate\Support\Facades\Cache;

class PlatformCacheService
{
    public function remember(string $category, string $key, Closure $callback, ?int $ttl = null): mixed
    {
        $ttl ??= config("platform.cache.{$category}", 300);

        return Cache::remember($this->key($category, $key), $ttl, $callback);
    }

    public function forget(string $category, string $key): void
    {
        Cache::forget($this->key($category, $key));
    }

    public function forgetCategory(string $category): void
    {
        // Tag support requires redis/memcached; callers should forget explicit keys for now.
    }

    protected function key(string $category, string $key): string
    {
        return "platform:{$category}:{$key}";
    }
}
