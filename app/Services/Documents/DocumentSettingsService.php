<?php

namespace App\Services\Documents;

use App\Models\DocumentSetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class DocumentSettingsService
{
    public function tableExists(): bool
    {
        return Schema::hasTable('document_settings');
    }

    public function get(string $key, mixed $default = null, ?int $companyId = null): mixed
    {
        $companyId ??= tenant()->companyId();

        if ($companyId === null) {
            return $this->configFallback($key) ?? $default;
        }

        return Cache::remember(
            $this->cacheKey($key, $companyId),
            config('platform.cache.document_settings', 900),
            fn () => $this->resolveUncached($key, $default, $companyId),
        );
    }

    /**
     * @return array{name: ?string, address: ?string, phone: ?string, email: ?string, website: ?string}
     */
    public function company(?int $companyId = null): array
    {
        return [
            'name' => $this->get('company.name', null, $companyId),
            'address' => $this->get('company.address', null, $companyId),
            'phone' => $this->get('company.phone', null, $companyId),
            'email' => $this->get('company.email', null, $companyId),
            'website' => $this->get('company.website', null, $companyId),
        ];
    }

    /**
     * @return array<string, ?string>
     */
    public function payment(?int $companyId = null): array
    {
        return [
            'mpesa_paybill' => $this->get('payment.mpesa_paybill', null, $companyId),
            'mpesa_account' => $this->get('payment.mpesa_account', null, $companyId),
            'cheque_payable_to' => $this->get('payment.cheque_payable_to', null, $companyId),
            'bank_name' => $this->get('payment.bank_name', null, $companyId),
            'bank_branch' => $this->get('payment.bank_branch', null, $companyId),
            'bank_account_name' => $this->get('payment.bank_account_name', null, $companyId),
            'bank_account' => $this->get('payment.bank_account', null, $companyId),
        ];
    }

    public function term(string $type, ?int $companyId = null): ?string
    {
        $value = $this->get("terms.{$type}", null, $companyId);

        return filled($value) ? (string) $value : null;
    }

    public function footerThanks(?int $companyId = null): string
    {
        return (string) ($this->get('footer.thanks', config('document_cms.defaults.footer.thanks'), $companyId)
            ?? config('document_cms.defaults.footer.thanks'));
    }

    public function footerSystem(?int $companyId = null): string
    {
        return (string) ($this->get('footer.system', config('document_cms.defaults.footer.system'), $companyId)
            ?? config('document_cms.defaults.footer.system'));
    }

    public function taxLabel(?int $companyId = null): string
    {
        return (string) ($this->get('labels.tax', config('document_cms.defaults.labels.tax'), $companyId)
            ?? config('document_cms.defaults.labels.tax'));
    }

    public function clearCache(?int $companyId = null): void
    {
        $companyId ??= tenant()->companyId();

        if ($companyId === null) {
            return;
        }

        foreach (array_keys(config('document_cms.settings', [])) as $key) {
            Cache::forget($this->cacheKey($key, $companyId));
        }
    }

    public function clearValue(string $key, ?int $companyId = null): void
    {
        $companyId ??= tenant()->companyId();

        if (! $this->tableExists() || $companyId === null) {
            return;
        }

        DocumentSetting::query()
            ->where('company_id', $companyId)
            ->where('key', $key)
            ->update([
                'value' => null,
                'updated_by' => auth()->id(),
            ]);

        Cache::forget($this->cacheKey($key, $companyId));
    }

    /**
     * @return Collection<int, DocumentSetting>
     */
    public function syncRegistryForCompany(int $companyId, array $groups = []): Collection
    {
        if (! $this->tableExists()) {
            return collect();
        }

        $schema = config('document_cms.settings', []);

        foreach ($schema as $key => $meta) {
            if ($groups !== [] && ! in_array($meta['group'], $groups, true)) {
                continue;
            }

            $fallback = $this->serializeConfigFallback($key, $meta['type']);

            DocumentSetting::query()->firstOrCreate(
                ['company_id' => $companyId, 'key' => $key],
                [
                    'group' => $meta['group'],
                    'type' => $meta['type'],
                    'fallback_value' => $fallback,
                    'is_active' => true,
                ],
            );
        }

        $query = DocumentSetting::query()->where('company_id', $companyId);

        if ($groups !== []) {
            $query->whereIn('group', $groups);
        }

        return $query->orderBy('key')->get();
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public function updateSettings(array $values, int $companyId, ?int $userId = null): void
    {
        $schema = config('document_cms.settings', []);

        foreach ($schema as $key => $meta) {
            if (! array_key_exists($key, $values)) {
                continue;
            }

            $raw = $values[$key];
            $this->validateValue($raw, $meta, $key);

            $stored = $meta['type'] === 'boolean'
                ? ($raw ? '1' : '0')
                : (string) $raw;

            $setting = DocumentSetting::query()->firstOrNew([
                'company_id' => $companyId,
                'key' => $key,
            ]);

            $setting->fill([
                'group' => $meta['group'],
                'type' => $meta['type'],
                'value' => $stored === '' ? null : $stored,
                'is_active' => true,
                'updated_by' => $userId,
            ]);

            if (! $setting->exists) {
                $setting->fallback_value = $this->serializeConfigFallback($key, $meta['type']);
                $setting->created_by = $userId;
            }

            $setting->save();
        }

        $this->clearCache($companyId);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function schema(): array
    {
        return config('document_cms.settings', []);
    }

    protected function resolveUncached(string $key, mixed $default, int $companyId): mixed
    {
        $setting = $this->findActiveSetting($key, $companyId);

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

    protected function findActiveSetting(string $key, int $companyId): ?DocumentSetting
    {
        if (! $this->tableExists()) {
            return null;
        }

        return DocumentSetting::query()
            ->where('company_id', $companyId)
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

        return config($meta['fallback_config']);
    }

    protected function serializeConfigFallback(string $key, string $type): ?string
    {
        $value = $this->configFallback($key);

        if ($value === null || $value === '') {
            return null;
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

        if ($optional && ($value === null || $value === '')) {
            return;
        }

        match ($meta['type']) {
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

    /**
     * @return array<string, mixed>|null
     */
    protected function settingSchema(string $key): ?array
    {
        return config('document_cms.settings')[$key] ?? null;
    }

    protected function cacheKey(string $key, int $companyId): string
    {
        return sprintf('document_settings:%d:%s', $companyId, md5($key));
    }
}
