<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('print_forecast_snapshots')) {
            return;
        }

        Schema::create('print_forecast_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('forecast_type', 30);
            $table->string('period_type', 20);
            $table->date('forecast_period_start');
            $table->date('forecast_period_end');
            $table->unsignedInteger('historical_periods_used')->default(0);
            $table->decimal('forecast_value', 14, 2)->nullable();
            $table->decimal('lower_bound', 14, 2)->nullable();
            $table->decimal('upper_bound', 14, 2)->nullable();
            $table->decimal('confidence_score', 5, 2)->nullable();
            $table->string('forecast_model', 30);
            $table->string('forecast_version', 30);
            $table->json('metadata')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->foreign('branch_id', 'pfs_branch_fk')->references('id')->on('branches')->nullOnDelete();

            $table->index('company_id', 'pfs_company_idx');
            $table->index('forecast_type', 'pfs_type_idx');
            $table->index('period_type', 'pfs_period_type_idx');
            $table->index('forecast_period_start', 'pfs_period_start_idx');
            $table->unique(
                ['company_id', 'forecast_type', 'period_type', 'forecast_period_start'],
                'pfs_company_type_period_uq',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('print_forecast_snapshots');
    }
};
