<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('background_job_cancellations', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->unsignedBigInteger('queue_job_id')->nullable();
            $table->string('queue');
            $table->string('job_class');
            $table->string('job_type');
            $table->text('payload')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at');
            $table->timestamps();

            $table->index(['queue', 'cancelled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('background_job_cancellations');
    }
};
