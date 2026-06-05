<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('artwork_requests')) {
            return;
        }

        Schema::table('artwork_requests', function (Blueprint $table) {
            $table->index(['company_id', 'branch_id', 'created_at'], 'artwork_requests_reporting_scope_idx');
            $table->index(['company_id', 'status', 'created_at'], 'artwork_requests_reporting_status_idx');
            $table->index(['company_id', 'customer_id', 'created_at'], 'artwork_requests_reporting_customer_idx');
            $table->index(['company_id', 'assigned_designer_id', 'status'], 'artwork_requests_reporting_designer_idx');
            $table->index(['company_id', 'due_date', 'status'], 'artwork_requests_reporting_due_idx');
        });

        if (Schema::hasTable('artwork_approvals')) {
            Schema::table('artwork_approvals', function (Blueprint $table) {
                $table->index(['company_id', 'artwork_request_id', 'decision'], 'artwork_approvals_reporting_decision_idx');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('artwork_requests')) {
            return;
        }

        Schema::table('artwork_requests', function (Blueprint $table) {
            $table->dropIndex('artwork_requests_reporting_scope_idx');
            $table->dropIndex('artwork_requests_reporting_status_idx');
            $table->dropIndex('artwork_requests_reporting_customer_idx');
            $table->dropIndex('artwork_requests_reporting_designer_idx');
            $table->dropIndex('artwork_requests_reporting_due_idx');
        });

        if (Schema::hasTable('artwork_approvals')) {
            Schema::table('artwork_approvals', function (Blueprint $table) {
                $table->dropIndex('artwork_approvals_reporting_decision_idx');
            });
        }
    }
};
