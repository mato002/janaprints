<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('public_quote_requests')) {
            Schema::create('public_quote_requests', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('name', 120);
                $table->string('company', 160)->nullable();
                $table->string('phone', 30);
                $table->string('email', 160);
                $table->string('service_needed', 120);
                $table->string('quantity', 80)->nullable();
                $table->string('deadline', 80)->nullable();
                $table->text('message');
                $table->string('artwork_path')->nullable();
                $table->string('artwork_original_name')->nullable();
                $table->string('status', 20)->default('pending');
                $table->string('source', 40)->default('storefront');
                $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
                $table->text('admin_notes')->nullable();
                $table->timestamp('responded_at')->nullable();
                $table->timestamps();

                $table->index('email');
                $table->index('phone');
                $table->index('status');
                $table->index('service_needed');
                $table->index('created_at');
            });
        }

        if (! Schema::hasTable('public_contact_messages')) {
            Schema::create('public_contact_messages', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('name', 120);
                $table->string('company', 160)->nullable();
                $table->string('phone', 30)->nullable();
                $table->string('email', 160);
                $table->string('subject', 160);
                $table->text('message');
                $table->string('status', 20)->default('unread');
                $table->string('source', 40)->default('storefront');
                $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
                $table->text('admin_notes')->nullable();
                $table->timestamp('responded_at')->nullable();
                $table->timestamps();

                $table->index('email');
                $table->index('status');
                $table->index('created_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('public_contact_messages');
        Schema::dropIfExists('public_quote_requests');
    }
};
