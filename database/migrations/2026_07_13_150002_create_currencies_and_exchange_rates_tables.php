<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('currencies')) {
            Schema::create('currencies', function (Blueprint $table) {
                $table->string('code', 3)->primary();
                $table->string('name');
                $table->string('symbol', 8)->nullable();
                $table->unsignedTinyInteger('decimal_places')->default(2);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('exchange_rates')) {
            Schema::create('exchange_rates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->string('currency_code', 3);
                $table->date('rate_date');
                $table->decimal('rate_to_base', 18, 8);
                $table->string('source')->nullable();
                $table->timestamps();

                $table->foreign('currency_code')->references('code')->on('currencies')->cascadeOnUpdate();
                $table->unique(['company_id', 'currency_code', 'rate_date']);
                $table->index(['company_id', 'rate_date']);
            });
        }

        $now = now();
        $seeds = [
            ['code' => 'KES', 'name' => 'Kenyan Shilling', 'symbol' => 'KSh', 'decimal_places' => 2, 'is_active' => true],
            ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'decimal_places' => 2, 'is_active' => true],
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'decimal_places' => 2, 'is_active' => true],
            ['code' => 'GBP', 'name' => 'British Pound', 'symbol' => '£', 'decimal_places' => 2, 'is_active' => true],
        ];

        foreach ($seeds as $row) {
            DB::table('currencies')->updateOrInsert(
                ['code' => $row['code']],
                array_merge($row, ['created_at' => $now, 'updated_at' => $now]),
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
        Schema::dropIfExists('currencies');
    }
};
