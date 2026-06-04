<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_invoices', function (Blueprint $table) {
            if (! Schema::hasColumn('customer_invoices', 'amount_paid')) {
                $table->decimal('amount_paid', 15, 2)->default(0)->after('total_amount');
            }
            if (! Schema::hasColumn('customer_invoices', 'balance_due')) {
                $table->decimal('balance_due', 15, 2)->default(0)->after('amount_paid');
            }
        });

        if (! Schema::hasTable('customer_payments')) {
        Schema::create('customer_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('payment_number');
            $table->date('payment_date');
            $table->string('payment_method', 20);
            $table->boolean('is_deposit')->default(false);
            $table->decimal('amount', 15, 2);
            $table->decimal('allocated_amount', 15, 2)->default(0);
            $table->decimal('unallocated_amount', 15, 2)->default(0);
            $table->string('currency', 3)->default('KES');
            $table->string('status', 30)->default('draft');
            $table->string('reference')->nullable();
            $table->string('bank_reference')->nullable();
            $table->string('mpesa_reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->foreignId('posted_journal_id')->nullable()->constrained('journals')->nullOnDelete();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancel_reason')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'payment_number']);
            $table->index(['company_id', 'customer_id', 'status']);
        });
        }

        if (! Schema::hasTable('customer_payment_allocations')) {
        Schema::create('customer_payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_invoice_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->timestamps();

            $table->unique(['customer_payment_id', 'customer_invoice_id'], 'payment_alloc_payment_invoice_unique');
        });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_payment_allocations');
        Schema::dropIfExists('customer_payments');

        Schema::table('customer_invoices', function (Blueprint $table) {
            $table->dropColumn(['amount_paid', 'balance_due']);
        });
    }
};
