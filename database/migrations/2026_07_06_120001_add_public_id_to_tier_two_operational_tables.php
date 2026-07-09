<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var list<string>
     */
    protected array $tables = [
        'production_specifications',
        'print_product_templates',
        'production_queues',
        'work_centers',
        'quality_checks',
        'artwork_requests',
        'artwork_files',
        'artwork_versions',
        'delivery_notes',
        'inventory_items',
        'warehouses',
        'stock_receipts',
        'stock_issues',
        'stock_adjustments',
        'fixed_assets',
        'maintenance_work_orders',
    ];

    public function up(): void
    {
        require_once database_path('migrations/helpers/add_public_id_column.php');

        $length = (int) config('public_hashes.length', 16);
        $column = (string) config('public_hashes.column', 'public_id');

        foreach ($this->tables as $table) {
            if (! Schema::hasTable($table) || Schema::hasColumn($table, $column)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($length) {
                add_public_id_column($blueprint, $length, afterId: true);
            });
        }
    }

    public function down(): void
    {
        $column = (string) config('public_hashes.column', 'public_id');

        foreach ($this->tables as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($column) {
                $blueprint->dropUnique([$column]);
                $blueprint->dropColumn($column);
            });
        }
    }
};
