<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('product_production_route_steps', 'work_center_id')) {
            Schema::table('product_production_route_steps', function (Blueprint $table) {
                $table->foreignId('work_center_id')->nullable()->after('inventory_item_id')
                    ->constrained('work_centers')->nullOnDelete();
                $table->index('work_center_id', 'prod_route_wc_idx');
            });
        }

        if (! Schema::hasColumn('job_card_route_steps', 'work_center_id')) {
            Schema::table('job_card_route_steps', function (Blueprint $table) {
                $table->foreignId('work_center_id')->nullable()->after('production_job_card_id')
                    ->constrained('work_centers')->nullOnDelete();
                $table->index('work_center_id', 'jc_route_wc_idx');
            });
        }

        if (! Schema::hasColumn('production_queues', 'job_card_route_step_id')) {
            Schema::table('production_queues', function (Blueprint $table) {
                $table->foreignId('job_card_route_step_id')->nullable()->after('production_job_card_id')
                    ->constrained('job_card_route_steps')->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('production_queues')) {
            try {
                Schema::table('production_queues', function (Blueprint $table) {
                    $table->dropUnique(['work_center_id', 'production_job_card_id']);
                });
            } catch (\Throwable) {
                // Index may already be dropped.
            }

            if (! $this->indexExists('production_queues', 'prod_queue_route_step_uq')) {
                Schema::table('production_queues', function (Blueprint $table) {
                    $table->unique('job_card_route_step_id', 'prod_queue_route_step_uq');
                });
            }
        }

        if (Schema::hasTable('production_queues')) {
            DB::table('production_queues')->where('status', 'pending')->update(['status' => 'waiting']);
        }

        if (! Schema::hasColumn('production_job_cards', 'required_date')) {
            Schema::table('production_job_cards', function (Blueprint $table) {
                $table->date('required_date')->nullable()->after('planned_end_date');
                $table->unsignedInteger('estimated_duration_minutes')->nullable()->after('required_date');
                $table->index(['company_id', 'branch_id', 'status'], 'prod_job_cards_tenant_status_idx');
                $table->index(['company_id', 'branch_id', 'priority'], 'prod_job_cards_tenant_priority_idx');
                $table->index(['company_id', 'branch_id', 'required_date'], 'prod_job_cards_tenant_required_idx');
            });
        }

        if (! Schema::hasColumn('sales_orders', 'is_direct_order')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                $table->boolean('is_direct_order')->default(false)->after('status');
                $table->foreignId('repeat_source_sales_order_id')->nullable()->after('is_direct_order')
                    ->constrained('sales_orders')->nullOnDelete();
            });
        }

        if (Schema::hasTable('production_queues') && ! $this->indexExists('production_queues', 'prod_queue_wc_status_idx')) {
            Schema::table('production_queues', function (Blueprint $table) {
                $table->index(['work_center_id', 'status', 'queue_position'], 'prod_queue_wc_status_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('production_job_cards', 'required_date')) {
            Schema::table('production_job_cards', function (Blueprint $table) {
                $table->dropIndex('prod_job_cards_tenant_status_idx');
                $table->dropIndex('prod_job_cards_tenant_priority_idx');
                $table->dropIndex('prod_job_cards_tenant_required_idx');
                $table->dropColumn(['required_date', 'estimated_duration_minutes']);
            });
        }

        if (Schema::hasColumn('sales_orders', 'is_direct_order')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                $table->dropConstrainedForeignId('repeat_source_sales_order_id');
                $table->dropColumn('is_direct_order');
            });
        }

        if (Schema::hasColumn('product_production_route_steps', 'work_center_id')) {
            Schema::table('product_production_route_steps', function (Blueprint $table) {
                $table->dropConstrainedForeignId('work_center_id');
            });
        }

        if (Schema::hasColumn('job_card_route_steps', 'work_center_id')) {
            Schema::table('job_card_route_steps', function (Blueprint $table) {
                $table->dropConstrainedForeignId('work_center_id');
            });
        }

        if (Schema::hasColumn('production_queues', 'job_card_route_step_id')) {
            Schema::table('production_queues', function (Blueprint $table) {
                $table->dropUnique('prod_queue_route_step_uq');
                $table->dropConstrainedForeignId('job_card_route_step_id');
            });
        }
    }

    protected function indexExists(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            $indexes = $connection->select("PRAGMA index_list('{$table}')");

            return collect($indexes)->contains(fn ($row) => ($row->name ?? '') === $index);
        }

        $database = $connection->getDatabaseName();

        return (bool) $connection->selectOne(
            'SELECT 1 FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ? LIMIT 1',
            [$database, $table, $index],
        );
    }
};
