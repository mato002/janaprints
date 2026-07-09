<?php

use Illuminate\Database\Schema\Blueprint;

if (! function_exists('add_public_id_column')) {
    /**
     * Add a nullable unique public_id column for phased public-hash rollout.
     *
     * Usage in a migration:
     *
     *   require_once database_path('migrations/helpers/add_public_id_column.php');
     *
 *   Schema::table('customers', function (Blueprint $table) {
 *       add_public_id_column($table, afterId: true);
 *   });
     *
     * Backfill with `php artisan public-hash:backfill --model=...`, then add a
     * follow-up migration to make the column NOT NULL.
     */
    function add_public_id_column(Blueprint $table, ?int $length = null, bool $afterId = false): void
    {
        $length ??= (int) config('public_hashes.length', 16);
        $column = (string) config('public_hashes.column', 'public_id');

        $definition = $table->string($column, $length)->nullable()->unique();

        if ($afterId) {
            $definition->after('id');
        }
    }
}
