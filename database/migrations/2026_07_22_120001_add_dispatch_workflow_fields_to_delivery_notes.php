<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->text('delivery_address')->nullable()->after('recipient_phone');
            $table->unsignedInteger('package_count')->nullable()->after('delivery_address');
            $table->text('package_notes')->nullable()->after('package_count');
            $table->foreignId('packaged_by')->nullable()->after('package_notes')->constrained('users')->nullOnDelete();
            $table->timestamp('packaged_at')->nullable()->after('packaged_by');
            $table->string('courier_name')->nullable()->after('packaged_at');
            $table->string('tracking_number')->nullable()->after('courier_name');
            $table->string('waybill_number')->nullable()->after('tracking_number');
            $table->string('pod_photo_path')->nullable()->after('recipient_signature');
            $table->timestamp('pod_captured_at')->nullable()->after('pod_photo_path');
        });
    }

    public function down(): void
    {
        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('packaged_by');
            $table->dropColumn([
                'delivery_address',
                'package_count',
                'package_notes',
                'packaged_at',
                'courier_name',
                'tracking_number',
                'waybill_number',
                'pod_photo_path',
                'pod_captured_at',
            ]);
        });
    }
};
