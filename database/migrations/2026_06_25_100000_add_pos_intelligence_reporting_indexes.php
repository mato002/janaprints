<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pos_sales')) {
            Schema::table('pos_sales', function (Blueprint $table) {
                if (! $this->hasIndex('pos_sales', 'pos_sales_intel_scope_idx')) {
                    $table->index(['company_id', 'branch_id', 'sale_date'], 'pos_sales_intel_scope_idx');
                }
                if (! $this->hasIndex('pos_sales', 'pos_sales_intel_status_idx')) {
                    $table->index(['company_id', 'status', 'sale_date'], 'pos_sales_intel_status_idx');
                }
                if (! $this->hasIndex('pos_sales', 'pos_sales_intel_cashier_idx')) {
                    $table->index(['company_id', 'cashier_id', 'sale_date'], 'pos_sales_intel_cashier_idx');
                }
                if (! $this->hasIndex('pos_sales', 'pos_sales_intel_session_idx')) {
                    $table->index(['pos_session_id', 'status'], 'pos_sales_intel_session_idx');
                }
            });
        }

        if (Schema::hasTable('pos_payments')) {
            Schema::table('pos_payments', function (Blueprint $table) {
                if (! $this->hasIndex('pos_payments', 'pos_payments_intel_method_idx')) {
                    $table->index(['pos_sale_id', 'payment_method'], 'pos_payments_intel_method_idx');
                }
            });
        }

        if (Schema::hasTable('pos_sessions')) {
            Schema::table('pos_sessions', function (Blueprint $table) {
                if (! $this->hasIndex('pos_sessions', 'pos_sessions_intel_scope_idx')) {
                    $table->index(['company_id', 'branch_id', 'status', 'closed_at'], 'pos_sessions_intel_scope_idx');
                }
            });
        }

        if (Schema::hasTable('pos_returns')) {
            Schema::table('pos_returns', function (Blueprint $table) {
                if (! $this->hasIndex('pos_returns', 'pos_returns_intel_scope_idx')) {
                    $table->index(['company_id', 'branch_id', 'status', 'completed_at'], 'pos_returns_intel_scope_idx');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pos_sales')) {
            Schema::table('pos_sales', function (Blueprint $table) {
                $table->dropIndex('pos_sales_intel_scope_idx');
                $table->dropIndex('pos_sales_intel_status_idx');
                $table->dropIndex('pos_sales_intel_cashier_idx');
                $table->dropIndex('pos_sales_intel_session_idx');
            });
        }

        if (Schema::hasTable('pos_payments')) {
            Schema::table('pos_payments', function (Blueprint $table) {
                $table->dropIndex('pos_payments_intel_method_idx');
            });
        }

        if (Schema::hasTable('pos_sessions')) {
            Schema::table('pos_sessions', function (Blueprint $table) {
                $table->dropIndex('pos_sessions_intel_scope_idx');
            });
        }

        if (Schema::hasTable('pos_returns')) {
            Schema::table('pos_returns', function (Blueprint $table) {
                $table->dropIndex('pos_returns_intel_scope_idx');
            });
        }
    }

    protected function hasIndex(string $table, string $index): bool
    {
        $indexes = Schema::getIndexes($table);

        foreach ($indexes as $definition) {
            if (($definition['name'] ?? '') === $index) {
                return true;
            }
        }

        return false;
    }
};
