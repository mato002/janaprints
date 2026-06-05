<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('leads')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->index(['company_id', 'branch_id', 'created_at'], 'leads_reporting_scope_idx');
                $table->index(['company_id', 'lead_source_id', 'created_at'], 'leads_reporting_source_idx');
                $table->index(['company_id', 'assigned_to', 'created_at'], 'leads_reporting_assignee_idx');
            });
        }

        if (Schema::hasTable('production_job_cards')) {
            Schema::table('production_job_cards', function (Blueprint $table) {
                $table->index(['company_id', 'branch_id', 'created_at'], 'production_job_cards_reporting_scope_idx');
            });
        }

        if (Schema::hasTable('delivery_notes')) {
            Schema::table('delivery_notes', function (Blueprint $table) {
                $table->index(['company_id', 'branch_id', 'delivery_date'], 'delivery_notes_reporting_scope_idx');
                $table->index(['company_id', 'status', 'delivery_date'], 'delivery_notes_reporting_status_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('leads')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->dropIndex('leads_reporting_scope_idx');
                $table->dropIndex('leads_reporting_source_idx');
                $table->dropIndex('leads_reporting_assignee_idx');
            });
        }

        if (Schema::hasTable('production_job_cards')) {
            Schema::table('production_job_cards', function (Blueprint $table) {
                $table->dropIndex('production_job_cards_reporting_scope_idx');
            });
        }

        if (Schema::hasTable('delivery_notes')) {
            Schema::table('delivery_notes', function (Blueprint $table) {
                $table->dropIndex('delivery_notes_reporting_scope_idx');
                $table->dropIndex('delivery_notes_reporting_status_idx');
            });
        }
    }
};
