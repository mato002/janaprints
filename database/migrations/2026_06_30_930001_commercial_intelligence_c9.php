<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('job_cost_sheets')) {
            Schema::table('job_cost_sheets', function (Blueprint $table) {
                if (! $this->indexExists('job_cost_sheets', 'jcs_company_branch_calc_idx')) {
                    $table->index(['company_id', 'branch_id', 'calculated_at'], 'jcs_company_branch_calc_idx');
                }
                if (! $this->indexExists('job_cost_sheets', 'jcs_job_card_idx')) {
                    $table->index(['production_job_card_id'], 'jcs_job_card_idx');
                }
            });
        }

        if (Schema::hasTable('production_job_cards')) {
            Schema::table('production_job_cards', function (Blueprint $table) {
                if (! $this->indexExists('production_job_cards', 'pjc_company_branch_customer_idx')) {
                    $table->index(['company_id', 'branch_id', 'customer_id'], 'pjc_company_branch_customer_idx');
                }
                if (! $this->indexExists('production_job_cards', 'pjc_sales_order_idx')) {
                    $table->index(['sales_order_id'], 'pjc_sales_order_idx');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('job_cost_sheets')) {
            Schema::table('job_cost_sheets', function (Blueprint $table) {
                if ($this->indexExists('job_cost_sheets', 'jcs_company_branch_calc_idx')) {
                    $table->dropIndex('jcs_company_branch_calc_idx');
                }
                if ($this->indexExists('job_cost_sheets', 'jcs_job_card_idx')) {
                    $table->dropIndex('jcs_job_card_idx');
                }
            });
        }

        if (Schema::hasTable('production_job_cards')) {
            Schema::table('production_job_cards', function (Blueprint $table) {
                if ($this->indexExists('production_job_cards', 'pjc_company_branch_customer_idx')) {
                    $table->dropIndex('pjc_company_branch_customer_idx');
                }
                if ($this->indexExists('production_job_cards', 'pjc_sales_order_idx')) {
                    $table->dropIndex('pjc_sales_order_idx');
                }
            });
        }
    }

    protected function indexExists(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            $rows = $connection->select("PRAGMA index_list('{$table}')");

            return collect($rows)->contains(fn ($row) => ($row->name ?? '') === $index);
        }

        $database = $connection->getDatabaseName();
        $result = $connection->select(
            'SELECT COUNT(*) AS aggregate FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?',
            [$database, $table, $index],
        );

        return (int) ($result[0]->aggregate ?? 0) > 0;
    }
};
