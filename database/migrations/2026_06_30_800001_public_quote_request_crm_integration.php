<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('public_quote_requests')) {
            Schema::table('public_quote_requests', function (Blueprint $table) {
                if (! Schema::hasColumn('public_quote_requests', 'company_id')) {
                    $table->foreignId('company_id')->nullable()->after('uuid')->constrained()->nullOnDelete();
                }
                if (! Schema::hasColumn('public_quote_requests', 'branch_id')) {
                    $table->foreignId('branch_id')->nullable()->after('company_id')->constrained()->nullOnDelete();
                }
                if (! Schema::hasColumn('public_quote_requests', 'lead_id')) {
                    $table->foreignId('lead_id')->nullable()->after('branch_id')->constrained()->nullOnDelete();
                }
                if (! Schema::hasColumn('public_quote_requests', 'quotation_id')) {
                    $table->foreignId('quotation_id')->nullable()->after('lead_id')->constrained()->nullOnDelete();
                }
                if (! Schema::hasColumn('public_quote_requests', 'artwork_request_id')) {
                    $table->foreignId('artwork_request_id')->nullable()->after('quotation_id')->constrained('artwork_requests')->nullOnDelete();
                }
            });
        }

        if (Schema::hasTable('leads')) {
            Schema::table('leads', function (Blueprint $table) {
                if (! Schema::hasColumn('leads', 'public_quote_request_id')) {
                    $table->foreignId('public_quote_request_id')->nullable()->after('customer_id')->constrained()->nullOnDelete();
                    $table->unique('public_quote_request_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('leads') && Schema::hasColumn('leads', 'public_quote_request_id')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->dropConstrainedForeignId('public_quote_request_id');
            });
        }

        if (Schema::hasTable('public_quote_requests')) {
            Schema::table('public_quote_requests', function (Blueprint $table) {
                foreach (['artwork_request_id', 'quotation_id', 'lead_id', 'branch_id', 'company_id'] as $column) {
                    if (Schema::hasColumn('public_quote_requests', $column)) {
                        $table->dropConstrainedForeignId($column);
                    }
                }
            });
        }
    }
};
