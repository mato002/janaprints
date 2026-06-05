<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_payments', function (Blueprint $table) {
            $table->foreignId('posted_journal_id')->nullable()->after('reference')->constrained('journals')->nullOnDelete();
        });

        Schema::table('pos_returns', function (Blueprint $table) {
            $table->foreignId('posted_journal_id')->nullable()->after('notes')->constrained('journals')->nullOnDelete();
        });

        Schema::table('pos_cash_reconciliations', function (Blueprint $table) {
            $table->foreignId('posted_journal_id')->nullable()->after('approval_notes')->constrained('journals')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pos_cash_reconciliations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('posted_journal_id');
        });

        Schema::table('pos_returns', function (Blueprint $table) {
            $table->dropConstrainedForeignId('posted_journal_id');
        });

        Schema::table('pos_payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('posted_journal_id');
        });
    }
};
