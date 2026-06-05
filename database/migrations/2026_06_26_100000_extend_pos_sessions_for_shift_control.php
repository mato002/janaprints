<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_sessions', function (Blueprint $table) {
            $table->string('terminal', 40)->nullable()->after('session_number');
            $table->decimal('expected_mpesa', 14, 2)->nullable()->after('expected_cash');
            $table->decimal('expected_card', 14, 2)->nullable()->after('expected_mpesa');
            $table->decimal('expected_bank', 14, 2)->nullable()->after('expected_card');
            $table->decimal('expected_total', 14, 2)->nullable()->after('expected_bank');
            $table->boolean('variance_requires_approval')->default(false)->after('variance');
            $table->foreignId('variance_approved_by')->nullable()->after('closed_by')->constrained('users')->nullOnDelete();
            $table->timestamp('variance_approved_at')->nullable()->after('variance_approved_by');
        });
    }

    public function down(): void
    {
        Schema::table('pos_sessions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('variance_approved_by');
            $table->dropColumn([
                'terminal',
                'expected_mpesa',
                'expected_card',
                'expected_bank',
                'expected_total',
                'variance_requires_approval',
                'variance_approved_at',
            ]);
        });
    }
};
