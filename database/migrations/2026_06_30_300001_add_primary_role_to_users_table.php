<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('primary_role')->nullable()->after('is_active');
        });

        $assignments = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_type', User::class)
            ->where('roles.guard_name', 'web')
            ->orderBy('model_has_roles.model_id')
            ->orderBy('roles.name')
            ->get(['model_has_roles.model_id as user_id', 'roles.name as role_name']);

        foreach ($assignments->groupBy('user_id') as $userId => $roles) {
            DB::table('users')->where('id', $userId)->update([
                'primary_role' => $roles->first()->role_name,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('primary_role');
        });
    }
};
