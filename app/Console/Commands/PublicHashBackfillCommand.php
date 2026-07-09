<?php

namespace App\Console\Commands;

use App\Support\PublicHash\PublicHashGenerator;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class PublicHashBackfillCommand extends Command
{
    protected $signature = 'public-hash:backfill
                            {--model= : Fully qualified Eloquent model class}
                            {--all : Backfill all models listed in config/public_hashes.php}
                            {--dry-run : Report counts without writing}
                            {--batch=1000 : Rows per batch}
                            {--force : Run without confirmation when using --all}';

    protected $description = 'Backfill missing public_id values for models that support public hashes';

    public function handle(PublicHashGenerator $generator): int
    {
        $models = $this->resolveTargetModels();

        if ($models === []) {
            $this->error('No models selected. Pass --model=Class or --all with configured route_exposed_models.');

            return self::FAILURE;
        }

        if ($this->option('all') && ! $this->option('force') && ! $this->option('dry-run')) {
            if (! $this->confirm('Backfill public hashes for '.count($models).' configured model(s)?')) {
                $this->warn('Aborted.');

                return self::SUCCESS;
            }
        }

        $dryRun = (bool) $this->option('dry-run');
        $batchSize = max(1, (int) $this->option('batch'));
        $exitCode = self::SUCCESS;

        foreach ($models as $modelClass) {
            $result = $this->backfillModel($modelClass, $generator, $batchSize, $dryRun);

            if ($result === self::FAILURE) {
                $exitCode = self::FAILURE;
            }
        }

        return $exitCode;
    }

    /**
     * @return list<class-string<Model>>
     */
    protected function resolveTargetModels(): array
    {
        if ($model = $this->option('model')) {
            return [$this->assertModelClass((string) $model)];
        }

        if ($this->option('all')) {
            $configured = config('public_hashes.route_exposed_models', []);

            return array_values(array_filter(
                $configured,
                fn ($class) => is_string($class) && class_exists($class),
            ));
        }

        return [];
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    protected function backfillModel(
        string $modelClass,
        PublicHashGenerator $generator,
        int $batchSize,
        bool $dryRun,
    ): int {
        /** @var Model $instance */
        $instance = new $modelClass;
        $table = $instance->getTable();
        $column = (string) config('public_hashes.column', 'public_id');

        if (! Schema::hasTable($table)) {
            $this->warn("Skipping [{$modelClass}]: table [{$table}] does not exist.");

            return self::FAILURE;
        }

        if (! Schema::hasColumn($table, $column)) {
            $this->warn("Skipping [{$modelClass}]: column [{$column}] does not exist on [{$table}].");

            return self::FAILURE;
        }

        $missingQuery = $modelClass::query()->where(function ($query) use ($column) {
            $query->whereNull($column)->orWhere($column, '');
        });

        $missingCount = (clone $missingQuery)->count();
        $this->line("{$modelClass}: {$missingCount} row(s) missing {$column}.");

        if ($missingCount === 0) {
            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->info("Dry run: would backfill {$missingCount} row(s) for [{$modelClass}].");

            return self::SUCCESS;
        }

        $filled = 0;
        $collisions = 0;

        $missingQuery->orderBy($instance->getKeyName())->chunkById($batchSize, function ($rows) use (
            $generator,
            $modelClass,
            $column,
            &$filled,
            &$collisions,
        ) {
            foreach ($rows as $row) {
                if (filled($row->{$column})) {
                    continue;
                }

                try {
                    $hash = $generator->generateUnique($modelClass, $column);
                } catch (RuntimeException $exception) {
                    $collisions++;
                    $this->error($exception->getMessage());

                    continue;
                }

                $row->forceFill([$column => $hash])->saveQuietly();
                $filled++;
            }
        });

        $this->info("Backfilled {$filled} row(s) for [{$modelClass}].");

        if ($collisions > 0) {
            $this->warn("Encountered {$collisions} collision(s) for [{$modelClass}].");
        }

        return self::SUCCESS;
    }

    /**
     * @return class-string<Model>
     */
    protected function assertModelClass(string $modelClass): string
    {
        if (! class_exists($modelClass)) {
            throw new RuntimeException("Model class [{$modelClass}] does not exist.");
        }

        if (! is_subclass_of($modelClass, Model::class)) {
            throw new RuntimeException("Class [{$modelClass}] is not an Eloquent model.");
        }

        return $modelClass;
    }
}
