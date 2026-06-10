<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('print_artwork_analyses')) {
            return;
        }

        Schema::table('print_artwork_analyses', function (Blueprint $table) {
            if (! Schema::hasColumn('print_artwork_analyses', 'public_quote_request_id')) {
                $table->foreignId('public_quote_request_id')
                    ->nullable()
                    ->after('production_job_card_id')
                    ->constrained('public_quote_requests')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('print_artwork_analyses', 'artwork_file_id')) {
                $table->foreignId('artwork_file_id')
                    ->nullable()
                    ->after('public_quote_request_id')
                    ->constrained('artwork_files')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('print_artwork_analyses', 'source_file_model')) {
                $table->string('source_file_model', 120)->nullable()->after('artwork_file_id');
            }

            if (! Schema::hasColumn('print_artwork_analyses', 'source_file_id')) {
                $table->unsignedBigInteger('source_file_id')->nullable()->after('source_file_model');
            }
        });

        if (Schema::hasColumn('print_artwork_analyses', 'public_quote_request_id')
            && ! $this->indexExists('print_artwork_analyses', 'print_artwork_pqr_idx')) {
            Schema::table('print_artwork_analyses', function (Blueprint $table) {
                $table->index('public_quote_request_id', 'print_artwork_pqr_idx');
            });
        }

        if (Schema::hasColumn('print_artwork_analyses', 'source_file_model')
            && Schema::hasColumn('print_artwork_analyses', 'source_file_id')
            && ! $this->indexExists('print_artwork_analyses', 'print_artwork_source_file_idx')) {
            Schema::table('print_artwork_analyses', function (Blueprint $table) {
                $table->index(['source_file_model', 'source_file_id'], 'print_artwork_source_file_idx');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('print_artwork_analyses')) {
            return;
        }

        Schema::table('print_artwork_analyses', function (Blueprint $table) {
            if ($this->indexExists('print_artwork_analyses', 'print_artwork_source_file_idx')) {
                $table->dropIndex('print_artwork_source_file_idx');
            }

            if ($this->indexExists('print_artwork_analyses', 'print_artwork_pqr_idx')) {
                $table->dropIndex('print_artwork_pqr_idx');
            }

            foreach (['source_file_id', 'source_file_model', 'artwork_file_id', 'public_quote_request_id'] as $column) {
                if (Schema::hasColumn('print_artwork_analyses', $column)) {
                    if (in_array($column, ['public_quote_request_id', 'artwork_file_id'], true)) {
                        $table->dropConstrainedForeignId($column);
                    } else {
                        $table->dropColumn($column);
                    }
                }
            }
        });
    }

    protected function indexExists(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            $indexes = $connection->select("PRAGMA index_list('{$table}')");

            return collect($indexes)->contains(fn ($row) => ($row->name ?? null) === $index);
        }

        $database = $connection->getDatabaseName();

        return (bool) $connection->selectOne(
            'SELECT 1 FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ? LIMIT 1',
            [$database, $table, $index],
        );
    }
};
