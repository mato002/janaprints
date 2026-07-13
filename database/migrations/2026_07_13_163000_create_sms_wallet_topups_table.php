<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_wallet_topups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reference', 40)->unique();
            $table->string('provider_transaction_id')->nullable()->index();
            $table->string('checkout_request_id')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('phone_number', 20);
            $table->string('status', 20)->default('pending')->index();
            $table->string('mpesa_receipt')->nullable();
            $table->decimal('provider_balance_after', 12, 2)->nullable();
            $table->boolean('local_credit_applied')->default(false);
            $table->text('message')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_wallet_topups');
    }
};
