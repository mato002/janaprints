<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite'
            || ! Schema::hasTable('email_messages')) {
            return;
        }

        Schema::table('email_messages', function (Blueprint $table) {
            $table->longText('body')->change();
        });
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite'
            || ! Schema::hasTable('email_messages')) {
            return;
        }

        Schema::table('email_messages', function (Blueprint $table) {
            $table->text('body')->change();
        });
    }
};
