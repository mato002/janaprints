<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cashier_id')->constrained('users')->cascadeOnDelete();
            $table->string('session_number', 40);
            $table->decimal('opening_float', 14, 2)->default(0);
            $table->decimal('opening_cash', 14, 2)->default(0);
            $table->decimal('expected_cash', 14, 2)->nullable();
            $table->decimal('actual_cash', 14, 2)->nullable();
            $table->decimal('variance', 14, 2)->nullable();
            $table->string('status', 20)->default('open');
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('opened_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('opening_notes')->nullable();
            $table->text('closing_notes')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'session_number']);
            $table->index(['company_id', 'branch_id', 'status', 'opened_at'], 'pos_sessions_scope_status_idx');
            $table->index(['company_id', 'branch_id', 'cashier_id', 'status'], 'pos_sessions_cashier_active_idx');
        });

        if (Schema::hasTable('pos_sales')) {
            Schema::table('pos_sales', function (Blueprint $table) {
                $table->foreignId('pos_session_id')
                    ->nullable()
                    ->after('cashier_id')
                    ->constrained('pos_sessions')
                    ->nullOnDelete();

                $table->index(['pos_session_id', 'status'], 'pos_sales_session_status_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pos_sales') && Schema::hasColumn('pos_sales', 'pos_session_id')) {
            Schema::table('pos_sales', function (Blueprint $table) {
                $table->dropForeign(['pos_session_id']);
                $table->dropIndex('pos_sales_session_status_idx');
                $table->dropColumn('pos_session_id');
            });
        }

        Schema::dropIfExists('pos_sessions');
    }
};
