<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recipient_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 60);
            $table->string('priority', 20)->default('normal');
            $table->string('title');
            $table->text('body');
            $table->string('action_url')->nullable();
            $table->string('required_permission')->nullable();
            $table->nullableMorphs('subject');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['recipient_user_id', 'created_at'], 'notif_recipient_created_idx');
            $table->index(['company_id', 'type'], 'notif_company_type_idx');
        });

        Schema::create('notification_reads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('notification_id');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('unread');
            $table->timestamp('read_at')->nullable();
            $table->timestamp('dismissed_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->unique(['notification_id', 'user_id'], 'notif_reads_unique');
            $table->index(['user_id', 'status'], 'notif_reads_user_status_idx');

            $table->foreign('notification_id', 'notif_reads_notif_fk')
                ->references('id')
                ->on('notifications')
                ->cascadeOnDelete();
        });

        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->boolean('commercial_alerts')->default(true);
            $table->boolean('production_alerts')->default(true);
            $table->boolean('accounting_alerts')->default(true);
            $table->boolean('hr_alerts')->default(true);
            $table->boolean('system_alerts')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'company_id'], 'notif_prefs_user_company_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
        Schema::dropIfExists('notification_reads');
        Schema::dropIfExists('notifications');
    }
};
