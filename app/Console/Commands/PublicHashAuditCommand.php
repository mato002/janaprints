<?php

namespace App\Console\Commands;

use App\Models\Concerns\HasPublicHash;
use App\Support\PublicHash\PublicHashGenerator;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use ReflectionClass;

class PublicHashAuditCommand extends Command
{
    protected $signature = 'public-hash:audit
                            {--strict : Exit with failure when issues are found}
                            {--model= : Audit a single fully qualified model class}
                            {--routes : Scan for unsafe custom Route::bind entries}
                            {--views : Scan Blade views for explicit numeric route leaks}
                            {--js : Scan JavaScript for numeric admin path construction}';

    protected $description = 'Audit public hash readiness and ID leak patterns for route-exposed models';

    /**
     * @var list<string>
     */
    protected array $migratedRoutePrefixes = [
        'admin/crm/customers/',
        'admin/crm/leads/',
        'admin/quotations/list/',
        'admin/sales-orders/list/',
        'admin/production/job-cards/',
        'admin/invoices/',
        'admin/payments/',
        'admin/artwork/requests/',
        'admin/dispatch/delivery-notes/',
        'admin/inventory/items/',
        'admin/inventory/warehouses/',
        'admin/inventory/receipts/',
        'admin/inventory/issues/',
        'admin/inventory/adjustments/',
        'admin/assets/register/',
        'admin/assets/maintenance/work-orders/',
        'client/quotations/',
        'client/orders/',
        'client/jobs/',
        'client/invoices/',
        'client/artwork/',
        'payment-receipt/',
    ];

    /**
     * @var list<string>
     */
    protected array $unsafeRouteFkPatterns = [
        'customer_id',
        'lead_id',
        'quotation_id',
        'sales_order_id',
        'job_card_id',
        'invoice_id',
        'payment_id',
        'artwork_request_id',
        'delivery_note_id',
        'warehouse_id',
        'fixed_asset_id',
    ];

    public function handle(PublicHashGenerator $generator): int
    {
        $issues = 0;

        $this->info('Public Hash Route ID Audit');
        $this->newLine();

        $models = $this->resolveModels();
        $rows = [];

        foreach ($models as $modelClass) {
            $rows[] = $this->auditModel($modelClass, $issues);
        }

        if ($rows !== []) {
            $this->table(
                ['Model', 'Table', 'Column', 'Missing', 'HasPublicHash', 'Route key'],
                $rows,
            );
        } else {
            $this->warn('No models to audit. Configure route_exposed_models or pass --model=.');
        }

        if ($this->option('routes')) {
            $issues += $this->auditCustomRouteBindings();
        }

        if ($this->option('views')) {
            $issues += $this->auditBladeViews();
            $issues += $this->auditServiceRouteLeaks();
            $issues += $this->auditPartialSelects();
        }

        if ($this->option('js')) {
            $issues += $this->auditJavaScriptPathLeaks();
        }

        $this->newLine();
        $this->line("Issues found: {$issues}");

        if ($issues > 0 && $this->option('strict')) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * @return list<class-string<Model>>
     */
    protected function resolveModels(): array
    {
        if ($model = $this->option('model')) {
            return [(string) $model];
        }

        return array_values(array_filter(
            config('public_hashes.route_exposed_models', []),
            fn ($class) => is_string($class) && class_exists($class),
        ));
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @return list<string|int>
     */
    protected function auditModel(string $modelClass, int &$issues): array
    {
        if (! class_exists($modelClass) || ! is_subclass_of($modelClass, Model::class)) {
            $issues++;

            return [$modelClass, '—', '—', '—', 'no', '—'];
        }

        /** @var Model $instance */
        $instance = new $modelClass;
        $table = $instance->getTable();
        $column = (string) config('public_hashes.column', 'public_id');
        $hasColumn = Schema::hasTable($table) && Schema::hasColumn($table, $column);
        $missing = '—';

        if ($hasColumn) {
            $missing = (string) $modelClass::query()
                ->where(function ($query) use ($column) {
                    $query->whereNull($column)->orWhere($column, '');
                })
                ->count();
        } else {
            $issues++;
        }

        $usesTrait = in_array(HasPublicHash::class, class_uses_recursive($modelClass), true);
        $routeKey = (new ReflectionClass($instance))->hasMethod('getRouteKeyName')
            ? $instance->getRouteKeyName()
            : $instance->getKeyName();

        if (! $usesTrait) {
            $issues++;
        }

        if ($usesTrait && $routeKey !== $column) {
            $issues++;
        }

        return [
            class_basename($modelClass),
            $table,
            $hasColumn ? $column : 'missing',
            $missing,
            $usesTrait ? 'yes' : 'no',
            $routeKey,
        ];
    }

    protected function auditCustomRouteBindings(): int
    {
        $this->newLine();
        $this->info('Custom Route::bind audit (AppServiceProvider)');

        $path = app_path('Providers/AppServiceProvider.php');

        if (! File::exists($path)) {
            $this->warn('AppServiceProvider not found.');

            return 0;
        }

        $contents = File::get($path);
        $issues = 0;
        $rows = [];

        if (preg_match_all(
            "/Route::bind\('([^']+)',\s*function\s*\([^)]*\)\s*\{([^}]+)\}/s",
            $contents,
            $matches,
            PREG_SET_ORDER,
        )) {
            foreach ($matches as $match) {
                $name = $match[1];
                $body = $match[2];
                $deferred = in_array($name, config('public_hashes.deferred_route_bindings', []), true);
                $unsafe = ! $deferred
                    && (str_contains($body, 'findOrFail($value)') || str_contains($body, "findOrFail(\$value)"));

                if ($unsafe) {
                    $issues++;
                }

                $rows[] = [$name, $deferred ? 'deferred' : ($unsafe ? 'numeric findOrFail' : 'review')];
            }
        }

        if ($rows === []) {
            $this->line('No Route::bind closures detected.');
        } else {
            $this->table(['Bind name', 'Assessment'], $rows);
        }

        return $issues;
    }

    protected function auditBladeViews(): int
    {
        $this->newLine();
        $this->info('Blade view scan (numeric route leaks)');

        $issues = 0;
        $rows = [];
        $viewsPath = resource_path('views');

        foreach (File::allFiles($viewsPath) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $relative = str_replace(['/', '\\'], '/', str_replace(base_path().DIRECTORY_SEPARATOR, '', $file->getPathname()));
            $contents = File::get($file->getPathname());
            $fileIssues = [];

            if (in_array($relative, config('public_hashes.deferred_view_paths', []), true)) {
                continue;
            }

            if (preg_match("/route\([^)]*->id\)/", $contents)) {
                $fileIssues[] = 'route(...->id)';
            }

            foreach ($this->unsafeRouteFkPatterns as $fk) {
                if (preg_match("/route\([^)]*->{$fk}\)/", $contents)) {
                    $fileIssues[] = "route(...->{$fk})";
                }
            }

            if ($fileIssues === []) {
                continue;
            }

            $issues += count($fileIssues);
            $rows[] = [$relative, implode(', ', array_unique($fileIssues))];
        }

        if ($rows === []) {
            $this->line('No explicit numeric route leaks found in Blade views.');
        } else {
            $this->table(['View', 'Issue'], array_slice($rows, 0, 25));

            if (count($rows) > 25) {
                $this->warn('Additional matches truncated. Re-run with ripgrep for full list.');
            }
        }

        return $issues;
    }

    protected function auditServiceRouteLeaks(): int
    {
        $this->newLine();
        $this->info('Service/presenter scan (FK passed to route() for migrated models)');

        $issues = 0;
        $rows = [];
        $paths = array_merge(
            File::glob(app_path('Services/**/*.php')) ?: [],
            File::glob(app_path('Support/**/*.php')) ?: [],
            File::glob(app_path('Http/Controllers/**/*.php')) ?: [],
        );

        foreach ($paths as $path) {
            $contents = File::get($path);
            $relative = str_replace(base_path().DIRECTORY_SEPARATOR, '', $path);
            $fileIssues = [];

            foreach ($this->unsafeRouteFkPatterns as $fk) {
                if (preg_match("/route\([^)]*->{$fk}\)/", $contents)) {
                    $fileIssues[] = "route(...->{$fk})";
                }
            }

            if (preg_match("/route\([^)]*->id\)/", $contents)
                && preg_match('/admin\.(crm|quotations|sales-orders|invoices|payments|production|artwork|dispatch|inventory|assets)/', $contents)) {
                $fileIssues[] = 'route(...->id) on admin resource';
            }

            if ($fileIssues === []) {
                continue;
            }

            $issues += count($fileIssues);
            $rows[] = [$relative, implode(', ', array_unique($fileIssues))];
        }

        if ($rows === []) {
            $this->line('No FK route leaks detected in services.');
        } else {
            $this->table(['File', 'Issue'], array_slice($rows, 0, 25));
        }

        return $issues;
    }

    protected function auditPartialSelects(): int
    {
        $this->newLine();
        $this->info('Partial eager-load scan (missing public_id on hash models)');

        $issues = 0;
        $rows = [];
        $migratedRelations = [
            'jobCard' => 'ProductionJobCard',
            'customer' => 'Customer',
            'salesOrder' => 'SalesOrder',
            'quotation' => 'Quotation',
            'workCenter' => 'WorkCenter',
            'artworkRequest' => 'ArtworkRequest',
            'asset' => 'FixedAsset',
        ];

        $paths = File::glob(app_path('**/*.php')) ?: [];

        foreach ($paths as $path) {
            $contents = File::get($path);

            if (! str_contains($contents, '->with(') && ! str_contains($contents, "'")) {
                continue;
            }

            foreach ($migratedRelations as $relation => $label) {
                $pattern = "/['\"]{$relation}:id,([^'\"]+)['\"]/";

                if (! preg_match_all($pattern, $contents, $matches)) {
                    continue;
                }

                foreach ($matches[1] as $columns) {
                    if (! str_contains($columns, 'public_id')) {
                        $issues++;
                        $rows[] = [
                            str_replace(base_path().DIRECTORY_SEPARATOR, '', $path),
                            "{$relation}:id,... without public_id ({$label})",
                        ];
                    }
                }
            }
        }

        if ($rows === []) {
            $this->line('No partial selects missing public_id detected.');
        } else {
            $this->table(['File', 'Issue'], array_slice($rows, 0, 25));
        }

        return $issues;
    }

    protected function auditJavaScriptPathLeaks(): int
    {
        $this->newLine();
        $this->info('JavaScript scan (numeric admin path segments)');

        $issues = 0;
        $rows = [];
        $jsPath = resource_path('js');

        foreach (File::allFiles($jsPath) as $file) {
            if (! str_ends_with($file->getFilename(), '.js')) {
                continue;
            }

            $contents = File::get($file->getPathname());
            $relative = str_replace(base_path().DIRECTORY_SEPARATOR, '', $file->getPathname());
            $fileIssues = [];

            foreach ($this->migratedRoutePrefixes as $prefix) {
                $escaped = preg_quote($prefix, '/');
                if (preg_match("/{$escaped}\\\\d\+/", $contents) || preg_match("/{$escaped}\\\${/", $contents)) {
                    $fileIssues[] = "numeric/concat path for {$prefix}";
                }
            }

            if (preg_match('/\/admin\/[a-z0-9\-\/]+\/\$\{[^}]+\}/', $contents)) {
                $fileIssues[] = 'template path with ${id}';
            }

            if ($fileIssues === []) {
                continue;
            }

            $issues += count($fileIssues);
            $rows[] = [$relative, implode(', ', array_unique($fileIssues))];
        }

        if ($rows === []) {
            $this->line('No obvious JS numeric path leaks detected.');
        } else {
            $this->table(['File', 'Issue'], $rows);
        }

        return $issues;
    }
}
