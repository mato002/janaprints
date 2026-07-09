<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tier 1 + Tier 2 certified tables only.
     *
     * @var list<string>
     */
    protected array $tables = [
        'customers',
        'leads',
        'quotations',
        'sales_orders',
        'production_job_cards',
        'customer_invoices',
        'customer_payments',
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
        $column = (string) config('public_hashes.column', 'public_id');
        $length = (int) config('public_hashes.length', 16);
        $driver = Schema::getConnection()->getDriverName();

        foreach ($this->tables as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                continue;
            }

            $nullCount = DB::table($table)
                ->whereNull($column)
                ->orWhere($column, '')
                ->count();

            if ($nullCount > 0) {
                throw new RuntimeException(
                    "Cannot enforce NOT NULL on {$table}.{$column}: {$nullCount} row(s) still missing public_id. Run public-hash:backfill first.",
                );
            }

            if ($driver === 'sqlite') {
                // SQLite cannot ALTER COLUMN reliably; tests enforce via application layer.
                // Production MySQL/MariaDB/PostgreSQL receive the constraint below.
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($column, $length) {
                $blueprint->string($column, $length)->nullable(false)->change();
            });
        }
    }

    public function down(): void
    {
        $column = (string) config('public_hashes.column', 'public_id');
        $length = (int) config('public_hashes.length', 16);
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            return;
        }

        foreach ($this->tables as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($column, $length) {
                $blueprint->string($column, $length)->nullable()->change();
            });
        }
    }
};
