<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fixed_assets', function (Blueprint $table) {
            $table->string('acquisition_accounting_status', 20)
                ->default('not_posted')
                ->after('posted_acquisition_journal_id');
            $table->text('acquisition_posting_error')
                ->nullable()
                ->after('acquisition_accounting_status');
        });

        DB::table('fixed_assets')
            ->whereNotNull('posted_acquisition_journal_id')
            ->update(['acquisition_accounting_status' => 'posted']);
    }

    public function down(): void
    {
        Schema::table('fixed_assets', function (Blueprint $table) {
            $table->dropColumn(['acquisition_accounting_status', 'acquisition_posting_error']);
        });
    }
};
