<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_reorder_alerts', function (Blueprint $table) {
            $table->foreignId('warehouse_id')->nullable()->after('inventory_item_id')->constrained()->nullOnDelete();
            $table->string('status', 20)->default('open')->after('reorder_level');
            $table->foreignId('acknowledged_by')->nullable()->after('alerted_at')->constrained('users')->nullOnDelete();
            $table->timestamp('acknowledged_at')->nullable()->after('acknowledged_by');
            $table->foreignId('resolved_by')->nullable()->after('acknowledged_at')->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable()->after('resolved_by');
        });

        DB::table('inventory_reorder_alerts')->where('is_resolved', true)->update(['status' => 'resolved']);
        DB::table('inventory_reorder_alerts')->where('is_resolved', false)->update(['status' => 'open']);
    }

    public function down(): void
    {
        Schema::table('inventory_reorder_alerts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('warehouse_id');
            $table->dropColumn('status');
            $table->dropConstrainedForeignId('acknowledged_by');
            $table->dropColumn('acknowledged_at');
            $table->dropConstrainedForeignId('resolved_by');
            $table->dropColumn('resolved_at');
        });
    }
};
