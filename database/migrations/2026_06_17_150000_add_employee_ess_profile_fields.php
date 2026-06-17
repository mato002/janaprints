<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (! Schema::hasColumn('employees', 'address')) {
                $table->text('address')->nullable()->after('phone');
            }

            if (! Schema::hasColumn('employees', 'emergency_contact_name')) {
                $table->string('emergency_contact_name')->nullable()->after('address');
            }

            if (! Schema::hasColumn('employees', 'emergency_contact_phone')) {
                $table->string('emergency_contact_phone', 50)->nullable()->after('emergency_contact_name');
            }

            if (! Schema::hasColumn('employees', 'next_of_kin_name')) {
                $table->string('next_of_kin_name')->nullable()->after('emergency_contact_phone');
            }

            if (! Schema::hasColumn('employees', 'next_of_kin_phone')) {
                $table->string('next_of_kin_phone', 50)->nullable()->after('next_of_kin_name');
            }

            if (! Schema::hasColumn('employees', 'next_of_kin_relationship')) {
                $table->string('next_of_kin_relationship')->nullable()->after('next_of_kin_phone');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            foreach ([
                'address',
                'emergency_contact_name',
                'emergency_contact_phone',
                'next_of_kin_name',
                'next_of_kin_phone',
                'next_of_kin_relationship',
            ] as $column) {
                if (Schema::hasColumn('employees', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
