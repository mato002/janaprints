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
                if (! Schema::hasColumn('public_quote_requests', 'priority')) {
                    $table->string('priority', 20)->nullable()->after('status');
                }
                if (! Schema::hasColumn('public_quote_requests', 'expected_value')) {
                    $table->decimal('expected_value', 15, 2)->nullable()->after('priority');
                }
                if (! Schema::hasColumn('public_quote_requests', 'probability')) {
                    $table->unsignedTinyInteger('probability')->nullable()->after('expected_value');
                }
                if (! Schema::hasColumn('public_quote_requests', 'target_follow_up_at')) {
                    $table->date('target_follow_up_at')->nullable()->after('probability');
                }
            });
        }

        if (! Schema::hasTable('public_quote_request_notes')) {
            Schema::create('public_quote_request_notes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('public_quote_request_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->text('body');
                $table->timestamps();

                $table->index(['public_quote_request_id', 'created_at'], 'pqr_notes_request_created_idx');
            });
        } elseif (! $this->indexExists('public_quote_request_notes', 'pqr_notes_request_created_idx')) {
            Schema::table('public_quote_request_notes', function (Blueprint $table) {
                $table->index(['public_quote_request_id', 'created_at'], 'pqr_notes_request_created_idx');
            });
        }
    }

    protected function indexExists(string $table, string $index): bool
    {
        $rows = Schema::getConnection()->select(
            'SHOW INDEX FROM `'.$table.'` WHERE Key_name = ?',
            [$index],
        );

        return count($rows) > 0;
    }

    public function down(): void
    {
        Schema::dropIfExists('public_quote_request_notes');

        if (Schema::hasTable('public_quote_requests')) {
            Schema::table('public_quote_requests', function (Blueprint $table) {
                $columns = ['priority', 'expected_value', 'probability', 'target_follow_up_at'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('public_quote_requests', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
