<?php

namespace App\Support\PublicHash;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class PublicHashResolver
{
    public function __construct(
        protected PublicHashGenerator $generator,
    ) {}

    /**
     * @param  class-string<Model>  $modelClass
     */
    public function resolve(string $modelClass, mixed $value, ?string $field = null): Model
    {
        return $this->resolveValue($modelClass, $value, $field, scoped: true);
    }

    /**
     * Resolve a route key for externally reachable surfaces (signed public links)
     * where tenant context may not be established.
     *
     * @param  class-string<Model>  $modelClass
     */
    public function resolveForExternal(string $modelClass, mixed $value, ?string $field = null): Model
    {
        return $this->resolveValue($modelClass, $value, $field, scoped: false);
    }

    /**
     * External resolution with optional legacy numeric fallback (signed public receipt TTL window).
     *
     * @param  class-string<Model>  $modelClass
     */
    public function resolveForExternalWithLegacyNumeric(
        string $modelClass,
        mixed $value,
        ?string $field = null,
        bool $allowLegacyNumeric = false,
    ): Model {
        if (! is_string($value) || $value === '') {
            throw (new ModelNotFoundException)->setModel($modelClass);
        }

        $field ??= (string) config('public_hashes.column', 'public_id');

        if ($this->generator->isValid($value)) {
            return $this->findByPublicHashUnscoped($modelClass, $value, $field);
        }

        if ($allowLegacyNumeric && ctype_digit($value)) {
            $this->logNumericFallback($modelClass, $value);

            return $this->findByNumericIdUnscoped($modelClass, $value);
        }

        throw (new ModelNotFoundException)->setModel($modelClass, [$field => $value]);
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    protected function resolveValue(
        string $modelClass,
        mixed $value,
        ?string $field,
        bool $scoped,
    ): Model {
        if (! is_string($value) || $value === '') {
            throw (new ModelNotFoundException)->setModel($modelClass);
        }

        $field ??= (string) config('public_hashes.column', 'public_id');

        if ($this->generator->isValid($value)) {
            return $scoped
                ? $this->findByPublicHash($modelClass, $value, $field)
                : $this->findByPublicHashUnscoped($modelClass, $value, $field);
        }

        if ($this->numericFallbackEnabled() && ctype_digit($value)) {
            $this->logNumericFallback($modelClass, $value);

            return $scoped
                ? $this->findByNumericId($modelClass, $value)
                : $this->findByNumericIdUnscoped($modelClass, $value);
        }

        throw (new ModelNotFoundException)->setModel($modelClass, [$field => $value]);
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    public function findByPublicHash(string $modelClass, string $hash, ?string $field = null): Model
    {
        $field ??= (string) config('public_hashes.column', 'public_id');
        $this->generator->assertValid($hash);

        $model = $this->scopedQuery($modelClass)
            ->where($field, $hash)
            ->first();

        if ($model === null) {
            throw (new ModelNotFoundException)->setModel($modelClass, [$field => $hash]);
        }

        return $model;
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    public function findByPublicHashUnscoped(string $modelClass, string $hash, ?string $field = null): Model
    {
        $field ??= (string) config('public_hashes.column', 'public_id');
        $this->generator->assertValid($hash);

        $model = $modelClass::query()
            ->where($field, $hash)
            ->first();

        if ($model === null) {
            throw (new ModelNotFoundException)->setModel($modelClass, [$field => $hash]);
        }

        return $model;
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    public function findByNumericId(string $modelClass, string|int $id): Model
    {
        $model = $this->scopedQuery($modelClass)
            ->whereKey($id)
            ->first();

        if ($model === null) {
            throw (new ModelNotFoundException)->setModel($modelClass, [(new $modelClass)->getKeyName() => $id]);
        }

        return $model;
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    public function findByNumericIdUnscoped(string $modelClass, string|int $id): Model
    {
        $model = $modelClass::query()
            ->whereKey($id)
            ->first();

        if ($model === null) {
            throw (new ModelNotFoundException)->setModel($modelClass, [(new $modelClass)->getKeyName() => $id]);
        }

        return $model;
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    protected function scopedQuery(string $modelClass): Builder
    {
        /** @var Builder $query */
        $query = $modelClass::query();

        if ($this->supportsTenantScope($modelClass)) {
            $query->forTenant();
        }

        return $query;
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    protected function supportsTenantScope(string $modelClass): bool
    {
        return method_exists($modelClass, 'scopeForTenant');
    }

    protected function numericFallbackEnabled(): bool
    {
        return (bool) config('public_hashes.numeric_fallback_enabled', false);
    }

    protected function signedReceiptLegacyNumericEnabled(): bool
    {
        return (bool) config('public_hashes.signed_receipt_legacy_numeric_enabled', true);
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    protected function logNumericFallback(string $modelClass, string $value): void
    {
        if (! config('public_hashes.fallback_log_enabled', true)) {
            return;
        }

        $user = auth()->user();

        Log::info('public_hash.numeric_fallback', [
            'route' => request()->route()?->getName(),
            'model' => $modelClass,
            'numeric_id' => $value,
            'user_id' => $user?->id,
            'client_id' => $user?->customer_id,
            'ip' => request()->ip(),
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
