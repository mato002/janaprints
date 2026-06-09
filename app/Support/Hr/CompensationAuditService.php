<?php

namespace App\Support\Hr;

use App\Models\ActivityLog;
use App\Models\Hr\CompensationSalaryChange;
use App\Models\Hr\EmployeeCompensation;
use App\Models\Hr\PayrollAllowance;
use App\Models\Hr\PayrollDeduction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CompensationAuditService
{
    /**
     * @return array{salary_changes: LengthAwarePaginator, activity: LengthAwarePaginator}
     */
    public function paginate(int $companyId, int $perPage = 20): array
    {
        $salaryChanges = CompensationSalaryChange::query()
            ->where('company_id', $companyId)
            ->with(['employee', 'changedBy', 'compensation'])
            ->orderByDesc('effective_from')
            ->paginate($perPage, ['*'], 'changes_page');

        $modelTypes = [
            EmployeeCompensation::class,
            PayrollAllowance::class,
            PayrollDeduction::class,
        ];

        $activity = ActivityLog::query()
            ->forTenant()
            ->where('company_id', $companyId)
            ->whereIn('model_type', $modelTypes)
            ->with('user')
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'activity_page');

        return [
            'salary_changes' => $salaryChanges,
            'activity' => $activity,
        ];
    }

    /**
     * @return Collection<int, CompensationSalaryChange>
     */
    public function salaryHistoryForEmployee(int $employeeId): Collection
    {
        return CompensationSalaryChange::query()
            ->where('employee_id', $employeeId)
            ->with(['changedBy', 'compensation'])
            ->orderByDesc('effective_from')
            ->get();
    }
}
