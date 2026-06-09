<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('stock_issues')) {
            return;
        }

        Schema::table('stock_issues', function (Blueprint $table) {
            if (! Schema::hasColumn('stock_issues', 'production_override_reason')) {
                $table->text('production_override_reason')->nullable()->after('notes');
            }
            if (! Schema::hasColumn('stock_issues', 'production_override_by')) {
                $table->foreignId('production_override_by')->nullable()->after('production_override_reason')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('stock_issues', 'production_override_at')) {
                $table->timestamp('production_override_at')->nullable()->after('production_override_by');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('stock_issues')) {
            return;
        }

        Schema::table('stock_issues', function (Blueprint $table) {
            if (Schema::hasColumn('stock_issues', 'production_override_by')) {
                $table->dropConstrainedForeignId('production_override_by');
            }
            if (Schema::hasColumn('stock_issues', 'production_override_reason')) {
                $table->dropColumn('production_override_reason');
            }
            if (Schema::hasColumn('stock_issues', 'production_override_at')) {
                $table->dropColumn('production_override_at');
            }
        });
    }
};
