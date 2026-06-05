<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pos_sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pos_session_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('return_number', 40);
            $table->string('return_type', 30);
            $table->string('status', 20)->default('pending');
            $table->string('refund_method', 20);
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('refund_amount', 14, 2)->default(0);
            $table->boolean('is_full_return')->default(false);
            $table->text('reason');
            $table->text('rejection_reason')->nullable();
            $table->string('refund_reference')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'branch_id', 'return_number']);
            $table->index(['company_id', 'branch_id', 'status']);
            $table->index(['pos_sale_id', 'status']);
        });

        Schema::create('pos_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pos_return_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pos_sale_item_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->decimal('quantity_returned', 14, 3);
            $table->decimal('unit_price', 14, 2)->default(0);
            $table->decimal('line_refund_amount', 14, 2)->default(0);
            $table->string('reason')->nullable();
            $table->timestamps();
        });

        Schema::create('pos_return_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pos_return_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 30);
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_return_events');
        Schema::dropIfExists('pos_return_items');
        Schema::dropIfExists('pos_returns');
    }
};
