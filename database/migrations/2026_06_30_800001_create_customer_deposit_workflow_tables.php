<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_payments', function (Blueprint $table) {
            if (! Schema::hasColumn('customer_payments', 'credit_issued')) {
                $table->decimal('credit_issued', 15, 2)->default(0)->after('unallocated_amount');
            }
            if (! Schema::hasColumn('customer_payments', 'credit_applied')) {
                $table->decimal('credit_applied', 15, 2)->default(0)->after('credit_issued');
            }
            if (! Schema::hasColumn('customer_payments', 'credit_refunded')) {
                $table->decimal('credit_refunded', 15, 2)->default(0)->after('credit_applied');
            }
            if (! Schema::hasColumn('customer_payments', 'credit_remaining')) {
                $table->decimal('credit_remaining', 15, 2)->default(0)->after('credit_refunded');
            }
        });

        if (! Schema::hasTable('customer_deposit_applications')) {
            Schema::create('customer_deposit_applications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
                $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
                $table->foreignId('customer_payment_id')->constrained()->cascadeOnDelete();
                $table->foreignId('customer_invoice_id')->constrained()->cascadeOnDelete();
                $table->string('application_number');
                $table->date('application_date');
                $table->decimal('amount', 15, 2);
                $table->string('status', 30)->default('posted');
                $table->text('notes')->nullable();
                $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('posted_at')->nullable();
                $table->foreignId('posted_journal_id')->nullable()->constrained('journals')->nullOnDelete();
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['company_id', 'application_number'], 'cust_dep_app_co_num_uniq');
                $table->index(['customer_id', 'status']);
                $table->index(['customer_invoice_id', 'status']);
            });
        }

        if (! Schema::hasTable('customer_deposit_refunds')) {
            Schema::create('customer_deposit_refunds', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
                $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
                $table->foreignId('customer_payment_id')->constrained()->cascadeOnDelete();
                $table->string('refund_number');
                $table->date('refund_date');
                $table->string('payment_method', 20);
                $table->decimal('amount', 15, 2);
                $table->string('status', 30)->default('posted');
                $table->string('reference')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('posted_at')->nullable();
                $table->foreignId('posted_journal_id')->nullable()->constrained('journals')->nullOnDelete();
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['company_id', 'refund_number'], 'cust_dep_ref_co_num_uniq');
                $table->index(['customer_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_deposit_refunds');
        Schema::dropIfExists('customer_deposit_applications');

        Schema::table('customer_payments', function (Blueprint $table) {
            $table->dropColumn([
                'credit_issued',
                'credit_applied',
                'credit_refunded',
                'credit_remaining',
            ]);
        });
    }
};
