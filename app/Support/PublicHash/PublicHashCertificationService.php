<?php

namespace App\Support\PublicHash;

use App\Models\Concerns\HasPublicHash;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ReflectionClass;

class PublicHashCertificationService
{
    public function __construct(
        protected PublicHashGenerator $generator,
    ) {}

    /**
     * @return array{passed: bool, checks: list<array{key: string, passed: bool, message: string}>, summary: array<string, mixed>}
     */
    public function run(): array
    {
        $checks = [];
        $models = $this->configuredModels();
        $column = (string) config('public_hashes.column', 'public_id');

        foreach ($models as $modelClass) {
            $checks = array_merge($checks, $this->certifyModel($modelClass, $column));
        }

        $checks[] = $this->checkDuplicates($models, $column);
        $checks[] = $this->checkFallbackConfig();
        $checks[] = $this->checkDeferredDocumentation();

        $passed = collect($checks)->every(fn (array $check) => $check['passed']);

        return [
            'passed' => $passed,
            'checks' => $checks,
            'summary' => [
                'model_count' => count($models),
                'check_count' => count($checks),
                'failed_count' => collect($checks)->where('passed', false)->count(),
                'numeric_fallback_enabled' => (bool) config('public_hashes.numeric_fallback_enabled', false),
                'certified_at' => now()->toIso8601String(),
            ],
        ];
    }

    /**
     * @return list<class-string<Model>>
     */
    public function configuredModels(): array
    {
        return array_values(array_filter(
            config('public_hashes.route_exposed_models', []),
            fn ($class) => is_string($class) && class_exists($class),
        ));
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @return list<array{key: string, passed: bool, message: string}>
     */
    protected function certifyModel(string $modelClass, string $column): array
    {
        $checks = [];
        $label = class_basename($modelClass);

        if (! is_subclass_of($modelClass, Model::class)) {
            return [[
                'key' => "{$label}.class",
                'passed' => false,
                'message' => "{$modelClass} is not an Eloquent model.",
            ]];
        }

        /** @var Model $instance */
        $instance = new $modelClass;
        $table = $instance->getTable();

        $hasColumn = Schema::hasTable($table) && Schema::hasColumn($table, $column);
        $checks[] = [
            'key' => "{$label}.column",
            'passed' => $hasColumn,
            'message' => $hasColumn
                ? "{$table}.{$column} exists."
                : "{$table}.{$column} is missing.",
        ];

        if (! $hasColumn) {
            return $checks;
        }

        $missing = (int) $modelClass::query()
            ->where(fn ($query) => $query->whereNull($column)->orWhere($column, ''))
            ->count();

        $checks[] = [
            'key' => "{$label}.backfill",
            'passed' => $missing === 0,
            'message' => $missing === 0
                ? "All {$label} rows have {$column}."
                : "{$label} has {$missing} row(s) missing {$column}.",
        ];

        $usesTrait = in_array(HasPublicHash::class, class_uses_recursive($modelClass), true);
        $checks[] = [
            'key' => "{$label}.trait",
            'passed' => $usesTrait,
            'message' => $usesTrait
                ? "{$label} uses HasPublicHash."
                : "{$label} is missing HasPublicHash.",
        ];

        $routeKey = (new ReflectionClass($instance))->hasMethod('getRouteKeyName')
            ? $instance->getRouteKeyName()
            : $instance->getKeyName();

        $checks[] = [
            'key' => "{$label}.route_key",
            'passed' => $routeKey === $column,
            'message' => $routeKey === $column
                ? "{$label} route key is {$column}."
                : "{$label} route key is {$routeKey}, expected {$column}.",
        ];

        $invalid = $modelClass::query()
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->pluck($column)
            ->filter(fn ($hash) => ! $this->generator->isValid((string) $hash))
            ->count();

        $checks[] = [
            'key' => "{$label}.format",
            'passed' => $invalid === 0,
            'message' => $invalid === 0
                ? "All {$label} {$column} values match base62 format."
                : "{$label} has {$invalid} invalid {$column} value(s).",
        ];

        if ($this->supportsNotNullAssertion($table, $column)) {
            $nullable = $this->columnIsNullable($table, $column);
            $checks[] = [
                'key' => "{$label}.not_null",
                'passed' => ! $nullable,
                'message' => ! $nullable
                    ? "{$table}.{$column} is NOT NULL."
                    : "{$table}.{$column} is still nullable.",
            ];
        }

        return $checks;
    }

    /**
     * @param  list<class-string<Model>>  $models
     * @return array{key: string, passed: bool, message: string}
     */
    protected function checkDuplicates(array $models, string $column): array
    {
        $duplicateGroups = 0;

        foreach ($models as $modelClass) {
            if (! class_exists($modelClass)) {
                continue;
            }

            /** @var Model $instance */
            $instance = new $modelClass;
            $table = $instance->getTable();

            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                continue;
            }

            $duplicateGroups += (int) DB::table($table)
                ->select($column)
                ->whereNotNull($column)
                ->where($column, '!=', '')
                ->groupBy($column)
                ->havingRaw('COUNT(*) > 1')
                ->count();
        }

        return [
            'key' => 'duplicates',
            'passed' => $duplicateGroups === 0,
            'message' => $duplicateGroups === 0
                ? 'No duplicate public_id values across certified models.'
                : "Found {$duplicateGroups} duplicate public_id group(s).",
        ];
    }

    /**
     * @return array{key: string, passed: bool, message: string}
     */
    protected function checkFallbackConfig(): array
    {
        $enabled = (bool) config('public_hashes.numeric_fallback_enabled', false);

        return [
            'key' => 'fallback_sunset',
            'passed' => ! $enabled,
            'message' => $enabled
                ? 'numeric_fallback_enabled is still true — sunset not complete.'
                : 'numeric_fallback_enabled is false (enforced mode).',
        ];
    }

    /**
     * @return array{key: string, passed: bool, message: string}
     */
    protected function checkDeferredDocumentation(): array
    {
        $deferred = config('public_hashes.deferred_models', []);

        return [
            'key' => 'deferred_models',
            'passed' => is_array($deferred) && $deferred !== [],
            'message' => is_array($deferred) && $deferred !== []
                ? 'Deferred model list is documented in config.'
                : 'Deferred model list is empty or missing.',
        ];
    }

    protected function supportsNotNullAssertion(string $table, string $column): bool
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return false;
        }

        return Schema::getConnection()->getDriverName() !== 'sqlite';
    }

    protected function columnIsNullable(string $table, string $column): bool
    {
        $columns = Schema::getConnection()->getSchemaBuilder()->getColumns($table);

        foreach ($columns as $definition) {
            if (($definition['name'] ?? null) === $column) {
                return (bool) ($definition['nullable'] ?? true);
            }
        }

        return true;
    }
}
