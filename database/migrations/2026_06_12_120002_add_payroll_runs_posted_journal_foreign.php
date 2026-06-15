<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payroll_runs') || ! Schema::hasColumn('payroll_runs', 'posted_journal_id')) {
            return;
        }

        if ($this->postedJournalForeignKeyExists()) {
            return;
        }

        Schema::table('payroll_runs', function (Blueprint $table) {
            $table->foreign('posted_journal_id', 'payroll_runs_posted_journal_id_foreign')
                ->references('id')
                ->on('journals')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('payroll_runs')) {
            return;
        }

        foreach (['payroll_runs_posted_journal_id_foreign', 'payroll_runs_posted_journal_fk'] as $constraint) {
            if ($this->foreignKeyExists($constraint)) {
                Schema::table('payroll_runs', function (Blueprint $table) use ($constraint) {
                    $table->dropForeign($constraint);
                });
            }
        }
    }

    protected function postedJournalForeignKeyExists(): bool
    {
        return $this->foreignKeyExists('payroll_runs_posted_journal_id_foreign')
            || $this->foreignKeyExists('payroll_runs_posted_journal_fk');
    }

    protected function foreignKeyExists(string $constraintName): bool
    {
        $result = DB::selectOne(
            'SELECT CONSTRAINT_NAME
             FROM information_schema.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND CONSTRAINT_NAME = ?
               AND CONSTRAINT_TYPE = ?',
            ['payroll_runs', $constraintName, 'FOREIGN KEY'],
        );

        return $result !== null;
    }
};
