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
        'customers',
        'leads',
        'quotations',
        'sales_orders',
        'production_job_cards',
        'customer_invoices',
        'customer_payments',
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
