<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('report_exports') && ! Schema::hasTable('commercial_report_exports')) {
            Schema::rename('report_exports', 'commercial_report_exports');
        }

        if (! Schema::hasTable('commercial_report_exports')) {
            Schema::create('commercial_report_exports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('module', 40);
                $table->string('tab', 40)->default('summary');
                $table->string('format', 10);
                $table->json('scope_payload')->nullable();
                $table->string('status', 20)->default('queued');
                $table->string('storage_path')->nullable();
                $table->string('filename')->nullable();
                $table->string('mime_type', 100)->nullable();
                $table->unsignedInteger('row_count')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamp('queued_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'status']);
                $table->index(['company_id', 'module']);
                $table->index('expires_at');
            });

            return;
        }

        Schema::table('commercial_report_exports', function (Blueprint $table) {
            if (! Schema::hasColumn('commercial_report_exports', 'scope_payload')) {
                $table->json('scope_payload')->nullable()->after('format');
            }
            if (! Schema::hasColumn('commercial_report_exports', 'row_count')) {
                $table->unsignedInteger('row_count')->nullable()->after('mime_type');
            }
            if (! Schema::hasColumn('commercial_report_exports', 'queued_at')) {
                $table->timestamp('queued_at')->nullable()->after('error_message');
            }
            if (! Schema::hasColumn('commercial_report_exports', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('completed_at');
            }
        });

        DB::table('commercial_report_exports')
            ->where('status', 'pending')
            ->update(['status' => 'queued']);

        DB::table('commercial_report_exports')
            ->whereNull('queued_at')
            ->update(['queued_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('commercial_report_exports')) {
            return;
        }

        Schema::table('commercial_report_exports', function (Blueprint $table) {
            $columns = ['scope_payload', 'row_count', 'queued_at', 'expires_at'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('commercial_report_exports', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        if (! Schema::hasTable('report_exports')) {
            Schema::rename('commercial_report_exports', 'report_exports');
        }
    }
};
