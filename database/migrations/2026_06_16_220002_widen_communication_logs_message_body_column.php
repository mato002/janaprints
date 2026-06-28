<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite'
            || ! Schema::hasTable('communication_logs')) {
            return;
        }

        Schema::table('communication_logs', function (Blueprint $table) {
            $table->longText('message_body')->change();
        });
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite'
            || ! Schema::hasTable('communication_logs')) {
            return;
        }

        Schema::table('communication_logs', function (Blueprint $table) {
            $table->text('message_body')->change();
        });
    }
};
