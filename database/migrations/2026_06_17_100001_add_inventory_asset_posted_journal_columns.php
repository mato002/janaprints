<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_receipts', function (Blueprint $table) {
            $table->foreignId('posted_journal_id')->nullable()->after('posted_at')->constrained('journals')->nullOnDelete();
        });

        Schema::table('stock_issues', function (Blueprint $table) {
            $table->foreignId('posted_journal_id')->nullable()->after('posted_at')->constrained('journals')->nullOnDelete();
        });

        Schema::table('stock_adjustments', function (Blueprint $table) {
            $table->foreignId('posted_journal_id')->nullable()->after('posted_at')->constrained('journals')->nullOnDelete();
        });

        Schema::table('production_material_consumptions', function (Blueprint $table) {
            $table->foreignId('posted_journal_id')->nullable()->after('consumed_at')->constrained('journals')->nullOnDelete();
        });

        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->foreignId('posted_journal_id')->nullable()->after('posted_at')->constrained('journals')->nullOnDelete();
        });

        Schema::table('asset_depreciation_entries', function (Blueprint $table) {
            $table->foreignId('posted_journal_id')->nullable()->after('net_book_value_after')->constrained('journals')->nullOnDelete();
        });

        Schema::table('asset_disposals', function (Blueprint $table) {
            $table->foreignId('posted_journal_id')->nullable()->after('notes')->constrained('journals')->nullOnDelete();
        });

        Schema::table('rfq_vendors', function (Blueprint $table) {
            $table->string('response_token', 64)->nullable()->unique()->after('responded_at');
        });

        Schema::table('asset_categories', function (Blueprint $table) {
            $table->string('default_gl_code', 20)->nullable()->after('depreciation_method');
        });
    }

    public function down(): void
    {
        Schema::table('asset_categories', function (Blueprint $table) {
            $table->dropColumn('default_gl_code');
        });

        Schema::table('rfq_vendors', function (Blueprint $table) {
            $table->dropColumn('response_token');
        });

        foreach ([
            'asset_disposals',
            'asset_depreciation_entries',
            'goods_receipts',
            'production_material_consumptions',
            'stock_adjustments',
            'stock_issues',
            'stock_receipts',
        ] as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropConstrainedForeignId('posted_journal_id');
            });
        }
    }
};
