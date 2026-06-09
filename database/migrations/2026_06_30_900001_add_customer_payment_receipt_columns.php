<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_payments', function (Blueprint $table) {
            if (! Schema::hasColumn('customer_payments', 'receipt_number')) {
                $table->string('receipt_number')->nullable()->after('payment_number');
            }
            if (! Schema::hasColumn('customer_payments', 'receipt_emailed_at')) {
                $table->timestamp('receipt_emailed_at')->nullable()->after('posted_at');
            }
            if (! Schema::hasColumn('customer_payments', 'receipt_sms_sent_at')) {
                $table->timestamp('receipt_sms_sent_at')->nullable()->after('receipt_emailed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customer_payments', function (Blueprint $table) {
            $table->dropColumn(['receipt_number', 'receipt_emailed_at', 'receipt_sms_sent_at']);
        });
    }
};
