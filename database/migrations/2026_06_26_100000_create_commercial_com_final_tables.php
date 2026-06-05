<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commercial_price_books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('code', 40);
            $table->text('description')->nullable();
            $table->string('currency', 3)->default('KES');
            $table->string('status', 20)->default('active');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_default')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'code'], 'com_price_books_company_code_uq');
            $table->index(['company_id', 'branch_id', 'status'], 'com_price_books_co_br_status_idx');
            $table->index(['company_id', 'is_default', 'status'], 'com_price_books_co_default_idx');
        });

        Schema::create('commercial_price_book_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('price_book_id')->constrained('commercial_price_books')->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->nullable()->constrained('inventory_items')->nullOnDelete();
            $table->string('service_code', 60)->nullable();
            $table->string('description')->nullable();
            $table->decimal('unit_price', 15, 2);
            $table->decimal('minimum_quantity', 12, 4)->nullable();
            $table->decimal('discount_percent', 5, 2)->nullable();
            $table->timestamp('effective_from')->nullable();
            $table->timestamp('effective_to')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->index(['price_book_id', 'inventory_item_id', 'status'], 'com_pb_items_book_item_status_idx');
            $table->index(['price_book_id', 'service_code', 'status'], 'com_pb_items_book_service_status_idx');
        });

        Schema::create('commercial_customer_price_books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('price_book_id')->constrained('commercial_price_books')->cascadeOnDelete();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->index(['company_id', 'customer_id', 'status'], 'com_cust_pb_co_cust_status_idx');
            $table->index(['price_book_id', 'status'], 'com_cust_pb_book_status_idx');
        });

        Schema::create('commercial_complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('related_document_type', 60)->nullable();
            $table->unsignedBigInteger('related_document_id')->nullable();
            $table->string('subject');
            $table->text('description');
            $table->string('source', 30)->default('other');
            $table->string('priority', 20)->default('medium');
            $table->string('status', 30)->default('open');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'branch_id', 'status'], 'com_complaints_co_br_status_idx');
            $table->index(['company_id', 'customer_id', 'status'], 'com_complaints_co_cust_status_idx');
            $table->index(['company_id', 'assigned_to', 'status'], 'com_complaints_co_assign_idx');
            $table->index(['company_id', 'created_at'], 'com_complaints_co_created_idx');
        });

        Schema::create('commercial_support_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('ticket_number', 40);
            $table->string('subject');
            $table->text('description');
            $table->string('channel', 30)->default('phone');
            $table->string('priority', 20)->default('medium');
            $table->string('status', 30)->default('open');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('due_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'ticket_number'], 'com_tickets_co_number_uq');
            $table->index(['company_id', 'branch_id', 'status'], 'com_tickets_co_br_status_idx');
            $table->index(['company_id', 'customer_id', 'status'], 'com_tickets_co_cust_status_idx');
            $table->index(['company_id', 'assigned_to', 'status'], 'com_tickets_co_assign_idx');
            $table->index(['company_id', 'due_at', 'status'], 'com_tickets_co_due_status_idx');
        });

        Schema::create('commercial_ticket_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('commercial_support_tickets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('comment');
            $table->string('visibility', 20)->default('internal');
            $table->timestamps();

            $table->index(['ticket_id', 'created_at'], 'com_ticket_comments_ticket_idx');
        });

        Schema::create('commercial_ticket_sla_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('commercial_support_tickets')->cascadeOnDelete();
            $table->string('event_type', 40);
            $table->timestamp('event_at');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['ticket_id', 'event_at'], 'com_ticket_sla_ticket_event_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commercial_ticket_sla_events');
        Schema::dropIfExists('commercial_ticket_comments');
        Schema::dropIfExists('commercial_support_tickets');
        Schema::dropIfExists('commercial_complaints');
        Schema::dropIfExists('commercial_customer_price_books');
        Schema::dropIfExists('commercial_price_book_items');
        Schema::dropIfExists('commercial_price_books');
    }
};
