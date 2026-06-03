<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->index(['company_id', 'created_at'], 'activity_logs_company_created_idx');
        });

        $this->addTenantStatusIndexes('quotations', 'quotations_tenant_status_idx', 'quotations_company_date_idx', 'quotation_date');
        $this->addTenantStatusIndexes('sales_orders', 'sales_orders_tenant_status_idx', 'sales_orders_company_date_idx', 'order_date');
        $this->addTenantStatusIndexes('production_job_cards', 'job_cards_tenant_status_idx', 'job_cards_company_end_date_idx', 'planned_end_date');
        $this->addTenantStatusIndexes('artwork_requests', 'artwork_requests_tenant_status_idx', 'artwork_requests_company_due_idx', 'due_date');
        $this->addTenantStatusIndexes('leads', 'leads_tenant_status_idx');
        $this->addTenantStatusIndexes('customers', 'customers_tenant_status_idx');
        $this->addTenantStatusIndexes('stock_receipts', 'stock_receipts_tenant_status_idx');
        $this->addTenantStatusIndexes('stock_issues', 'stock_issues_tenant_status_idx');
        $this->addTenantStatusIndexes('stock_adjustments', 'stock_adjustments_tenant_status_idx');
        $this->addTenantStatusIndexes('production_queues', 'production_queues_tenant_status_idx');

        Schema::table('lead_follow_ups', function (Blueprint $table) {
            $table->index(['company_id', 'branch_id', 'scheduled_at'], 'lead_follow_ups_tenant_scheduled_idx');
            $table->index(['company_id', 'branch_id', 'status'], 'lead_follow_ups_tenant_status_idx');
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->index(['company_id', 'branch_id', 'inventory_item_id'], 'inv_movements_tenant_item_idx');
            $table->index(['company_id', 'branch_id', 'movement_date'], 'inv_movements_tenant_date_idx');
        });

        Schema::table('inventory_reorder_alerts', function (Blueprint $table) {
            $table->index(['company_id', 'branch_id', 'is_resolved'], 'inv_reorder_alerts_tenant_resolved_idx');
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex('activity_logs_company_created_idx');
            $table->dropConstrainedForeignId('company_id');
        });

        $this->dropTenantStatusIndexes('quotations', 'quotations_tenant_status_idx', 'quotations_company_date_idx');
        $this->dropTenantStatusIndexes('sales_orders', 'sales_orders_tenant_status_idx', 'sales_orders_company_date_idx');
        $this->dropTenantStatusIndexes('production_job_cards', 'job_cards_tenant_status_idx', 'job_cards_company_end_date_idx');
        $this->dropTenantStatusIndexes('artwork_requests', 'artwork_requests_tenant_status_idx', 'artwork_requests_company_due_idx');
        $this->dropTenantStatusIndexes('leads', 'leads_tenant_status_idx');
        $this->dropTenantStatusIndexes('customers', 'customers_tenant_status_idx');
        $this->dropTenantStatusIndexes('stock_receipts', 'stock_receipts_tenant_status_idx');
        $this->dropTenantStatusIndexes('stock_issues', 'stock_issues_tenant_status_idx');
        $this->dropTenantStatusIndexes('stock_adjustments', 'stock_adjustments_tenant_status_idx');
        $this->dropTenantStatusIndexes('production_queues', 'production_queues_tenant_status_idx');

        Schema::table('lead_follow_ups', function (Blueprint $table) {
            $table->dropIndex('lead_follow_ups_tenant_scheduled_idx');
            $table->dropIndex('lead_follow_ups_tenant_status_idx');
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->dropIndex('inv_movements_tenant_item_idx');
            $table->dropIndex('inv_movements_tenant_date_idx');
        });

        Schema::table('inventory_reorder_alerts', function (Blueprint $table) {
            $table->dropIndex('inv_reorder_alerts_tenant_resolved_idx');
        });
    }

    protected function addTenantStatusIndexes(
        string $table,
        string $statusIndex,
        ?string $dateIndex = null,
        ?string $dateColumn = null,
    ): void {
        Schema::table($table, function (Blueprint $table) use ($statusIndex, $dateIndex, $dateColumn) {
            $table->index(['company_id', 'branch_id', 'status'], $statusIndex);

            if ($dateIndex && $dateColumn) {
                $table->index(['company_id', $dateColumn], $dateIndex);
            }
        });
    }

    protected function dropTenantStatusIndexes(string $table, string $statusIndex, ?string $dateIndex = null): void
    {
        Schema::table($table, function (Blueprint $table) use ($statusIndex, $dateIndex) {
            $table->dropIndex($statusIndex);

            if ($dateIndex) {
                $table->dropIndex($dateIndex);
            }
        });
    }
};
