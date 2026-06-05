<?php

namespace App\Support\Assets;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class AssetSchema
{
    public static function hasTable(string $table): bool
    {
        return Schema::hasTable($table);
    }

    public static function hasColumn(string $table, string $column): bool
    {
        return static::hasTable($table) && Schema::hasColumn($table, $column);
    }

    public static function supportsProcurementAssets(): bool
    {
        return static::hasColumn('fixed_assets', 'acquisition_source')
            && static::hasColumn('fixed_assets', 'capitalization_date');
    }

    public static function count(string $table, callable $callback): int
    {
        if (! static::hasTable($table)) {
            return 0;
        }

        return (int) $callback();
    }

    public static function sum(string $table, callable $callback): float
    {
        if (! static::hasTable($table)) {
            return 0.0;
        }

        return (float) $callback();
    }

    /**
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    public static function whenReady(string $table, callable $callback, mixed $default = null): mixed
    {
        if (! static::hasTable($table)) {
            return $default;
        }

        return $callback();
    }

    /**
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    public static function whenColumnReady(string $table, string $column, callable $callback, mixed $default = null): mixed
    {
        if (! static::hasColumn($table, $column)) {
            return $default;
        }

        return $callback();
    }

    /**
     * @template T
     *
     * @param  callable(): Collection<int, mixed>  $callback
     * @return Collection<int, mixed>
     */
    public static function collect(string $table, callable $callback): Collection
    {
        /** @var Collection<int, mixed> $empty */
        $empty = collect();

        if (! static::hasTable($table)) {
            return $empty;
        }

        return $callback();
    }
}
