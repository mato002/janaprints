<?php

namespace App\Support\Platform;

use App\Models\Platform\SystemSetting;
use Illuminate\Support\Facades\Cache;

class SystemSettingsService
{
    public function get(string $key, mixed $default = null, ?int $companyId = null, ?int $branchId = null): mixed
    {
        $companyId ??= tenant()->companyId();
        $branchId ??= tenant()->branchId();

        $cacheKey = $this->cacheKey($key, $companyId, $branchId);

        return Cache::remember(
            $cacheKey,
            config('platform.cache.system_settings', 900),
            fn () => $this->resolve($key, $default, $companyId, $branchId),
        );
    }

    public function set(
        string $key,
        mixed $value,
        ?int $companyId = null,
        ?int $branchId = null,
        string $valueType = 'string',
    ): SystemSetting {
        $companyId ??= tenant()->companyId();

        $setting = SystemSetting::query()->updateOrCreate(
            [
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'key' => $key,
            ],
            [
                'value' => ['data' => $value],
                'value_type' => $valueType,
            ],
        );

        Cache::forget($this->cacheKey($key, $companyId, $branchId));

        return $setting;
    }

    protected function resolve(string $key, mixed $default, ?int $companyId, ?int $branchId): mixed
    {
        if ($branchId !== null) {
            $branchValue = $this->readScoped($key, $companyId, $branchId);
            if ($branchValue !== null) {
                return $branchValue;
            }
        }

        if ($companyId !== null) {
            $companyValue = $this->readScoped($key, $companyId, null);
            if ($companyValue !== null) {
                return $companyValue;
            }
        }

        $globalValue = $this->readScoped($key, null, null);

        return $globalValue ?? $default;
    }

    protected function readScoped(string $key, ?int $companyId, ?int $branchId): mixed
    {
        $setting = SystemSetting::query()
            ->where('key', $key)
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->first();

        if (! $setting) {
            return null;
        }

        return $setting->value['data'] ?? null;
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
