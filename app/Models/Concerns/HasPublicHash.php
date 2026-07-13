<?php

namespace App\Models\Concerns;

use App\Support\PublicHash\PublicHashGenerator;
use App\Support\PublicHash\PublicHashResolver;
use App\Support\PublicHash\PublicHashValidationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

trait HasPublicHash
{
    public static function bootHasPublicHash(): void
    {
        static::creating(function (Model $model): void {
            $column = $model->publicHashColumn();

            if (! empty($model->{$column})) {
                app(PublicHashGenerator::class)->assertValid((string) $model->{$column});

                return;
            }

            $model->{$column} = app(PublicHashGenerator::class)->generateUnique(static::class, $column);
        });

        static::updating(function (Model $model): void {
            $column = $model->publicHashColumn();

            if ($model->isDirty($column) && filled($model->getOriginal($column))) {
                throw new PublicHashValidationException('Public hash is immutable once assigned.');
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return $this->publicHashColumn();
    }

    /**
     * Ensure route generation still works when a partial SELECT omitted public_id.
     * Prefer including public_id in the query; this is a safe fallback to avoid Turbo frame 500 loops.
     */
    public function getRouteKey(): mixed
    {
        $column = $this->getRouteKeyName();
        $value = $this->getAttribute($column);

        if (filled($value)) {
            return $value;
        }

        if ($this->exists && $this->getKey() !== null && ! array_key_exists($column, $this->attributes)) {
            $fetched = $this->newQueryWithoutScopes()
                ->where($this->getKeyName(), $this->getKey())
                ->value($column);

            if (filled($fetched)) {
                $this->setAttribute($column, $fetched);

                return $fetched;
            }
        }

        return $value;
    }

    public function scopeWherePublicHash(Builder $query, string $hash): Builder
    {
        return $query->where($this->publicHashColumn(), $hash);
    }

    public static function findByPublicHash(string $hash): ?static
    {
        try {
            return app(PublicHashResolver::class)->findByPublicHash(static::class, $hash);
        } catch (ModelNotFoundException) {
            return null;
        }
    }

    public function resolveRouteBinding($value, $field = null): Model
    {
        return app(PublicHashResolver::class)->resolve(static::class, $value, $field);
    }

    protected function publicHashColumn(): string
    {
        return (string) config('public_hashes.column', 'public_id');
    }
}
