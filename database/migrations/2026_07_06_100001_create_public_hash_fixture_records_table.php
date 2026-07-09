<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_hash_fixture_records', function (Blueprint $table) {
            $table->id();
            require_once database_path('migrations/helpers/add_public_id_column.php');
            add_public_id_column($table);
            $table->string('label')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_hash_fixture_records');
    }
};
