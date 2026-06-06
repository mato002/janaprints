<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('website_gallery_items', function (Blueprint $table) {
            if (! Schema::hasColumn('website_gallery_items', 'materials_label')) {
                $table->string('materials_label', 255)->nullable()->after('timeline_label');
            }

            if (! Schema::hasColumn('website_gallery_items', 'outcome')) {
                $table->text('outcome')->nullable()->after('materials_label');
            }
        });
    }

    public function down(): void
    {
        Schema::table('website_gallery_items', function (Blueprint $table) {
            if (Schema::hasColumn('website_gallery_items', 'outcome')) {
                $table->dropColumn('outcome');
            }

            if (Schema::hasColumn('website_gallery_items', 'materials_label')) {
                $table->dropColumn('materials_label');
            }
        });
    }
};
