<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('job_title_id')
                ->nullable()
                ->after('department_id')
                ->constrained('job_titles')
                ->nullOnDelete();

            $table->index(['company_id', 'job_title_id'], 'employees_company_job_title_idx');
        });

        $this->backfillFromDesignations();
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['job_title_id']);
            $table->dropIndex('employees_company_job_title_idx');
            $table->dropColumn('job_title_id');
        });
    }

    protected function backfillFromDesignations(): void
    {
        if (! Schema::hasTable('job_titles') || ! Schema::hasColumn('employees', 'designation')) {
            return;
        }

        $employees = DB::table('employees')
            ->whereNotNull('designation')
            ->where('designation', '!=', '')
            ->whereNull('job_title_id')
            ->get(['id', 'company_id', 'designation']);

        foreach ($employees as $employee) {
            $jobTitleId = DB::table('job_titles')
                ->where('company_id', $employee->company_id)
                ->where(function ($query) use ($employee) {
                    $query->where('title', $employee->designation)
                        ->orWhere('code', $this->designationCode($employee->designation));
                })
                ->value('id');

            if ($jobTitleId) {
                DB::table('employees')
                    ->where('id', $employee->id)
                    ->update(['job_title_id' => $jobTitleId]);
            }
        }
    }

    protected function designationCode(string $designation): string
    {
        return strtoupper(str_replace(' ', '_', preg_replace('/[^a-zA-Z0-9 ]/', '', $designation) ?? ''));
    }
};
