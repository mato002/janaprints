<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_notes', function (Blueprint $table) {
            if (! Schema::hasColumn('delivery_notes', 'invoiced_by')) {
                $table->foreignId('invoiced_by')->nullable()->after('invoice_ready')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('delivery_notes', 'invoiced_at')) {
                $table->timestamp('invoiced_at')->nullable()->after('invoiced_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('delivery_notes', function (Blueprint $table) {
            if (Schema::hasColumn('delivery_notes', 'invoiced_by')) {
                $table->dropConstrainedForeignId('invoiced_by');
            }
            if (Schema::hasColumn('delivery_notes', 'invoiced_at')) {
                $table->dropColumn('invoiced_at');
            }
        });
    }
};
