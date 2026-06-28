<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('client_portal_repeat_requests')) {
            Schema::create('client_portal_repeat_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
                $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
                $table->foreignId('sales_order_id')->constrained()->cascadeOnDelete();
                $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
                $table->string('status', 20)->default('pending');
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['customer_id', 'status'], 'cprr_customer_status_idx');
                $table->index(['sales_order_id'], 'cprr_sales_order_idx');
            });
        }

        if (Schema::hasTable('customer_invoices')) {
            Schema::table('customer_invoices', function (Blueprint $table) {
                if (! $this->indexExists('customer_invoices', 'ci_customer_invoice_date_idx')) {
                    $table->index(['customer_id', 'invoice_date'], 'ci_customer_invoice_date_idx');
                }
            });
        }

        if (Schema::hasTable('sales_orders')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                if (! $this->indexExists('sales_orders', 'so_customer_status_idx')) {
                    $table->index(['customer_id', 'status'], 'so_customer_status_idx');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('client_portal_repeat_requests');

        if (Schema::hasTable('customer_invoices')) {
            Schema::table('customer_invoices', function (Blueprint $table) {
                if ($this->indexExists('customer_invoices', 'ci_customer_invoice_date_idx')) {
                    $table->dropIndex('ci_customer_invoice_date_idx');
                }
            });
        }

        if (Schema::hasTable('sales_orders')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                if ($this->indexExists('sales_orders', 'so_customer_status_idx')) {
                    $table->dropIndex('so_customer_status_idx');
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
